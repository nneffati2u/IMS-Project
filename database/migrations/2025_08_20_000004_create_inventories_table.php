<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $t) {
            $t->id();
            $t->date('inventory_date');
            $t->enum('status', ['Draft', 'Closed'])->default('Draft');
            $t->string('attestation_path')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
