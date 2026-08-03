<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('unity_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->json('items')->nullable(); // Store items as JSON
            $table->json('operations')->nullable(); // Store users as JSON
            $table->json('orders')->nullable(); // Store users as JSON
            $table->boolean('has_order_pending')->default(false); // Flag to indicate if an order is pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
