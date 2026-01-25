<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Giảm Giá Điện Tử 50%',
                'description' => 'Khuyến mãi lớn cho tất cả sản phẩm điện tử',
                'image' => '/images/banners/electronics.jpg',
                'link' => '/products?category=dien-tu',
                'banner_type' => 'promo',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Thời Trang Mùa Hè 2026',
                'description' => 'Bộ sưu tập thời trang mới nhất mùa hè',
                'image' => '/images/banners/fashion.jpg',
                'link' => '/products?category=thoi-trang',
                'banner_type' => 'promo',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Nhà Sách Khuyến Mãi',
                'description' => 'Mua sách được giảm giá đến 30%',
                'image' => '/images/banners/books.jpg',
                'link' => '/products?category=sach-truyen',
                'banner_type' => 'category',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Chào Mừng Bạn Đến E-Commerce',
                'description' => 'Nơi mua sắm trực tuyến uy tín và an toàn',
                'image' => '/images/banners/welcome.jpg',
                'link' => '/',
                'banner_type' => 'main',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Mỹ Phẩm Chính Hãng',
                'description' => 'Mỹ phẩm và chăm sóc da chính hãng từ các thương hiệu nổi tiếng',
                'image' => '/images/banners/beauty.jpg',
                'link' => '/products?category=my-pham-cham-soc',
                'banner_type' => 'promo',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(
                ['title' => $banner['title']],
                $banner
            );
        }
    }
}
