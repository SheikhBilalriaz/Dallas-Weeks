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
        Schema::create('global_limit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seat_id');
            $table->foreign('seat_id')->references('id')->on('seat')->onDelete('cascade');
            $table->string('health_slug');
            $table->integer('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_limit');
    }
};
