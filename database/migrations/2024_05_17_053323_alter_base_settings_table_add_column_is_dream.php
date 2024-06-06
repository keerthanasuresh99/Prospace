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
        Schema::table('base_settings', function (Blueprint $table) {
            $table->integer('is_dream')->after('status')->default(0)->comment('1: Included in dream list  , 0:Not Included');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('base_settings', function (Blueprint $table) {
            $table->dropColumn('is_dream');
        });
    }
};
