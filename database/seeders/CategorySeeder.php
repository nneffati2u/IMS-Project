<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Epicerie', 'Boissons', 'Hygiène', 'Divers'] as $n) {
            Category::firstOrCreate(['name' => $n]);
        }
    }
}
