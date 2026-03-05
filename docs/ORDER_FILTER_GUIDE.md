# 📖 Hướng dẫn sử dụng Order & Filter System

## 🛒 Hệ thống Đặt Hàng (Order System)

### Luồng Đặt Hàng Tiêu Chuẩn

```
Khách hàng đặt hàng
    ↓
[Chọn sản phẩm] → [Thanh toán] → [Xác nhận]
    ↓
Status: PENDING (Chờ bán hàng xác nhận)
    ↓
Bán hàng xác nhận (seller confirms)
    ↓
Status: CONFIRMED
    ↓
Bán hàng chuẩn bị hàng
    ↓
Status: PROCESSING
    ↓
Bán hàng gửi hàng
    ↓
Status: SHIPPED
    ↓
[Webhook từ Shipper: POST /api/shipper/update-status]
    ↓
Status: DELIVERED + delivered_at timestamp
    ↓
✅ Khách hàng có thể REVIEW/ĐÁN GIÁ
```

### Các Trạng Thái Đơn Hàng (Order Status)

| Status | Tiếng Việt | Khách hàng có thể làm gì | Bán hàng có thể làm gì |
|---|---|---|---|
| `pending` | Chờ xác nhận | Hủy đơn | Xác nhận / Hủy |
| `confirmed` | Đã xác nhận | Chờ | Chuẩn bị hàng |
| `processing` | Đang xử lý | Chờ | Gửi hàng |
| `shipped` | Đang giao | Chờ | Chờ shipper xác nhận |
| `delivered` | Đã giao | **Review/Đánh giá** | Hoàn tất |
| `cancelled` | Đã hủy | - | - |
| `returned` | Trả hàng | Chờ hoàn tiền | Xử lý trả hàng |

---

## ⭐ Hệ thống Review/Đánh Giá

### Điều kiện để Review

✅ **Cho phép review khi:**
- Order status = `DELIVERED`
- Khách hàng là người mua hàng
- Chưa review sản phẩm này trên đơn này trước đây
- Sản phẩm có trong đơn hàng

❌ **KHÔNG cho phép review khi:**
- Status khác `DELIVERED` (pending, processing, shipped, etc.)
- Người review không phải khách hàng
- Đã review sản phẩm này rồi

### Cách Review

```php
// Endpoint: POST /products/{product}/reviews
{
    "rating": 5,              // 1-5 stars (bắt buộc)
    "comment": "Sản phẩm tốt", // optional
    "images": [file1, file2]  // optional
}
```

### Ví dụ Code (Test)

```php
// Chỉ những order "delivered" mới có thể review
$eligibleOrders = Order::where('status', 'delivered')
    ->where('customer_id', Auth::id())
    ->get();

// Kiểm tra trước khi tạo review
if (!$eligibleOrder) {
    return back()->with('error', 'Bạn chỉ có thể đánh giá sau khi nhận hàng');
}
```

---

## 🔍 Hệ thống Lộc Sản Phẩm (Filter System)

### 1️⃣ Tìm Kiếm Theo Tên

```php
$products = Product::where('name', 'like', '%iPhone%')->get();
```

**Ví dụ:** Tìm "iPhone" → kết quả tất cả sản phẩm có chữ "iPhone"

### 2️⃣ Lộc Theo Danh Mục

```php
$products = Product::where('category_id', 5)->get();
```

**Ví dụ:** Danh mục `5` → Điện thoại

### 3️⃣ Lộc Theo Khoảng Giá

```php
$products = Product::whereBetween('price', [100000, 500000])->get();
// Khoảng: 100k - 500k VND
```

### 4️⃣ Lộc Theo Người Bán

```php
$products = Product::where('seller_id', 10)->get();
```

### 5️⃣ Lộc Theo Tình Trạng Hàng

```php
// Còn hàng
$inStock = Product::where('stock', '>', 0)->get();

// Hết hàng
$outOfStock = Product::where('stock', '<=', 0)->get();
```

### 6️⃣ Kết Hợp Nhiều Lộc (Advanced)

```php
// Danh mục 5 + Giá 100k-500k + Còn hàng + Sắp xếp theo giá
$products = Product::where('category_id', 5)
    ->whereBetween('price', [100000, 500000])
    ->where('stock', '>', 0)
    ->orderBy('price', 'asc')  // Giá từ thấp đến cao
    ->get();
```

### Request URL Example

```
GET /products?category=5&price_min=100000&price_max=500000&sort=price
```

---

## 🔗 Webhook API (Shipper)

### Mục đích
Dùng để cập nhật trạng thái đơn hàng từ `shipped` → `delivered` khi shipper giao hàng

### Endpoint
```
POST /api/shipper/update-status
```

### Request Format
```json
{
    "order_id": 5,
    "secret_key": "LUXGUARD_SECRET_2026"
}
```

### Response
```json
{
    "message": "Order status updated"
}
```

### Ví dụ cURL

```bash
curl -X POST http://localhost/api/shipper/update-status \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": 5,
    "secret_key": "LUXGUARD_SECRET_2026"
  }'
```

### Test bằng PHP

```php
// Chạy: php test-shipper-webhook.php
php artisan tinker
>>> Post::create(['order_id' => 5, 'secret_key' => 'LUXGUARD_SECRET_2026'])
```

---

## 📊 Database Relations

### Order Model
```php
class Order extends Model {
    // Người mua
    public function customer()      // User
    
    // Người bán
    public function seller()        // User
    
    // Các mặt hàng trong đơn
    public function items()         // OrderItem[]
    
    // Thanh toán
    public function payment()       // Payment
    
    // Reviews (đánh giá sản phẩm)
    public function reviews()       // ProductReview[]
}
```

### OrderItem Model
```php
class OrderItem extends Model {
    public function order()         // Order
    public function product()       // Product
}
```

### OrderStatus Flow (SQL)
```sql
-- Query tất cả đơn hàng chờ review
SELECT * FROM orders WHERE status = 'delivered' AND customer_id = ?;

-- Cập nhật trạng thái via webhook
UPDATE orders SET status = 'delivered', delivered_at = NOW() WHERE id = ?;
```

---

## 🧪 Testing Guide

### Chạy Full Test

```bash
php test-order-and-filters.php
```

### Test từng chức năng

#### Test Order Placement
```php
// Tạo đơn COD
Order::create([
    'customer_id' => 1,
    'seller_id' => 2,
    'payment_method' => 'cod',
    'status' => 'pending',
    'total_amount' => 100000,
]);
```

#### Test Review Constraints
```php
// Kiểm tra order có thể review chưa
$isDelivered = Order::find(5)->status === 'delivered';
echo $isDelivered ? 'Có thể review' : 'Chưa thể review';
```

#### Test Filters
```php
// Tìm sản phẩm
$products = Product::where('name', 'like', '%test%')
    ->where('category_id', 1)
    ->whereBetween('price', [50000, 100000])
    ->where('stock', '>', 0)
    ->get();
```

---

## ⚠️ Lưu Ý

1. **Webhook Secret Key:** Luôn sử dụng `LUXGUARD_SECRET_2026` để bảo mật
2. **Review Status:** Chỉ check `order.status === 'delivered'`
3. **Timestamp:** Luôn ghi `delivered_at` khi update status
4. **Order Items:** Phải lưu `product_name`, `product_price`, `quantity`, `subtotal`

---

**Cập nhật lần cuối:** 04/03/2026
