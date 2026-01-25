<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Điện Tử',
                'description' => 'Các sản phẩm điện tử như điện thoại, laptop, tablet',
                'slug' => 'dien-tu'
            ],
            [
                'name' => 'Thời Trang',
                'description' => 'Quần áo, giày dép, phụ kiện thời trang',
                'slug' => 'thoi-trang'
            ],
            [
                'name' => 'Sách & Truyện',
                'description' => 'Sách, truyện tranh, tài liệu học tập',
                'slug' => 'sach-truyen'
            ],
            [
                'name' => 'Nhà & Nội Thất',
                'description' => 'Đồ nội thất, trang trí nhà cửa',
                'slug' => 'nha-noi-that'
            ],
            [
                'name' => 'Thể Thao & Ngoài Trời',
                'description' => 'Dụng cụ thể thao, áo quần thể thao',
                'slug' => 'the-thao-ngoai-troi'
            ],
            [
                'name' => 'Mỹ Phẩm & Chăm Sóc',
                'description' => 'Mỹ phẩm, sản phẩm chăm sóc da, tóc',
                'slug' => 'my-pham-cham-soc'
            ],
            [
                'name' => 'Đồ Chơi & Trẻ Em',
                'description' => 'Đồ chơi, sản phẩm cho trẻ em',
                'slug' => 'do-choi-tre-em'
            ],
            [
                'name' => 'Thực Phẩm & Đồ Uống',
                'description' => 'Thực phẩm, đồ uống, snack',
                'slug' => 'thuc-pham-do-uong'
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
