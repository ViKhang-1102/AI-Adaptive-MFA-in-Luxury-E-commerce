import cv2
import numpy as np

img = np.full((100, 100, 3), 255, dtype=np.uint8)
cv2.putText(img, 'A', (20, 70), cv2.FONT_HERSHEY_SIMPLEX, 2, (0, 0, 0), 3)
cv2.imwrite('test-face.png', img)
print('wrote test-face.png', img.shape)
