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
        Schema::table('time_sheets', function (Blueprint $table) {
            // Change the project_id column to CHAR(16)
            $table->char('project_id', 16)->nullable()->change();
            // Make the break_duration column nullable
            $table->integer('break_duration')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_sheets', function (Blueprint $table) {
             // Revert the project_id column to bigInteger
             $table->bigInteger('project_id')->unsigned()->nullable()->change();
             // Revert the break_duration column to not nullable
             $table->integer('break_duration')->nullable(false)->change();
        });
    }
};
