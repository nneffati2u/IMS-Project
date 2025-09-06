<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['IN', 'OUT']);
            $t->decimal('quantity', 14, 3);
            $t->decimal('unit_price_ht', 12, 2)->nullable();
            $t->timestamp('occurred_at')->useCurrent();
            $t->string('note')->nullable();
            $t->timestamps();
            $t->index(['product_id', 'occurred_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
