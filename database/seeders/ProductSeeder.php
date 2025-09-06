<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $g = Category::where('name', 'Epicerie')->first();
        $d = Category::where('name', 'Boissons')->first();
        Product::firstOrCreate(['name' => 'Café moulu'], ['category_id' => $g?->id, 'price_ht' => 6.5, 'tva_rate' => 5.5, 'qty_theoretical' => 120, 'low_stock_threshold_percent' => 20, 'is_threshold_percent' => true]);
        Product::firstOrCreate(['name' => 'Bouteille Eau 1L'], ['category_id' => $d?->id, 'price_ht' => 0.4, 'tva_rate' => 5.5, 'qty_theoretical' => 300, 'low_stock_threshold_value' => 50, 'is_threshold_percent' => false]);
    }
}
