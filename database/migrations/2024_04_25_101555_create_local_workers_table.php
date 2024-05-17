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
        Schema::create('local_workers', function (Blueprint $table) {
            $table->id('id');
            $table->string('worker_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('status',['active','inactive'])->default('active');
            $table->integer('planned_hours')->nullable();
            $table->json('work_assignment')->nullable();
            $table->unsignedBigInteger('timesheet_id')->unsigned()->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_workers');
    }
};
