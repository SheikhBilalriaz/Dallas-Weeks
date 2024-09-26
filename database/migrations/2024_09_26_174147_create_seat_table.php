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
        Schema::create('seat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
            $table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')->on('team')->onDelete('cascade');
            $table->unsignedBigInteger('company_info_id');
            $table->foreign('company_info_id')->references('id')->on('company_info')->onDelete('cascade');
            $table->unsignedBigInteger('seat_info_id');
            $table->foreign('seat_info_id')->references('id')->on('seat_info')->onDelete('cascade');
            $table->string('subscription_id');
            $table->string('customer_id');
            $table->boolean('is_active')->default(0);
            $table->boolean('is_connected')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat');
    }
};
