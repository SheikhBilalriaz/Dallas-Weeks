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
        Schema::create('campaign', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('seat_id');
            $table->foreign('seat_id')->references('id')->on('seat')->onDelete('cascade');
            $table->boolean('is_active')->default(1);
            $table->string('type');
            $table->longText('img_path');
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
            $table->text('url');
            $table->string('connection')->default('o');
            $table->boolean('is_archive')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign');
    }
};