# Tài Liệu Hệ Thống AI - E-commerce 2026

Tài liệu này chi tiết hóa cấu trúc, công nghệ, công cụ và thư viện được sử dụng cho các thành phần AI trong dự án E-commerce 2026.

## 1. Tổng Quan (Overview)
Hệ thống tích hợp AI nhằm hai mục đích chính:
1.  **Xác thực sinh trắc học (Face Verification)**: Tăng cường bảo mật cho các giao dịch quan trọng và ví điện tử.
2.  **Chấm điểm rủi ro (AI-Driven Risk Scoring)**: Tự động phân tích và phát hiện các hành vi gian lận hoặc chiếm đoạt tài khoản (ATO) trong thời gian thực.

---

## 2. Kiến Trúc AI (Architecture)

Hệ thống được thiết kế theo mô hình lai (Hybrid):
-   **Face Verification**: Chạy cục bộ (local) thông qua các script Python được gọi trực tiếp từ Laravel.
-   **Risk Scoring**: Chạy dưới dạng một Microservice độc lập (FastAPI) giao tiếp qua HTTP API, kết hợp giữa học máy truyền thống (Scikit-learn) và mô hình ngôn ngữ lớn (OpenAI GPT).

---

## 3. Công Nghệ & Công Cụ (Technologies & Tools)

### Ngôn ngữ & Framework:
-   **Python 3.10+**: Ngôn ngữ chính cho các tác vụ AI/ML.
-   **FastAPI**: Framework web hiệu năng cao dùng để xây dựng Risk Scoring API.
-   **Laravel (PHP)**: Backend chính điều phối và gọi các dịch vụ AI.
-   **Uvicorn**: ASGI server để chạy FastAPI.

### Công cụ phát triển:
-   **Haar Cascades**: Công cụ nhận diện khuôn mặt cổ điển của OpenCV.
-   **OpenAI API (GPT-4o-mini)**: "Security Guard Agent" thực hiện phân tích hành vi chuyên sâu.

---

## 4. Thư Viện Sử Dụng (Libraries)

### Phía Python (AI Services):
-   `opencv-python`: Xử lý hình ảnh, phát hiện khuôn mặt và tiền xử lý môi trường.
-   `numpy`: Tính toán ma trận và xử lý dữ liệu ảnh.
-   `scikit-learn`: Triển khai mô hình `IsolationForest` để phát hiện bất thường (anomaly detection).
-   `openai`: Giao tiếp với các mô hình ngôn ngữ của OpenAI.
-   `pydantic`: Ràng buộc và kiểm tra kiểu dữ liệu đầu vào cho API.
-   `python-dotenv`: Quản lý các biến môi trường (API keys).
-   `requests`: Thực hiện các yêu cầu HTTP.

---

## 5. Chi Tiết Các Thành Phần

### A. Xác Thực Khuôn Mặt (Face Verification)
-   **Vị trí**: `scripts/face_verify.py` & `app/Services/FaceVerificationService.php`.
-   **Chức năng**:
    *   **Tiền xử lý**: Cân bằng trắng (Gray World), cân bằng biểu đồ (Histogram Equalization), lọc song phương (Bilateral Filter) để giảm nhiễu.
    *   **Phát hiện**: Sử dụng `CascadeClassifier` để tìm vùng khuôn mặt.
    *   **So sánh**: So sánh ảnh chụp trực tiếp với ảnh định danh đã lưu trong hệ thống.

### B. Chấm Điểm Rủi Ro (Risk Scoring)
-   **Vị trí**: `risk_scoring_api/main.py` & `app/Services/RiskAssessmentService.php`.
-   **Chức năng**:
    *   **Phân tích thống kê**: Sử dụng `Isolation Forest` để so sánh giao dịch hiện tại với các mẫu dữ liệu bất thường.
    *   **Phân tích hành vi**: Gửi ngữ cảnh (số tiền, phương thức thanh toán, số lần đổi IP, dấu vân tay thiết bị) cho AI Agent (GPT-4o-mini) để đưa ra đánh giá rủi ro từ 0-100 và lời giải thích bằng tiếng Anh.

---

## 6. Ưu & Nhược Điểm (Pros & Cons)

### Ưu điểm:
-   **Độ chính xác cao**: Kết hợp giữa toán học thống kê và khả năng lý luận của LLM giúp phát hiện các mẫu gian lận phức tạp.
-   **Giải thích được (Explainable AI)**: AI cung cấp lý do cụ thể tại sao một giao dịch bị coi là rủi ro, giúp quản trị viên dễ dàng ra quyết định.
-   **Bảo mật đa lớp**: Xác thực khuôn mặt giúp ngăn chặn việc rút tiền trái phép ngay cả khi tài khoản bị lộ mật khẩu.
-   **Xử lý môi trường tốt**: Script Python có các thuật toán hiệu chỉnh ánh sáng và nhiễu camera.

### Nhược điểm:
-   **Phụ thuộc bên thứ ba**: Risk Scoring API phụ thuộc vào OpenAI; nếu mất kết nối hoặc hết hạn mức API, hệ thống phải dùng fallback local.
-   **Độ trễ (Latency)**: Việc gọi LLM qua API có thể mất từ 1-3 giây, ảnh hưởng nhẹ đến trải nghiệm người dùng.
-   **Face Verification cơ bản**: Sử dụng Haar Cascades dễ bị đánh lừa bởi ảnh chụp (thiếu tính năng Liveness Detection).

---

## 7. Cải Tiến (Improvements)

1.  **Nâng cấp mô hình Face**: Thay thế Haar Cascades bằng các mô hình Deep Learning hiện đại như **Mediapipe** hoặc **InsightFace** để tăng độ chính xác và tốc độ.
2.  **Liveness Detection**: Thêm kiểm tra cử động (nháy mắt, quay đầu) để chống giả mạo bằng hình ảnh/video.
3.  **Local SLM (Small Language Model)**: Sử dụng các mô hình nhỏ chạy tại chỗ (như Llama 3-8B hoặc Phi-3) để giảm chi phí OpenAI và tăng tốc độ xử lý.
4.  **Học máy trực tuyến (Online Learning)**: Cập nhật mô hình `IsolationForest` liên tục từ dữ liệu giao dịch thực tế của người dùng thay vì dùng dữ liệu mẫu (dummy data).
5.  **Tối ưu hóa Cache**: Lưu trữ các đặc trưng khuôn mặt (embeddings) thay vì so sánh ảnh thô để tăng tốc độ xác thực.
