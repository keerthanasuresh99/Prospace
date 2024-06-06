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
        Schema::create('achievement_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('is_tracker_list')->default(false)->comment('Added column to identify if it is in the achievement tracker list. 1 : include in achievement tracker'); // Added column to identify if it is in the achievement tracker list
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_lists');
    }
};
