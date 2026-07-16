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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->tinyInteger('tipo');
            $table->tinyInteger('estado');
            $table->tinyInteger('tipo_pago');
            $table->tinyInteger('n_fichas');
            $table->tinyInteger('n_personas');
            $table->decimal('costo_ficha', 12, 2);
            $table->tinyInteger('tipo_ambiente');
            $table->decimal('costo_ambiente', 12, 2);
            $table->decimal('costo_asignado', 12, 2);
            $table->decimal('costo_hora', 12, 2);
            $table->dateTime('fecha_inicio');
            $table->decimal('tiempo_horas', 5, 2);
            $table->decimal('costo_total', 12, 2);
            $table->foreignId('unity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('users');
            $table->json('items');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
