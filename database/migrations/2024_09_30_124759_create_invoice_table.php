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
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')->on('team')->onDelete('cascade');
            $table->unsignedBigInteger('seat_id');
            $table->foreign('seat_id')->references('id')->on('seat')->onDelete('cascade');
            $table->string('invoice_id');
            $table->text('invoice_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
