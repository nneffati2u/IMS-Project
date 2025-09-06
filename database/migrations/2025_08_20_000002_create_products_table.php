<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('price_ht', 12, 2)->default(0);
            $t->decimal('tva_rate', 5, 2)->default(0);
            $t->decimal('qty_theoretical', 14, 3)->default(0);
            $t->decimal('low_stock_threshold_value', 14, 3)->nullable();
            $t->decimal('low_stock_threshold_percent', 5, 2)->nullable();
            $t->boolean('is_threshold_percent')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
