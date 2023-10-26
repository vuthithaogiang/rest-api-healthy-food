<?php

namespace Database\Seeders;

use App\Models\CategoryProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryProduct::create([
            'name'=> 'Vegetable',
            'slug' => 'vegetable',
            'description' => 'Vegetables are a cornerstone of a balanced diet. They are rich in nutrients, fiber, and antioxidants. From leafy greens like spinach to starchy potatoes, vegetables provide a diverse array of tastes and textures.'
        ]);

        CategoryProduct::create([
            'name' => 'Breads and Bakery',
            'slug' => 'breads-and-bakery',
            'description' => 'This category includes a wide selection of bread and baked goods. From crusty artisanal bread to sweet pastries and bagels, you can find something to satisfy your carb cravings.'
        ]);
    }
}
