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
        Schema::table('global_blacklist', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
        Schema::table('email_blacklist', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
        Schema::table('role', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
        Schema::table('seat', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
        Schema::table('campaign', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
        Schema::table('webhook', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_blacklist', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
        });
        Schema::table('email_blacklist', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
        });
        Schema::table('role', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
        });
        Schema::table('seat', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
        });
        Schema::table('campaign', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
        });
        Schema::table('webhook', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade');
        });
    }
};
