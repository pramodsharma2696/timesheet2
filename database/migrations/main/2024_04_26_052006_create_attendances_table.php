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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('worker_id');
            $table->unsignedBigInteger('timesheet_id');
            $table->json('attendance')->nullable();
            $table->json('assigned_task_hours')->nullable();
            $table->date('date');
            $table->enum('approve',[0, 1])->default(0)->comment('0:disapprove, 1:approve');
            $table->integer('total_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
