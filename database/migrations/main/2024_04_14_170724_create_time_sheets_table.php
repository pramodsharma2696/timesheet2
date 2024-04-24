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
        Schema::create('time_sheets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('project_id')->unsigned()->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status',[0, 1])->default(0);
            $table->enum('localwork',[0, 1])->default(0);
            $table->enum('scanning',[0, 1])->default(0);
            $table->enum('hours',[0, 1])->default(0);
            $table->enum('break',[0, 1])->default(0);
            $table->integer('break_duration')->nullable();;
            $table->enum('break_duration_type',['hours','minutes'])->default('hours');
            $table->json("assign_admin");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_sheets');
    }
};
