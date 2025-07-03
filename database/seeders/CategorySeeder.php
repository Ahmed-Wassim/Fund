<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Software, hardware, and tech startups'],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Financial services and startups'],
            ['name' => 'Healthcare', 'slug' => 'healthcare', 'description' => 'Medical devices, healthcare services'],
            ['name' => 'E-commerce', 'slug' => 'ecommerce', 'description' => 'Online retail and marketplace platforms'],
            ['name' => 'Fintech', 'slug' => 'fintech', 'description' => 'Financial technology and services'],
            ['name' => 'Real Estate', 'slug' => 'real-estate', 'description' => 'Property development and management'],
            ['name' => 'Food & Beverage', 'slug' => 'food-beverage', 'description' => 'Restaurants, food products, beverages'],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'EdTech and educational services'],
            ['name' => 'Manufacturing', 'slug' => 'manufacturing', 'description' => 'Production and manufacturing businesses'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
