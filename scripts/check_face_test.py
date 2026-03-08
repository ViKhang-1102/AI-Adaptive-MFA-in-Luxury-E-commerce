import cv2
import os

path = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'public', 'face_test.png')
path = os.path.abspath(path)

img = cv2.imread(path)
print('path:', path)
print('loaded:', img is not None)
if img is None:
    raise SystemExit(1)

cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
face_cascade = cv2.CascadeClassifier(cascade_path)

gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=4, minSize=(30, 30))
print('faces:', faces)
print('shape:', img.shape)
