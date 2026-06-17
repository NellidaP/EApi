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
        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('longitud')->nullable();
            $table->string('latitud')->nullable();
            $table->integer('estado')->default(0);
            $table->integer('entrada')->default(0);
            $table->integer('aprobador_id')->default(0);
            $table->text('comentario')->nullable();
            $table->text('url_in')->nullable();
            $table->text('url_out')->nullable();
            $table->unsignedBigInteger('unity_in_id')->nullable();
            $table->unsignedBigInteger('unity_out_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jornadas');
    }
};
