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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achiever_id');
            $table->unsignedBigInteger('sub_achiever_id');
            $table->string('image')->nullable();
            $table->foreign('achiever_id')->references('id')->on('base_settings');
            $table->foreign('sub_achiever_id')->references('id')->on('sub_achiever_lists');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
