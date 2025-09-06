<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamp('triggered_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamp('last_email_sent_at')->nullable();
            $t->enum('current_state', ['Normal', 'Below'])->default('Normal');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
