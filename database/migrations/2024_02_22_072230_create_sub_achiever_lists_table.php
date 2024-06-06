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
        Schema::create('sub_achiever_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achiever_id');
            $table->string('value');
            $table->foreign('achiever_id')->references('id')->on('base_settings');
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_achiever_lists');
    }
};
