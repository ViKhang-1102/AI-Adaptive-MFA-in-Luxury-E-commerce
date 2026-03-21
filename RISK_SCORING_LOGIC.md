# Tài liệu Chi tiết: Logic Tính toán Điểm Rủi ro LuxGuard AI

Tài liệu này giải thích cách hệ thống LuxGuard AI và công cụ Heuristic dự phòng (Fallback) tính toán điểm rủi ro cho mỗi giao dịch hoặc hành động đăng nhập của người dùng.

---

## 1. Nguyên tắc Tổng quát
Điểm rủi ro được tính trên thang điểm từ **0 đến 100**:
- **0 - 39 (Low):** An toàn. Cho phép giao dịch ngay lập tức (ALLOW).
- **40 - 64 (Medium):** Nghi vấn. Yêu cầu xác thực OTP qua Email.
- **65 - 84 (High):** Rủi ro cao. Yêu cầu quét khuôn mặt (FACEID) hoặc xác thực sinh trắc học.
- **85 - 100 (Critical):** Nguy hiểm. Tự động chặn giao dịch (BLOCK) và yêu cầu Admin xem xét thủ công.

---

## 2. Các Chỉ số Tính điểm (Risk Metrics)

### A. Phân tích Giá trị Giao dịch (Amount Analysis)
Số tiền giao dịch càng lớn, rủi ro cơ sở càng cao:
- **> $10,000:** +60 điểm.
- **$5,000 - $10,000:** +50 điểm.
- **$1,000 - $5,000:** +30 điểm.
- **<= $1,000:** +0 điểm.

### B. Phương thức Thanh toán (Payment Method)
- **Thanh toán Online (PayPal/Credit Card):** +20 điểm (do rủi ro gian lận thẻ cao).
- **COD (Thanh toán khi nhận hàng):** +0 điểm.

### C. Độ uy tín của Tài khoản (Account Trust & Velocity)
Hệ thống đặc biệt khắt khe với tài khoản mới:
- **Tài khoản mới (< 24 giờ):** +35 điểm.
- **Tài khoản mới (< 7 ngày):** +15 điểm.
- **Tài khoản lâu năm (> 30 ngày):** -5 điểm (Thưởng tin cậy).

### D. Kiểm tra Tần suất Hoạt động (Activity Velocity)
Ngăn chặn các cuộc tấn công Brute-force hoặc "quét" đơn hàng:
- **Tần suất thao tác (Audit Storm):**
    - >= 10 hành động/giờ: +45 điểm.
    - >= 4 hành động/giờ: +20 điểm.
- **Tần suất đặt hàng (Daily Spree):**
    - >= 5 đơn hàng/24 giờ: +40 điểm.
    - >= 2 đơn hàng/24 giờ: +15 điểm.

### E. Thiết bị & Định danh (Device Fingerprinting)
- **Thiết bị mới/Chưa xác thực:** +45 điểm.
- **Thiết bị đã xác thực (Verified Device):**
    - Nếu tài khoản < 24h: -5 điểm (Giảm thưởng để kiểm soát tài khoản mới).
    - Nếu tài khoản > 24h: -20 điểm (Thưởng thiết bị tin cậy).

### F. Thói quen Chi tiêu (Spending Patterns)
Hệ thống so sánh với giá trị trung bình của các đơn hàng đã hoàn thành trước đó:
- **Giao dịch nằm trong ngưỡng bình thường (<= 150% trung bình cũ):** -15 điểm.
- **Giao dịch bất thường (vượt xa lịch sử chi tiêu):** Không được giảm điểm.

### G. Phần thưởng Khách hàng Thân thiết (Loyalty Rewards)
Dành cho các tài khoản có lịch sử mua hàng tốt:
- **Hạng Elite (Tổng chi tiêu >= $5,000 & >= 10 đơn hàng):** -35 điểm.
- **Hạng High Trust (Tổng chi tiêu >= $1,000 & >= 5 đơn hàng):** -20 điểm.

---

## 3. Ví dụ Minh họa

### Kịch bản 1: Tài khoản mới, giao dịch lớn
*Người dùng mới tạo tài khoản 2 giờ trước, đặt mua đồng hồ Rolex $3,500 bằng PayPal trên máy tính mới.*
1. Giá trị ($3,500): **+30**
2. Thanh toán Online: **+20**
3. Tài khoản mới (< 24h): **+35**
4. Thiết bị mới: **+45**
**Tổng điểm: 100 (CRITICAL)** -> **KẾT QUẢ: BLOCK** (Chặn và yêu cầu Admin duyệt).

### Kịch bản 2: Khách hàng thân thiết, thiết bị quen thuộc
*Người dùng đã mua hàng 2 năm, đặt đơn hàng $500 bằng COD trên điện thoại đã xác thực FaceID trước đó.*
1. Giá trị ($500): **+0**
2. Phương thức COD: **+0**
3. Tài khoản lâu năm: **-5**
4. Thiết bị đã xác thực: **-20**
5. Chi tiêu bình thường: **-15**
**Tổng điểm: 0 (LOW)** -> **KẾT QUẢ: ALLOW** (Duyệt ngay lập tức).

---

## 4. Cơ chế Học máy (Learning Mechanism)
Sau mỗi lần người dùng vượt qua **FaceID** hoặc **OTP** thành công trên một thiết bị, hệ thống sẽ lưu `device_fingerprint` vào bảng `verified_devices`. Trong các lần đăng nhập sau, điểm rủi ro sẽ tự động giảm xuống, giúp người dùng không phải xác thực lại nhiều lần trên cùng một thiết bị tin cậy.
