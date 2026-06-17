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
        Schema::create('unities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('direction')->nullable();
            $table->string('description')->nullable();
            $table->string('longitud')->nullable();
            $table->string('latitud')->nullable();
            $table->integer('type')->default(0);
            $table->foreignId('unity_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tickets')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unities');
    }
};
