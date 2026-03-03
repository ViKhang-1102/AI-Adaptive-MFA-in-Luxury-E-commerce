# API Routes & Endpoints Documentation

Complete reference for all routes available in the E-Commerce Platform.

## Route Organization

All routes are organized by user role and feature area:
- **Public Routes** - No authentication required
- **Auth Routes** - Authentication endpoints
- **Customer Routes** - Protected by `auth` middleware
- **Seller Routes** - Protected by `role:seller` middleware  
- **Admin Routes** - Protected by `role:admin` middleware

## 📌 Public Routes

These routes are accessible to anyone (no login required).

### Home & Navigation
```
GET    /                               Home page with banners and featured products
GET    /products                       All products with search/filter/pagination
GET    /products/{product}             Product detail page
GET    /categories                     All product categories
GET    /categories/{category}          Category with its products
```

### Authentication
```
GET    /login                          Login page
POST   /login                          Login submission (email + password)
GET    /register                       Registration form
POST   /register                       User registration (email, password, role)
POST   /logout                         Logout (requires auth)
GET    /forgot-password                Password reset page (if implemented)
POST   /forgot-password                Reset password request (if implemented)
```

## 🛍️ Customer Routes

Protected by `auth` middleware. Only authenticated customers and admins can access.

### Shopping Cart
```
GET    /cart                           View shopping cart
POST   /cart/add                       Add product to cart
       Parameters: product_id, quantity
POST   /cart/update/{cart_item}        Update cart item quantity
       Parameters: quantity
DELETE /cart/remove/{cart_item}        Remove item from cart
DELETE /cart/clear                     Clear entire cart
```

### Checkout & Orders
```
GET    /checkout                       Checkout page with address/payment selection
POST   /orders                         Place new order
       Parameters: address_id, payment_method
GET    /orders                         View customer's orders (paginated)
GET    /orders/{order}                 View specific order details
POST   /orders/{order}/cancel          Cancel pending/confirmed order
       Parameters: reason (optional)
```

### Wishlist
```
GET    /wishlist                       View saved products
POST   /wishlist/add/{product}         Add product to wishlist
DELETE /wishlist/remove/{product}      Remove product from wishlist
POST   /wishlist/move-to-cart/{wish}   Move wishlist item to cart
```

### Addresses
```
GET    /addresses                      List all customer addresses
POST   /addresses                      Create new address
       Parameters: name, phone, address_line1, address_line2, city, postal_code
GET    /addresses/{address}            View address details
POST   /addresses/{address}/edit       Update address
DELETE /addresses/{address}            Delete address
POST   /addresses/{address}/default    Set as default address
```

### User Profile
```
GET    /profile                        View user profile
POST   /profile                        Update profile information
       Parameters: name, phone, avatar
POST   /profile/password               Change password
       Parameters: current_password, new_password, new_password_confirmation
POST   /profile/avatar                 Upload profile picture
       Parameters: avatar (file)
```

### E-Wallet (Customer)
```
GET    /wallet                         View wallet balance
GET    /wallet/transactions            View transaction history (paginated)
GET    /wallet/transactions/{trans}    View transaction details
```

## 🏪 Seller Routes

Protected by `role:seller` middleware. Only sellers can access.

All seller routes are prefixed with `/seller/`

### Seller Dashboard
```
GET    /seller/dashboard               Seller dashboard with statistics
                                       (total products, orders, revenue, pending orders)
```

### Product Management
```
GET    /seller/products                List seller's products (search, sort, filter)
GET    /seller/products/create         Create product form
POST   /seller/products                Save new product
       Parameters: name, description, category_id, price, stock, discount_percent, 
                  discount_start, discount_end
GET    /seller/products/{product}      View/edit product details
POST   /seller/products/{product}/edit Update product
DELETE /seller/products/{product}      Delete product
POST   /seller/products/{product}/images Upload product images
DELETE /seller/products/{product}/images/{image} Delete product image
```

### Category Management (Seller)
```
GET    /seller/categories              List available categories for seller
GET    /seller/categories/create       Create seller category selection
POST   /seller/categories              Add categories to seller
DELETE /seller/categories/{category}   Remove category from seller
```

### Order Management (Seller)
```
GET    /seller/orders                  List seller's orders (filter by status)
       Query params: status=pending|confirmed|processing|shipped|delivered
GET    /seller/orders/{order}          View order details
POST   /seller/orders/{order}/confirm  Confirm order from customer
POST   /seller/orders/{order}/reject   Reject order with reason
       Parameters: reason
POST   /seller/orders/{order}/cancel   Cancel confirmed order
       Parameters: reason
POST   /seller/orders/{order}/process  Mark order as processing
POST   /seller/orders/{order}/ship     Mark order as shipped
       Parameters: tracking_number (optional)
POST   /seller/orders/{order}/deliver  Mark order as delivered
```

### Seller Wallet
```
GET    /seller/wallet                  View wallet balance and transactions
GET    /seller/wallet/transactions     Transaction history (paginated)
GET    /seller/wallet/transactions/{trans} View transaction details
POST   /seller/wallet/withdraw         Request withdrawal (if implemented)
```

## 👨‍💼 Admin Routes

Protected by `role:admin` middleware. Only admin can access.

All admin routes are prefixed with `/admin/`

### Admin Dashboard
```
GET    /admin/dashboard                Admin dashboard with platform statistics
                                       (users, sellers, orders, revenue, today's stats)
```

### Customer Management
```
GET    /admin/customers                List all customers (search, paginate)
GET    /admin/customers/{user}         View customer details
POST   /admin/customers/{user}/edit    Update customer information
DELETE /admin/customers/{user}         Delete customer account
POST   /admin/customers/{user}/activate Activate account
POST   /admin/customers/{user}/deactivate Deactivate account
POST   /admin/customers/{user}/orders  View customer orders
```

### Seller Management
```
GET    /admin/sellers                  List all sellers (search, paginate)
GET    /admin/sellers/create           Create new seller form
POST   /admin/sellers                  Create new seller account
       Parameters: name, email, password, business_info
GET    /admin/sellers/{user}           View seller details
POST   /admin/sellers/{user}/edit      Update seller information
DELETE /admin/sellers/{user}           Delete seller account
POST   /admin/sellers/{user}/activate  Activate seller
POST   /admin/sellers/{user}/deactivate Deactivate seller
GET    /admin/sellers/{user}/products  View seller's products
GET    /admin/sellers/{user}/orders    View seller's orders
```

### Category Management (Admin)
```
GET    /admin/categories               List all categories
GET    /admin/categories/create        Create category form
POST   /admin/categories               Save new category
       Parameters: name, parent_id (optional), description, icon
GET    /admin/categories/{category}    View category details
POST   /admin/categories/{category}/edit Update category
DELETE /admin/categories/{category}    Delete category (if no products)
```

### Banner Management
```
GET    /admin/banners                  List all banners
GET    /admin/banners/create           Create banner form
POST   /admin/banners                  Save new banner
       Parameters: title, image, link, start_date, end_date, is_active, sort_order
GET    /admin/banners/{banner}         View banner details
POST   /admin/banners/{banner}/edit    Update banner
DELETE /admin/banners/{banner}         Delete banner
POST   /admin/banners/{banner}/image   Upload/replace banner image
```

### Fee Management
```
GET    /admin/fees                     View current fees configuration
GET    /admin/fees/edit                Edit fees form
POST   /admin/fees/{fee}/update        Update fee
       Parameters: platform_fee_percent, transaction_fee_percent, 
                  shipping_fee_default
```

### Wallet Management (Admin)
```
GET    /admin/wallet                   View admin wallet balance
GET    /admin/wallet/transactions      View wallet transaction history
GET    /admin/wallet/transactions/{trans} View transaction details
```

### Order Management (Admin)
```
GET    /admin/orders                   List all platform orders
       Query params: status, date_from, date_to, seller_id
GET    /admin/orders/{order}           View order details
GET    /admin/orders/{order}/invoice   Generate invoice (if implemented)
```

## 🔑 Authentication & Authorization

### User Roles
```
Customer   - Can access /cart, /orders, /profile, /wishlist, /addresses
Seller     - Can access /seller/* routes for managing products/orders
Admin      - Can access /admin/* routes for platform management
```

### Middleware Stack
```
auth               - User must be logged in
role:customer      - User must have customer role
role:seller        - User must have seller role
role:admin         - User must have admin role
verified           - Email must be verified (if implemented)
```

## 📊 Query Parameters & Filters

### Product Listing
```
GET /products?
    search=keyword         - Search by product name
    category=5            - Filter by category ID
    sort=price            - Sort by: newest, price-asc, price-desc, popular
    min_price=10000       - Minimum price filter
    max_price=1000000     - Maximum price filter
    rating=4              - Minimum rating filter
    in_stock=true         - Only in-stock products
    discount=true         - Only discounted products
    page=2                - Pagination page number
    per_page=12           - Items per page (default 12)
```

### Order Listing
```
GET /orders?
    status=pending        - Filter by status: pending, confirmed, processing, shipped, delivered
    date_from=2025-01-01  - Orders from date
    date_to=2025-12-31    - Orders to date
    sort=newest           - Sort by: newest, oldest
    page=1                - Pagination
    per_page=10           - Items per page
```

### Customer Listing
```
GET /admin/customers?
    search=email          - Search by name or email
    status=active         - Filter by: active, inactive
    sort=newest           - Sort by: newest, oldest
    page=1                - Pagination
```

## 📤 Request/Response Examples

### Create Product (Seller)
```bash
POST /seller/products
Content-Type: application/json

{
    "name": "iPhone 13 Pro",
    "description": "Latest iPhone model",
    "category_id": 5,
    "price": 25000000,
    "stock": 50,
    "discount_percent": 10,
    "discount_start": "2025-01-01",
    "discount_end": "2025-01-31"
}
```

### Place Order (Customer)
```bash
POST /orders
Content-Type: application/json

{
    "address_id": 1,
    "payment_method": "cod"
}
```

### Create Category (Admin)
```bash
POST /admin/categories
Content-Type: application/json

{
    "name": "Electronics",
    "description": "Electronic devices",
    "parent_id": null,
    "icon": "fa-laptop"
}
```

## 🔍 Status Codes

```
200 OK                 - Request successful
201 Created            - Resource created successfully
204 No Content         - Successful, no content to return
400 Bad Request        - Invalid request parameters
401 Unauthorized       - User not authenticated
403 Forbidden          - User not authorized for this action
404 Not Found          - Resource not found
422 Unprocessable      - Validation errors
500 Server Error       - Internal server error
```

## 📝 Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        "id": 1,
        "name": "Product Name",
        ...
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Error details"]
    }
}
```

## 🔐 CSRF Protection

All POST, PUT, PATCH, DELETE requests require CSRF token:
```html
<input type="hidden" name="_token" value="{{ csrf_token() }}">
```

Or in AJAX:
```javascript
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

## 🚀 Rate Limiting

API rate limiting (ready for implementation):
- General endpoint: 60 requests per minute
- Authentication: 5 attempts per minute
- File upload: 10 requests per minute

## 📱 API Testing Tools

Recommended tools for testing API endpoints:
- **Postman** - Desktop application for API testing
- **Insomnia** - Lightweight REST client
- **Thunder Client** - VS Code extension
- **cURL** - Command-line tool

### Example cURL Request
```bash
curl -X GET http://localhost:8000/products \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

## 📚 Related Files

- **Routes Definition**: [routes/web.php](routes/web.php)
- **Controllers**: [app/Http/Controllers/](app/Http/Controllers/)
- **Middleware**: [app/Http/Middleware/](app/Http/Middleware/)
- **Models**: [app/Models/](app/Models/)

## 🔄 Common Workflows

### Customer Shopping Workflow
```
1. GET /products                    - Browse products
2. GET /products/{id}               - View product details
3. POST /cart/add                   - Add to cart
4. GET /cart                        - Review cart
5. GET /checkout                    - Proceed to checkout
6. POST /orders                     - Place order
7. GET /orders/{id}                 - Track order
```

### Seller Order Fulfillment
```
1. GET /seller/orders?status=pending     - View pending orders
2. GET /seller/orders/{id}               - Review order details
3. POST /seller/orders/{id}/confirm      - Confirm order
4. POST /seller/orders/{id}/process      - Mark as processing
5. POST /seller/orders/{id}/ship         - Mark as shipped
6. POST /seller/orders/{id}/deliver      - Mark as delivered
```

### Admin Dashboard Usage
```
1. GET /admin/dashboard             - View statistics
2. GET /admin/customers             - Manage users
3. GET /admin/categories            - Manage categories
4. GET /admin/banners               - Manage promotions
5. GET /admin/fees                  - Configure fees
6. GET /admin/wallet                - Monitor wallet
```

---

**API Documentation Last Updated**: January 2026
**Platform Version**: 1.0
**All Routes**: 40+ endpoints
**Status**: ✅ Production Ready
