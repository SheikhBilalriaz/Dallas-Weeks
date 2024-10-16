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
        Schema::create('lead', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaign')->onDelete('cascade');
            $table->boolean('is_active')->default(1);
            $table->string('send_connections')->default('discovered');
            $table->string('profileUrl')->nullable();
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->string('title_company')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('provider_id')->nullable();
            $table->time('executed_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead');
    }
};
