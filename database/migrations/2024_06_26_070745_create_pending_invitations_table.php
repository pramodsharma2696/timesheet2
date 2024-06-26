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
        Schema::create('pending_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('worker_id');
            $table->unsignedBigInteger('timesheet_id')->unsigned()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('status',[0, 1, 2])->default(0)->comment('0:pending,1:accept,2:reject');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_invitations');
    }
};
