import argparse
import base64
import hashlib
import json
import os
import sys
import cv2
import numpy as np
import requests

def log_debug(msg):
    """In log ra stderr để không làm hỏng luồng stdout chứa JSON."""
    sys.stderr.write(f"DEBUG: {msg}\n")

def load_image(path):
    img = cv2.imread(path)
    if img is None:
        return None
    return img

def enhance_image(img):
    """
    Tiền xử lý ảnh: 
    1. Tăng Brightness & Contrast lên 30% nếu ảnh bị tối.
    2. Sử dụng CLAHE để cân bằng ánh sáng cục bộ.
    """
    # Chuyển sang không gian màu LAB
    lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
    l, a, b = cv2.split(lab)
    
    # Kiểm tra độ sáng trung bình của kênh L (Lightness)
    avg_brightness = np.mean(l)
    log_debug(f"Average brightness: {avg_brightness:.2f}")
    
    # Nếu ảnh tối (ngưỡng < 100), tăng độ sáng và tương phản
    if avg_brightness < 100:
        log_debug("Low light detected. Applying 30% boost.")
        # Tăng contrast (alpha) và brightness (beta)
        l = cv2.convertScaleAbs(l, alpha=1.3, beta=30)
    
    # Áp dụng CLAHE để tối ưu hóa chi tiết khuôn mặt
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    cl = clahe.apply(l)
    
    lab = cv2.merge((cl, a, b))
    enhanced = cv2.cvtColor(lab, cv2.COLOR_LAB2BGR)
    return enhanced

def detect_face_region(img):
    cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
    face_cascade = cv2.CascadeClassifier(cascade_path)
    
    def _detect(img_to_check):
        gray = cv2.cvtColor(img_to_check, cv2.COLOR_BGR2GRAY)
        # Nới lỏng minNeighbors để dễ tìm mặt hơn trong điều kiện khó
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=3, minSize=(40, 40))
        if len(faces) == 0:
            return None
        # Ưu tiên khuôn mặt to nhất (gần camera nhất)
        faces = sorted(faces, key=lambda r: r[2] * r[3], reverse=True)
        return faces[0]

    # Thử detect trên nhiều kích thước khác nhau (Multi-scale retry)
    scales = [1.0, 1.25, 0.8, 1.5]
    for scale in scales:
        if scale == 1.0:
            temp_img = img
        else:
            w = int(img.shape[1] * scale)
            h = int(img.shape[0] * scale)
            temp_img = cv2.resize(img, (w, h), interpolation=cv2.INTER_CUBIC)
            
        face = _detect(temp_img)
        if face is not None:
            x, y, w, h = face
            if scale != 1.0:
                x, y, w, h = int(x/scale), int(y/scale), int(w/scale), int(h/scale)
            
            # Crop vùng mặt với margin 15% để giữ lại các đặc trưng xung quanh
            margin_w = int(w * 0.15)
            margin_h = int(h * 0.15)
            y1 = max(0, y - margin_h)
            y2 = min(img.shape[0], y + h + margin_h)
            x1 = max(0, x - margin_w)
            x2 = min(img.shape[1], x + w + margin_w)
            
            return img[y1:y2, x1:x2], True

    return img, False

def get_face_descriptor_vision(img, api_key):
    """Trích xuất Landmarks từ Google Vision API."""
    if not api_key:
        log_debug("Google API Key missing.")
        return None
        
    _, buffer = cv2.imencode('.jpg', img)
    b64_image = base64.b64encode(buffer).decode('utf-8')
    
    url = f'https://vision.googleapis.com/v1/images:annotate?key={api_key}'
    payload = {
        'requests': [{
            'image': {'content': b64_image},
            'features': [{'type': 'FACE_DETECTION', 'maxResults': 1}]
        }]
    }
    
    try:
        resp = requests.post(url, json=payload, timeout=10)
        if resp.status_code == 200:
            data = resp.json()
            faces = data.get('responses', [{}])[0].get('faceAnnotations', [])
            if faces:
                landmarks = faces[0].get('landmarks', [])
                bbox = faces[0].get('boundingPoly', {}).get('vertices', [])
                if len(bbox) >= 4:
                    xs = [v.get('x', 0) for v in bbox]
                    ys = [v.get('y', 0) for v in bbox]
                    min_x, max_x = min(xs), max(xs)
                    min_y, max_y = min(ys), max(ys)
                    bw = max(1, max_x - min_x)
                    bh = max(1, max_y - min_y)
                    
                    norm_landmarks = []
                    for l in landmarks:
                        pos = l.get('position', {})
                        nx = (pos.get('x', 0) - min_x) / bw
                        ny = (pos.get('y', 0) - min_y) / bh
                        norm_landmarks.append({'type': l.get('type'), 'x': nx, 'y': ny})
                    return norm_landmarks
        else:
            log_debug(f"Vision API Error: {resp.status_code} - {resp.text}")
    except Exception as e:
        log_debug(f"Vision API Exception: {str(e)}")
    return None

def save_to_cache(img_path, descriptor, cache_dir):
    """Lưu định danh kỹ thuật (JSON) vào cache dựa trên hash của ảnh gốc."""
    with open(img_path, 'rb') as f:
        h = hashlib.sha256(f.read()).hexdigest()
    
    cache_path = os.path.join(cache_dir, f'{h}.json')
    os.makedirs(cache_dir, exist_ok=True)
    
    with open(cache_path, 'w', encoding='utf-8') as f:
        json.dump(descriptor, f)
    
    # In ra stderr theo đúng yêu cầu Senior Engineer
    sys.stderr.write(f"SUCCESS: JSON descriptor saved to face_verify_cache/{h}.json\n")
    return cache_path

def lbph_match(training_img, candidate_img):
    """Dự phòng LBPH nếu Vision API lỗi hoặc môi trường offline."""
    def normalize(i):
        g = cv2.cvtColor(i, cv2.COLOR_BGR2GRAY)
        g = cv2.equalizeHist(g)
        return cv2.resize(g, (200, 200))
        
    recognizer = cv2.face.LBPHFaceRecognizer_create()
    recognizer.train([normalize(training_img)], np.array([0]))
    label, confidence = recognizer.predict(normalize(candidate_img))
    return confidence

def euclidean_dist(l1, l2):
    """Tính khoảng cách Euclidean trung bình giữa 2 bộ landmarks."""
    dists = []
    l2_dict = {item['type']: item for item in l2}
    for item1 in l1:
        item2 = l2_dict.get(item1['type'])
        if item2:
            dists.append(np.sqrt((item1['x'] - item2['x'])**2 + (item1['y'] - item2['y'])**2))
    return np.mean(dists) if dists else 1.0

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('reference')
    parser.add_argument('candidate')
    parser.add_argument('--enroll', action='store_true')
    args = parser.parse_args()

    google_key = os.environ.get('GOOGLE_API_KEY')
    cache_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'face_verify_cache'))

    # Load và tiền xử lý
    ref_img = load_image(args.reference)
    cand_img = load_image(args.candidate)

    if ref_img is None or cand_img is None:
        # Debug chi tiết tại sao load fail
        if ref_img is None:
            log_debug(f"Reference image load failed: {args.reference}")
            if not os.path.exists(args.reference):
                log_debug(f"File DOES NOT EXIST at {args.reference}")
        if cand_img is None:
            log_debug(f"Candidate image load failed: {args.candidate}")
            
        print(json.dumps({'match': False, 'confidence': 0, 'reason': 'Image loading failed (Check server logs for path details)'}))
        return

    # 1. Tiền xử lý (Brightness, Contrast, CLAHE)
    ref_enhanced = enhance_image(ref_img)
    cand_enhanced = enhance_image(cand_img)

    # 2. Phát hiện vùng mặt
    ref_face, ref_found = detect_face_region(ref_enhanced)
    cand_face, cand_found = detect_face_region(cand_enhanced)

    # 3. Luồng Enrollment (Đăng ký)
    if args.enroll:
        # Ưu tiên dùng Google Vision để trích xuất landmarks 3D
        desc = get_face_descriptor_vision(ref_face if ref_found else ref_enhanced, google_key)
        if desc:
            save_to_cache(args.reference, desc, cache_dir)
            print(json.dumps({'match': True, 'confidence': 1.0, 'reason': 'Face landmarks extracted and cached successfully'}))
        else:
            # Fallback LBPH Enrollment (vẫn tạo file cache dummy để đánh dấu đã quét)
            save_to_cache(args.reference, {'fallback': 'LBPH'}, cache_dir)
            print(json.dumps({'match': True, 'confidence': 0.5, 'reason': 'Google Vision failed; enrolled using LBPH fallback'}))
        return

    # 4. Luồng Verification (Xác thực)
    # Tìm cache JSON của ảnh gốc
    with open(args.reference, 'rb') as f:
        h = hashlib.sha256(f.read()).hexdigest()
    cache_path = os.path.join(cache_dir, f'{h}.json')
    
    ref_desc = None
    if os.path.exists(cache_path):
        try:
            with open(cache_path, 'r', encoding='utf-8') as f:
                ref_desc = json.load(f)
        except Exception:
            pass

    # Nếu có cache Landmarks và có Google Key -> So sánh Landmarks
    if ref_desc and 'fallback' not in ref_desc and google_key:
        cand_desc = get_face_descriptor_vision(cand_face if cand_found else cand_enhanced, google_key)
        if cand_desc:
            dist = euclidean_dist(ref_desc, cand_desc)
            # Threshold Landmark Distance = 0.42
            match = dist <= 0.42
            print(json.dumps({
                'match': match,
                'confidence': float(max(0, 1 - dist)),
                'reason': f'Vision Landmark Distance: {dist:.3f} (Threshold: 0.42)',
                'used_google_vision': True
            }))
            return

    # Fallback cuối cùng: LBPH (OpenCV)
    raw_score = lbph_match(ref_face if ref_found else ref_img, cand_face if cand_found else cand_img)
    # Threshold LBPH = 65.0
    match = raw_score <= 65.0
    print(json.dumps({
        'match': match,
        'confidence': float(max(0, (100 - raw_score) / 100)),
        'reason': f'LBPH Match raw={raw_score:.1f} (Threshold: 65.0)',
        'used_google_vision': False
    }))

if __name__ == '__main__':
    # Đảm bảo CHỈ in ra JSON ở stdout
    main()
