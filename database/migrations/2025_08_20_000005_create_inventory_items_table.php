<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->decimal('theoretical_qty_at_snapshot', 14, 3)->default(0);
            $t->decimal('real_qty', 14, 3)->nullable();
            $t->string('notes')->nullable();
            $t->timestamps();
            $t->unique(['inventory_id', 'product_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
