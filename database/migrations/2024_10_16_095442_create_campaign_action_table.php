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
        Schema::create('campaign_action', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('current_element_id')->nullable();
            $table->foreign('current_element_id')->references('id')->on('campaign_element')->onDelete('cascade');
            $table->unsignedBigInteger('next_true_element_id')->nullable();
            $table->foreign('next_true_element_id')->references('id')->on('campaign_element')->onDelete('cascade');
            $table->unsignedBigInteger('next_false_element_id')->nullable();
            $table->foreign('next_false_element_id')->references('id')->on('campaign_element')->onDelete('cascade');
            $table->enum('status', ['inprogress', 'completed'])->default('inprogress');
            $table->unsignedBigInteger('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaign')->onDelete('cascade');
            $table->dateTime('ending_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_action');
    }
};
