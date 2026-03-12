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
    """Log to stderr so we do not corrupt JSON output on stdout."""
    sys.stderr.write(f"DEBUG: {msg}\n")

def load_image(path):
    img = cv2.imread(path)
    if img is None:
        return None
    return img

def gray_world_white_balance(img):
    """Apply Gray World white balance to remove environmental color cast (yellow/green)."""
    result = img.astype(np.float32)
    # Compute per-channel average
    avg_b = np.mean(result[:, :, 0])
    avg_g = np.mean(result[:, :, 1])
    avg_r = np.mean(result[:, :, 2])
    avg = (avg_b + avg_g + avg_r) / 3.0

    # Scale each channel toward the global average
    if avg_b > 0:
        result[:, :, 0] = np.clip(result[:, :, 0] * (avg / avg_b), 0, 255)
    if avg_g > 0:
        result[:, :, 1] = np.clip(result[:, :, 1] * (avg / avg_g), 0, 255)
    if avg_r > 0:
        result[:, :, 2] = np.clip(result[:, :, 2] * (avg / avg_r), 0, 255)

    return result.astype(np.uint8)


def enhance_image(img):
    """Image preprocessing (Adaptive Environment Correction).
    - White balance (Gray World) to remove yellow/green cast from lighting.
    - Histogram Equalization on Y channel (YCrCb) to reduce ceiling light hotspots.
    - Bilateral Filter to reduce noise while preserving facial details.
    """
    # 1) White balance
    img = gray_world_white_balance(img)

    # 2) Histogram Equalization on Y channel (YCrCb)
    ycrcb = cv2.cvtColor(img, cv2.COLOR_BGR2YCrCb)
    y, cr, cb = cv2.split(ycrcb)
    y = cv2.equalizeHist(y)
    img = cv2.cvtColor(cv2.merge((y, cr, cb)), cv2.COLOR_YCrCb2BGR)

    # 3) Bilateral filter to reduce camera noise while preserving edges
    enhanced = cv2.bilateralFilter(img, d=5, sigmaColor=50, sigmaSpace=50)

    log_debug('Environmental correction applied: GrayWorldWB + Y-channel hist-eq + bilateral filter')
    return enhanced

def detect_face_region(img):
    cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
    face_cascade = cv2.CascadeClassifier(cascade_path)
    
    def _detect(img_to_check):
        gray = cv2.cvtColor(img_to_check, cv2.COLOR_BGR2GRAY)
        # Relax minNeighbors to detect faces more easily under difficult conditions
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=3, minSize=(40, 40))
        if len(faces) == 0:
            return None
        # Prioritize the largest face (closest to the camera)
        faces = sorted(faces, key=lambda r: r[2] * r[3], reverse=True)
        return faces[0]

    # Try detection at multiple scales (multi-scale retry)
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
            
            # Crop face region with 15% margin to preserve surrounding features
            margin_w = int(w * 0.15)
            margin_h = int(h * 0.15)
            y1 = max(0, y - margin_h)
            y2 = min(img.shape[0], y + h + margin_h)
            x1 = max(0, x - margin_w)
            x2 = min(img.shape[1], x + w + margin_w)
            
            return img[y1:y2, x1:x2], True

    return img, False

def get_face_descriptor_vision(img, api_key):
    """Extract facial landmarks from Google Vision API."""
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
    """Normalize path so cache keys are stable and independent from image content."""
    return os.path.normpath(path).replace('\\', '/').lower()


def _get_user_id_from_reference(reference_path: str) -> Optional[str]:
    """Try to extract user ID from filename or path (e.g. user_123, user-123)."""
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
    """Each user -> one stable JSON cache file.

    If user_id is provided it is used directly (file name user_{id}.json).
    If user_id cannot be determined, fall back to a hash of the path to keep legacy caches working.
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
    """Create a 3x3 grid-based descriptor to stay stable when hair/beard changes."""
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
    """Compare two grid descriptors and return a match score with per-cell details."""
    # Weight the 'golden triangle' region (eyes + nose) in the middle row more heavily.
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

    # Match thresholds tuned for real-world conditions (webcam, indoor lighting).
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
    """Create a template descriptor (grid + lighting info) from a face image."""
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
    """Create dark + noisy variants so AI is familiar with typical indoor conditions."""
    return augment_noisy(augment_dark(img))


def augment_dark(img):
    """Create a darker version to simulate low-light conditions."""
    return cv2.convertScaleAbs(img, alpha=0.6, beta=-30)


def augment_noisy(img):
    """Add Gaussian noise to simulate low-quality camera environments."""
    noise = np.random.normal(0, 12, img.shape).astype(np.int16)
    noisy = np.clip(img.astype(np.int16) + noise, 0, 255).astype(np.uint8)
    return noisy


def rotate_image(img, angle: float):
    """Rotate image around the center while keeping original size."""
    (h, w) = img.shape[:2]
    center = (w // 2, h // 2)
    M = cv2.getRotationMatrix2D(center, angle, 1.0)
    rotated = cv2.warpAffine(img, M, (w, h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REFLECT)
    return rotated


MAX_TEMPLATES = 5


def save_to_cache(reference_path, descriptor, cache_dir, user_id: str = None):
    """Persist technical identity (JSON) into cache keyed by User ID (multi-template).

    If user_id is missing, try to extract it from filename. If still missing -> fall back to path hash.
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

    # If descriptor is already a grid template, merge it into the list.
    if isinstance(descriptor, dict) and 'grid' in descriptor:
        template = {
            'id': hashlib.sha256(json.dumps(descriptor, sort_keys=True).encode('utf-8')).hexdigest()[:16],
            'created_at': datetime.utcnow().isoformat() + 'Z',
            'brightness': descriptor.get('brightness'),
            'lighting': descriptor.get('lighting'),
            'grid': descriptor['grid'],
        }

        # If a very similar template exists, overwrite it; otherwise append as new.
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

        # Keep at most MAX_TEMPLATES samples, newest at the end
        templates = templates[-MAX_TEMPLATES:]
        out = {'templates': templates}
    else:
        # Legacy fallback to avoid breaking previously stored data
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
    """LBPH-based fallback when Vision API fails or environment is offline.

    - Boost brightness/contrast for very dark images.
    - Apply light Gaussian blur to reduce noise under low light.
    - Use CLAHE instead of equalizeHist for local contrast normalization.
    """
    def normalize(i):
        g = cv2.cvtColor(i, cv2.COLOR_BGR2GRAY)
        # Lightly reduce noise before histogram equalization
        g = cv2.GaussianBlur(g, (3, 3), 0)

        # Automatically adjust brightness/contrast based on average intensity
        avg = np.mean(g)
        if avg < 80:
            # Very dark: stronger boost
            g = cv2.convertScaleAbs(g, alpha=1.5, beta=40)
        elif avg < 100:
            # Slightly dark: moderate boost
            g = cv2.convertScaleAbs(g, alpha=1.3, beta=30)

        # CLAHE: local contrast enhancement, better than equalizeHist for faces
        clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
        g = clahe.apply(g)

        return cv2.resize(g, (200, 200))
        
    recognizer = cv2.face.LBPHFaceRecognizer_create()
    recognizer.train([normalize(training_img)], np.array([0]))
    label, confidence = recognizer.predict(normalize(candidate_img))
    return confidence

def compute_face_vector(img, size=(128, 128)):
    """Extract a face vector combining HOG (gradient/shape) and LBP (texture).
    Bilateral filter keeps edges sharp to capture bone structure even in low light.
    """
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.bilateralFilter(gray, d=9, sigmaColor=75, sigmaSpace=75)
    gray = cv2.resize(gray, size)
    gray = cv2.GaussianBlur(gray, (3, 3), 0)

    # 1. Extract HOG vector (overall facial shape and contours)
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

    # 2. Extract LBP vector (skin surface and fine wrinkles)
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

    # 3. Concatenate both vectors into a single high-dimensional descriptor
    combined_vector = np.concatenate((hog_feats, lbp_hist))
    return combined_vector.tolist()


def euclidean_dist(l1, l2):
    """Compute average Euclidean distance between two landmark sets."""
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
    parser.add_argument('--user-id', dest='user_id', help='User ID to keep cache separated per user')
    parser.add_argument('--enroll', action='store_true')
    parser.add_argument('--clear-cache', action='store_true', help='Clear all JSON cache files in face_verify_cache')
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

    # Load and pre-process
    ref_img = load_image(args.reference)
    cand_img = load_image(args.candidate)

    if ref_img is None or cand_img is None:
        # Detailed debug for why loading failed
        if ref_img is None:
            log_debug(f"Reference image load failed: {args.reference}")
            if not os.path.exists(args.reference):
                log_debug(f"File DOES NOT EXIST at {args.reference}")
        if cand_img is None:
            log_debug(f"Candidate image load failed: {args.candidate}")
            
        print(json.dumps({'match': False, 'confidence': 0, 'reason': 'Image loading failed (Check server logs for path details)'}))
        return

    # 1. Pre-processing (brightness, contrast, CLAHE)
    ref_enhanced = enhance_image(ref_img)
    cand_enhanced = enhance_image(cand_img)

    # 2. Face region detection
    ref_face, ref_found = detect_face_region(ref_enhanced)
    cand_face, cand_found = detect_face_region(cand_enhanced)

    # 3. Enrollment flow - store multiple templates (multi-template) for both bright and dark conditions.
    if args.enroll:
        used_img = cand_face if cand_found else cand_enhanced

        # Determine User ID so cache is separated (user_{id}.json)
        user_id = args.user_id or _get_user_id_from_reference(args.reference)

        templates = []

        # 1) Base image after GrayWorld white balance
        gray_world = gray_world_white_balance(used_img)
        desc_gray = build_template_descriptor(gray_world)
        save_to_cache(args.reference, desc_gray, cache_dir, user_id=user_id)
        templates.append({'type': 'gray_world', 'lighting': desc_gray['lighting'], 'brightness': desc_gray['brightness']})

        # 2) & 3) Rotated images at +-15 degrees
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

    # 4. Verification flow
    # Always prefer user-ID-based cache (user_{id}.json) to avoid cross-user contamination.
    user_id = args.user_id or _get_user_id_from_reference(args.reference)
    cache_path = _get_cache_path(args.reference, cache_dir, user_id)

    # If user identity is known but cache is missing, do not apply further fallbacks
    if user_id and not os.path.exists(cache_path):
        print(json.dumps({'match': False, 'confidence': 0, 'reason': 'Face cache missing for user.'}))
        return

    # Legacy handoff: when user_id is unknown, try the old hash-based file to keep compatibility
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

            # Self-learning: if match is strong (score > 0.85), add a new template to update appearance.
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

    # 2) If landmark cache exists and Google Key is available -> compare landmarks (legacy)
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

    # 3) If vector descriptor cache exists (fallback), compare Euclidean distance (legacy)
    if ref_desc and isinstance(ref_desc, dict) and 'descriptor' in ref_desc:
        cand_desc = compute_face_vector(cand_face if cand_found else cand_enhanced)
        ref_vec = np.array(ref_desc['descriptor'], dtype=float)
        cand_vec = np.array(cand_desc, dtype=float)
        
        if len(ref_vec) != len(cand_vec):
            # Bad descriptor length from old model; force mismatch to trigger auth cache update
            dist = 1.0 
        else:
            dist = float(np.linalg.norm(ref_vec - cand_vec))
            
        # Combined HOG+LBP descriptor threshold, allowing reasonable variation 0.80
        match = dist <= 0.80
        print(json.dumps({
            'match': bool(match),
            'confidence': float(max(0.0, 1.0 - (dist / 1.5))),
            'reason': f'Facial Vector distance: {dist:.3f} (Threshold: 0.80)',
            'used_google_vision': False,
            'used_fallback_descriptor': True
        }))
        return

    # Final fallback: LBPH (OpenCV)
    raw_score = lbph_match(ref_face if ref_found else ref_img, cand_face if cand_found else cand_img)
    # LBPH threshold = 65.0
    match = raw_score <= 65.0
    print(json.dumps({
        'match': match,
        'confidence': float(max(0, (100 - raw_score) / 100)),
        'reason': f'LBPH Match raw={raw_score:.1f} (Threshold: 65.0)',
        'used_google_vision': False
    }))

if __name__ == '__main__':
    # Ensure we only print JSON to stdout
    main()
