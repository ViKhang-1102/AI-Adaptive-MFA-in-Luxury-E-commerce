<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a seller account
        $seller = User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Người Bán Hàng',
                'role' => 'seller',
                'is_active' => true,
                'password' => bcrypt('password123'),
            ]
        );

        // Create wallet for seller if not exists
        if (!$seller->wallet) {
            $seller->wallet()->create([
                'balance' => 0,
                'total_received' => 0,
                'total_spent' => 0,
            ]);
        }

        // Get categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $products = [
            // Electronics
            [
                'category_id' => $categories->firstWhere('slug', 'dien-tu')->id ?? 1,
                'name' => 'iPhone 15 Pro Max',
                'description' => 'Smartphone cao cấp nhất của Apple với chip A17 Pro',
                'price' => 29990000,
                'stock' => 50,
                'discount_percent' => 5,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'dien-tu')->id ?? 1,
                'name' => 'MacBook Pro 14"',
                'description' => 'Laptop mạnh mẽ cho công việc chuyên nghiệp',
                'price' => 34990000,
                'stock' => 20,
                'discount_percent' => 0,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'dien-tu')->id ?? 1,
                'name' => 'iPad Air',
                'description' => 'Máy tính bảng mạnh mẽ và đa năng',
                'price' => 15990000,
                'stock' => 35,
                'discount_percent' => 10,
            ],

            // Fashion
            [
                'category_id' => $categories->firstWhere('slug', 'thoi-trang')->id ?? 2,
                'name' => 'Áo Phông Cotton Premium',
                'description' => 'Áo phông 100% cotton, thoáng mát, bền đẹp',
                'price' => 299000,
                'stock' => 200,
                'discount_percent' => 15,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'thoi-trang')->id ?? 2,
                'name' => 'Quần Jean Denim',
                'description' => 'Quần jean chất liệu denim cao cấp, kiểu dáng hiện đại',
                'price' => 599000,
                'stock' => 150,
                'discount_percent' => 0,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'thoi-trang')->id ?? 2,
                'name' => 'Giày Thể Thao Nike',
                'description' => 'Giày thể thao Nike chính hãng, thoáng khí',
                'price' => 2290000,
                'stock' => 100,
                'discount_percent' => 8,
            ],

            // Books
            [
                'category_id' => $categories->firstWhere('slug', 'sach-truyen')->id ?? 3,
                'name' => 'Dạo Bước Trên Ánh Nắng',
                'description' => 'Cuốn sách nổi tiếng của tác giả Nguyễn Nhật Ánh',
                'price' => 95000,
                'stock' => 300,
                'discount_percent' => 10,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'sach-truyen')->id ?? 3,
                'name' => 'Python Programming for Beginners',
                'description' => 'Sách lập trình Python dành cho người mới bắt đầu',
                'price' => 299000,
                'stock' => 80,
                'discount_percent' => 0,
            ],

            // Home & Furniture
            [
                'category_id' => $categories->firstWhere('slug', 'nha-noi-that')->id ?? 4,
                'name' => 'Sofa Da Cao Cấp',
                'description' => 'Sofa da công nghiệp cao cấp, thoải mái và bền',
                'price' => 15990000,
                'stock' => 10,
                'discount_percent' => 20,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'nha-noi-that')->id ?? 4,
                'name' => 'Bàn Làm Việc Gỗ',
                'description' => 'Bàn làm việc gỗ tự nhiên, kiểu dáng sang trọng',
                'price' => 5990000,
                'stock' => 15,
                'discount_percent' => 0,
            ],

            // Beauty
            [
                'category_id' => $categories->firstWhere('slug', 'my-pham-cham-soc')->id ?? 6,
                'name' => 'Kem Dưỡng Da Face Cream',
                'description' => 'Kem dưỡng da ban đêm, dưỡng ẩm sâu',
                'price' => 399000,
                'stock' => 500,
                'discount_percent' => 12,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'my-pham-cham-soc')->id ?? 6,
                'name' => 'Dầu Gội Thiên Nhiên',
                'description' => 'Dầu gội từ thiên nhiên, không hóa chất',
                'price' => 189000,
                'stock' => 300,
                'discount_percent' => 15,
            ],

            // Sports
            [
                'category_id' => $categories->firstWhere('slug', 'the-thao-ngoai-troi')->id ?? 5,
                'name' => 'Vợt Cầu Lông Victor',
                'description' => 'Vợt cầu lông chuyên nghiệp từ thương hiệu Victor',
                'price' => 2990000,
                'stock' => 45,
                'discount_percent' => 10,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'the-thao-ngoai-troi')->id ?? 5,
                'name' => 'Bộ Yoga Mat 6mm',
                'description' => 'Thảm yoga cao cấp, mềm mại và chống trơn',
                'price' => 599000,
                'stock' => 100,
                'discount_percent' => 20,
            ],

            // Toys
            [
                'category_id' => $categories->firstWhere('slug', 'do-choi-tre-em')->id ?? 7,
                'name' => 'LEGO City Police Station',
                'description' => 'Bộ LEGO xây dựng đồn cảnh sát, 1000+ mảnh',
                'price' => 1990000,
                'stock' => 30,
                'discount_percent' => 8,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'do-choi-tre-em')->id ?? 7,
                'name' => 'Xe Điều Khiển Từ Xa',
                'description' => 'Xe RC tốc độ cao, điều khiển từ xa 2.4GHz',
                'price' => 799000,
                'stock' => 50,
                'discount_percent' => 15,
            ],

            // Food & Drinks
            [
                'category_id' => $categories->firstWhere('slug', 'thuc-pham-do-uong')->id ?? 8,
                'name' => 'Cà Phê Espresso Premium',
                'description' => 'Cà phê rang xay cao cấp từ Ethiopia',
                'price' => 299000,
                'stock' => 200,
                'discount_percent' => 10,
            ],
            [
                'category_id' => $categories->firstWhere('slug', 'thuc-pham-do-uong')->id ?? 8,
                'name' => 'Trà Oolong Đài Loan',
                'description' => 'Trà Oolong nguyên chất từ Đài Loan',
                'price' => 199000,
                'stock' => 150,
                'discount_percent' => 5,
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                [
                    'seller_id' => $seller->id,
                    'name' => $productData['name'],
                ],
                array_merge($productData, [
                    'seller_id' => $seller->id,
                    'slug' => Str::slug($productData['name']) . '-' . uniqid(),
                ])
            );
        }
    }
}
