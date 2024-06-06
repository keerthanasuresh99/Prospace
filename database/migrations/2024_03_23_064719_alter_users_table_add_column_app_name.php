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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->after('place')->nullable();
            $table->unsignedBigInteger('introducer_id')->after('token')->nullable();
            $table->string('introducer_phone')->after('introducer_id')->nullable();
            $table->integer('event_builder')->after('introducer_phone')->nullable()->default(0)->comment('1: Event Builder , 0:Not An Event Builder');
            $table->string('app_name')->after('event_builder')->nullable()->comment('App through which user is registered');
            $table->integer('is_registered')->after('app_name')->nullable()->default(0)->comment('1: Registered , 0:Not Registered.Applicable only for 2nd app');

            $table->foreign('introducer_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('introducer_id');
            $table->dropColumn('introducer_phone');
            $table->dropColumn('event_builder');
            $table->dropColumn('app_name');
            $table->dropColumn('is_registered');
        });
    }
};
