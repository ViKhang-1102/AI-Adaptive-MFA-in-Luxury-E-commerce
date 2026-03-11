import argparse
import base64
import hashlib
import json
import os
import re
import shutil
import sys
from datetime import datetime
from typing import Optional

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

def gray_world_white_balance(img):
    """Áp dụng Gray World White Balance để loại bỏ ám màu do môi trường (vàng/xanh)."""
    result = img.astype(np.float32)
    # Tính trung bình mỗi kênh
    avg_b = np.mean(result[:, :, 0])
    avg_g = np.mean(result[:, :, 1])
    avg_r = np.mean(result[:, :, 2])
    avg = (avg_b + avg_g + avg_r) / 3.0

    # Scale mỗi kênh về mức trung bình chung
    if avg_b > 0:
        result[:, :, 0] = np.clip(result[:, :, 0] * (avg / avg_b), 0, 255)
    if avg_g > 0:
        result[:, :, 1] = np.clip(result[:, :, 1] * (avg / avg_g), 0, 255)
    if avg_r > 0:
        result[:, :, 2] = np.clip(result[:, :, 2] * (avg / avg_r), 0, 255)

    return result.astype(np.uint8)


def enhance_image(img):
    """Tiền xử lý ảnh (Adaptive Environment Correction).
    - White balance (Gray World) để loại bỏ ám vàng/xanh do ánh sáng.
    - Histogram Equalization trên kênh Y (YCrCb) để giảm bóng đèn trần.
    - Bilateral Filter để giảm nhiễu mà không làm mờ chi tiết khuôn mặt.
    """
    # 1) White balance
    img = gray_world_white_balance(img)

    # 2) Histogram Equalization trên kênh Y (YCrCb)
    ycrcb = cv2.cvtColor(img, cv2.COLOR_BGR2YCrCb)
    y, cr, cb = cv2.split(ycrcb)
    y = cv2.equalizeHist(y)
    img = cv2.cvtColor(cv2.merge((y, cr, cb)), cv2.COLOR_YCrCb2BGR)

    # 3) Bilateral filter giúp giảm nhiễu camera mà vẫn giữ chi tiết cạnh
    enhanced = cv2.bilateralFilter(img, d=5, sigmaColor=50, sigmaSpace=50)

    log_debug('Environmental correction applied: GrayWorldWB + Y-channel hist-eq + bilateral filter')
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

def _normalize_path_for_hash(path: str) -> str:
    """Chuẩn hóa đường dẫn để tạo key cache ổn định (không phụ thuộc vào nội dung ảnh)."""
    return os.path.normpath(path).replace('\\', '/').lower()


def _get_user_id_from_reference(reference_path: str) -> Optional[str]:
    """Cố gắng trích xuất user ID từ tên file hoặc đường dẫn (vd. user_123, user-123)."""
    if not reference_path:
        return None
    base = os.path.basename(reference_path)
    m = re.search(r'user[_-]?(\d+)', base)
    if m:
        return m.group(1)
    m = re.search(r'user[_-]?(\d+)', reference_path)
    if m:
        return m.group(1)
    return None


def _get_cache_path(reference_path: str, cache_dir: str, user_id: str = None) -> str:
    """Mỗi user -> 1 file JSON cache ổn định.

    Nếu user_id được cung cấp sẽ được dùng thẳng (tên file user_{id}.json).
    Nếu không thể xác định user_id thì fallback về hash của đường dẫn để tránh phá vỡ cache cũ.
    """
    if not user_id:
        user_id = _get_user_id_from_reference(reference_path)

    if user_id:
        key = f'user_{user_id}'
    else:
        key = hashlib.sha256(_normalize_path_for_hash(reference_path).encode('utf-8')).hexdigest()

    return os.path.join(cache_dir, f'{key}.json')


def _brightness_label(brightness: float) -> str:
    if brightness < 90:
        return 'dark'
    if brightness > 170:
        return 'bright'
    return 'normal'


def compute_grid_descriptors(img, grid=(3, 3), cell_size=(128, 128)):
    """Tạo bộ descriptor theo lưới 3x3 (Grid-based) để tăng tính ổn định khi tóc/râu thay đổi."""
    target_h = grid[0] * cell_size[0]
    target_w = grid[1] * cell_size[1]
    resized = cv2.resize(img, (target_w, target_h), interpolation=cv2.INTER_CUBIC)

    descriptors = []
    for r in range(grid[0]):
        for c in range(grid[1]):
            y1 = r * cell_size[0]
            y2 = y1 + cell_size[0]
            x1 = c * cell_size[1]
            x2 = x1 + cell_size[1]
            cell = resized[y1:y2, x1:x2]
            descriptors.append(compute_face_vector(cell, size=cell_size))
    return descriptors


def compare_grid_descriptors(ref_grid, cand_grid):
    """So sánh 2 bộ descriptor grid và trả về điểm khớp với thông tin chi tiết từng ô."""
    # Trọng số ưu tiên vùng 'Tam giác vàng' (mắt + mũi) ở hàng giữa.
    weights = [
        [0.02, 0.06, 0.02],
        [0.20, 0.40, 0.20],
        [0.02, 0.06, 0.02],
    ]

    total_score = 0.0
    per_cell = []
    center_score = None

    for idx, (ref_vec, cand_vec) in enumerate(zip(ref_grid, cand_grid)):
        dist = float(np.linalg.norm(np.array(ref_vec, dtype=float) - np.array(cand_vec, dtype=float)))
        score = max(0.0, 1.0 - (dist / 1.5))
        r = idx // 3
        c = idx % 3
        w = weights[r][c]
        total_score += score * w
        per_cell.append({'row': r, 'col': c, 'dist': dist, 'score': score, 'weight': w})
        if r == 1 and c == 1:
            center_score = score

    # Ngưỡng khớp được tune lại cho môi trường thực tế (webcam, ánh sáng indoor).
    center_threshold = 0.40
    total_threshold = 0.35
    match = (center_score is not None and center_score >= center_threshold and total_score >= total_threshold)

    log_debug(
        f"Regional score breakdown: total={total_score:.3f}, center={center_score:.3f} "
        f"(threshold-center={center_threshold:.2f}, threshold-total={total_threshold:.2f}), match={match}"
    )
    for cell in per_cell:
        log_debug(f"  cell[{cell['row']},{cell['col']}]=score:{cell['score']:.3f} w:{cell['weight']}")

    return {
        'match': bool(match),
        'score': float(total_score),
        'center_score': float(center_score or 0.0),
        'cells': per_cell
    }


def build_template_descriptor(img):
    """Tạo descriptor template (grid + lighting info) từ ảnh khuôn mặt."""
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    brightness = float(np.mean(gray))
    lighting = _brightness_label(brightness)
    grid = compute_grid_descriptors(img)
    return {
        'grid': grid,
        'brightness': brightness,
        'lighting': lighting,
    }


def augment_dark_noisy(img):
    """Tạo bản giả lập môi trường tối/nhiễu để AI quen với điều kiện indoor."""
    return augment_noisy(augment_dark(img))


def augment_dark(img):
    """Tạo phiên bản tối để mô phỏng ánh sáng yếu."""
    return cv2.convertScaleAbs(img, alpha=0.6, beta=-30)


def augment_noisy(img):
    """Thêm nhiễu Gaussian để mô phỏng môi trường thấp chất lượng camera."""
    noise = np.random.normal(0, 12, img.shape).astype(np.int16)
    noisy = np.clip(img.astype(np.int16) + noise, 0, 255).astype(np.uint8)
    return noisy


def rotate_image(img, angle: float):
    """Xoay ảnh quanh tâm, giữ kích thước ban đầu."""
    (h, w) = img.shape[:2]
    center = (w // 2, h // 2)
    M = cv2.getRotationMatrix2D(center, angle, 1.0)
    rotated = cv2.warpAffine(img, M, (w, h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REFLECT)
    return rotated


MAX_TEMPLATES = 5


def save_to_cache(reference_path, descriptor, cache_dir, user_id: str = None):
    """Lưu định danh kỹ thuật (JSON) vào cache theo User ID (multi-template).

    Nếu không có user_id, sẽ cố gắng trích xuất từ tên file. Nếu vẫn không có -> fallback hash.
    """
    cache_path = _get_cache_path(reference_path, cache_dir, user_id)
    os.makedirs(cache_dir, exist_ok=True)

    is_new_profile = not os.path.exists(cache_path)
    existing = {}
    if not is_new_profile:
        try:
            with open(cache_path, 'r', encoding='utf-8') as f:
                existing = json.load(f) or {}
        except Exception:
            existing = {}

    templates = existing.get('templates') if isinstance(existing, dict) else None
    if not isinstance(templates, list):
        templates = []

    # Nếu descriptor đã là template (grid), thì nhập vào danh sách.
    if isinstance(descriptor, dict) and 'grid' in descriptor:
        template = {
            'id': hashlib.sha256(json.dumps(descriptor, sort_keys=True).encode('utf-8')).hexdigest()[:16],
            'created_at': datetime.utcnow().isoformat() + 'Z',
            'brightness': descriptor.get('brightness'),
            'lighting': descriptor.get('lighting'),
            'grid': descriptor['grid'],
        }

        # Nếu đã có template tương tự, ghi đè; nếu không thì thêm mới.
        updated = False
        for i, t in enumerate(templates):
            if isinstance(t, dict) and 'grid' in t:
                score = compare_grid_descriptors(t['grid'], template['grid'])
                if score['center_score'] >= 0.9:
                    templates[i] = template
                    updated = True
                    break
        if not updated:
            templates.append(template)

        # Giữ tối đa MAX_TEMPLATES mẫu, mới nhất ở cuối
        templates = templates[-MAX_TEMPLATES:]
        out = {'templates': templates}
    else:
        # fallback cũ (legacy) để không phá vỡ các dữ liệu đã lưu
        out = descriptor

    with open(cache_path, 'w', encoding='utf-8') as f:
        json.dump(out, f)

    if is_new_profile:
        if user_id:
            sys.stderr.write(f"New Profile created for User ID: {user_id}\n")
        else:
            sys.stderr.write(f"New Profile created: {os.path.basename(cache_path)}\n")
    else:
        sys.stderr.write(f"SUCCESS: JSON descriptor saved to face_verify_cache/{os.path.basename(cache_path)}\n")

    return cache_path

def lbph_match(training_img, candidate_img):
    """Dự phòng LBPH nếu Vision API lỗi hoặc môi trường offline.

    - Tăng sáng/tương phản khi ảnh quá tối.
    - Áp dụng Gaussian Blur nhẹ để giảm nhiễu hạt trong điều kiện thiếu sáng.
    - Sử dụng CLAHE thay vì equalizeHist để cân bằng sáng cục bộ.
    """
    def normalize(i):
        g = cv2.cvtColor(i, cv2.COLOR_BGR2GRAY)
        # Giảm nhiễu hạt nhẹ trước khi cân bằng histogram
        g = cv2.GaussianBlur(g, (3, 3), 0)

        # Tự động bù sáng/tương phản dựa trên độ sáng trung bình
        avg = np.mean(g)
        if avg < 80:
            # Rất tối: tăng mạnh hơn
            g = cv2.convertScaleAbs(g, alpha=1.5, beta=40)
        elif avg < 100:
            # Hơi tối: tăng vừa phải
            g = cv2.convertScaleAbs(g, alpha=1.3, beta=30)

        # CLAHE: cân bằng sáng cục bộ hơn equalizeHist
        clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
        g = clahe.apply(g)

        return cv2.resize(g, (200, 200))
        
    recognizer = cv2.face.LBPHFaceRecognizer_create()
    recognizer.train([normalize(training_img)], np.array([0]))
    label, confidence = recognizer.predict(normalize(candidate_img))
    return confidence

def compute_face_vector(img, size=(128, 128)):
    """Trích xuất Vector khuôn mặt kết hợp HOG (Gradient/Shape) và LBP (Texture).
    Kết hợp Bilateral Filter để giữ cạnh sắc nét, giúp nhận diện 'khung xương' trong bóng tối.
    """
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.bilateralFilter(gray, d=9, sigmaColor=75, sigmaSpace=75)
    gray = cv2.resize(gray, size)
    gray = cv2.GaussianBlur(gray, (3, 3), 0)

    # 1. Trích xuất HOG Vector (Hình dáng, đường nét khuôn mặt)
    hog = cv2.HOGDescriptor(
        _winSize=size, _blockSize=(16, 16),
        _blockStride=(8, 8), _cellSize=(8, 8), _nbins=9
    )
    hog_feats = hog.compute(gray)
    if hog_feats is not None:
        hog_feats = hog_feats.flatten()
        norm = np.linalg.norm(hog_feats)
        if norm > 0:
            hog_feats = hog_feats / norm
    else:
        hog_feats = np.array([])

    # 2. Trích xuất LBP Vector (Bề mặt da, các nếp nhăn)
    neighbors = [(-1, -1), (-1, 0), (-1, 1), (0, 1), (1, 1), (1, 0), (1, -1), (0, -1)]
    lbp = np.zeros_like(gray, dtype=np.uint8)
    for i, (dy, dx) in enumerate(neighbors):
        shifted = np.roll(np.roll(gray, dy, axis=0), dx, axis=1)
        lbp |= ((shifted >= gray) << i).astype(np.uint8)

    lbp_hist, _ = np.histogram(lbp.ravel(), bins=256, range=(0, 256))
    lbp_hist = lbp_hist.astype(float)
    lbp_norm = np.linalg.norm(lbp_hist)
    if lbp_norm > 0:
        lbp_hist = lbp_hist / lbp_norm

    # 3. Nối (Concatenate) cả 2 vector lại thành 1 siêu vector đặc trưng
    combined_vector = np.concatenate((hog_feats, lbp_hist))
    return combined_vector.tolist()


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
    parser.add_argument('reference', nargs='?', help='Reference image path (stored identity image)')
    parser.add_argument('candidate', nargs='?', help='Candidate image path (live scan)')
    parser.add_argument('--user-id', dest='user_id', help='User ID để tách cache theo từng người dùng')
    parser.add_argument('--enroll', action='store_true')
    parser.add_argument('--clear-cache', action='store_true', help='Xóa toàn bộ file JSON cache trong face_verify_cache')
    args = parser.parse_args()

    if not args.clear_cache and (not args.reference or not args.candidate):
        parser.error('reference and candidate are required unless --clear-cache is used')

    google_key = os.environ.get('GOOGLE_API_KEY')
    cache_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'face_verify_cache'))

    if args.clear_cache:
        if os.path.isdir(cache_dir):
            try:
                shutil.rmtree(cache_dir)
            except Exception:
                pass
        # Ensure folder exists after clearing
        os.makedirs(cache_dir, exist_ok=True)
        print(json.dumps({'cleared': True, 'path': cache_dir}))
        return

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

    # 3. Luồng Enrollment (Đăng ký) - Lưu nhiều mẫu (Multi-Template) để nhận diện trong cả môi trường sáng/tối.
    if args.enroll:
        used_img = cand_face if cand_found else cand_enhanced

        # Xác định User ID để chia cache riêng (user_{id}.json)
        user_id = args.user_id or _get_user_id_from_reference(args.reference)

        templates = []

        # 1) Ảnh gốc đã qua xử lý GrayWorld (cân bằng trắng)
        gray_world = gray_world_white_balance(used_img)
        desc_gray = build_template_descriptor(gray_world)
        save_to_cache(args.reference, desc_gray, cache_dir, user_id=user_id)
        templates.append({'type': 'gray_world', 'lighting': desc_gray['lighting'], 'brightness': desc_gray['brightness']})

        # 2) & 3) Ảnh xoay ±15 độ
        for angle in (-15, 15):
            rotated = rotate_image(used_img, angle)
            desc_rot = build_template_descriptor(rotated)
            save_to_cache(args.reference, desc_rot, cache_dir, user_id=user_id)
            templates.append({'type': f'rotated_{angle}', 'lighting': desc_rot['lighting'], 'brightness': desc_rot['brightness']})

        # 4) Dark variant
        dark = augment_dark(used_img)
        desc_dark = build_template_descriptor(dark)
        save_to_cache(args.reference, desc_dark, cache_dir, user_id=user_id)
        templates.append({'type': 'dark', 'lighting': desc_dark['lighting'], 'brightness': desc_dark['brightness']})

        # 5) Noisy variant
        noisy = augment_noisy(used_img)
        desc_noisy = build_template_descriptor(noisy)
        save_to_cache(args.reference, desc_noisy, cache_dir, user_id=user_id)
        templates.append({'type': 'noisy', 'lighting': desc_noisy['lighting'], 'brightness': desc_noisy['brightness']})

        print(json.dumps({
            'match': True,
            'confidence': 1.0,
            'reason': 'Enrollment saved (multi-angle templates).',
            'enrolled_templates': templates,
        }))
        return

    # 4. Luồng Verification (Xác thực)
    # Luôn ưu tiên cache theo User ID (user_{id}.json) để tránh nhiễm chéo giữa các user.
    user_id = args.user_id or _get_user_id_from_reference(args.reference)
    cache_path = _get_cache_path(args.reference, cache_dir, user_id)

    # Nếu định danh user xác định được nhưng cache chưa tồn tại thì không tiếp tục các cơ chế fallback
    if user_id and not os.path.exists(cache_path):
        print(json.dumps({'match': False, 'confidence': 0, 'reason': 'Face cache missing for user.'}))
        return

    # Chuyển tiếp cho legacy: nếu không có user_id, vẫn thử dùng hash cũ để giữ tương thích
    if not os.path.exists(cache_path):
        try:
            with open(args.reference, 'rb') as f:
                h = hashlib.sha256(f.read()).hexdigest()
            legacy_path = os.path.join(cache_dir, f'{h}.json')
            if os.path.exists(legacy_path):
                cache_path = legacy_path
        except Exception:
            pass

    ref_desc = None
    if os.path.exists(cache_path):
        try:
            with open(cache_path, 'r', encoding='utf-8') as f:
                ref_desc = json.load(f)
        except Exception:
            pass

    # 1) Multi-template (Grid-based) matching
    if isinstance(ref_desc, dict) and isinstance(ref_desc.get('templates'), list):
        cand_grid = compute_grid_descriptors(cand_face if cand_found else cand_enhanced)
        best = None
        for idx, t in enumerate(ref_desc.get('templates', [])):
            if not isinstance(t, dict) or 'grid' not in t:
                continue
            sys.stderr.write(f"Matching with Template index: {idx}\n")
            score = compare_grid_descriptors(t['grid'], cand_grid)
            if best is None or score['score'] > best['score']['score']:
                best = {'score': score, 'template': t}

        if best:
            comp = best['score']
            t = best['template']
            match = comp['match']

            # Self-learning: nếu match tốt (score > 0.85), thêm template mới vào cache để cập nhật ngoại hình.
            if match and comp['score'] > 0.85:
                new_template = build_template_descriptor(cand_face if cand_found else cand_enhanced)
                save_to_cache(args.reference, new_template, cache_dir)
                log_debug(f"Update-on-success: added new template (score={comp['score']:.3f})")

            print(json.dumps({
                'match': match,
                'confidence': float(comp['score']),
                'reason': f"Grid match (lighting={t.get('lighting')}, brightness={t.get('brightness')})",
                'used_google_vision': False,
                'used_grid': True,
                'best_template': {
                    'lighting': t.get('lighting'),
                    'brightness': t.get('brightness'),
                    'center_score': comp['center_score'],
                    'score': comp['score'],
                },
                'grid_details': comp['cells'],
            }))
            return

    # 2) Nếu có cache Landmarks và có Google Key -> So sánh Landmarks (legacy)
    if ref_desc and isinstance(ref_desc, list) and google_key:
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

    # 3) Nếu có cache vector descriptor (fallback), hãy so sánh euclidean distance (legacy)
    if ref_desc and isinstance(ref_desc, dict) and 'descriptor' in ref_desc:
        cand_desc = compute_face_vector(cand_face if cand_found else cand_enhanced)
        ref_vec = np.array(ref_desc['descriptor'], dtype=float)
        cand_vec = np.array(cand_desc, dtype=float)
        
        if len(ref_vec) != len(cand_vec):
            # Formatted sai do model cũ, bắt buộc loại để trigger Auth cập nhật
            dist = 1.0 
        else:
            dist = float(np.linalg.norm(ref_vec - cand_vec))
            
        # Threshold vector tổng hợp (HOG+LBP) cho phép dao động hợp lý 0.80
        match = dist <= 0.80
        print(json.dumps({
            'match': bool(match),
            'confidence': float(max(0.0, 1.0 - (dist / 1.5))),
            'reason': f'Facial Vector distance: {dist:.3f} (Threshold: 0.80)',
            'used_google_vision': False,
            'used_fallback_descriptor': True
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
