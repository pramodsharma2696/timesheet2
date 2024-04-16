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
        Schema::create('project_lists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('team_id')->unsigned();
            $table->tinyInteger('legacy')->default(0);
            $table->mediumText('desr')->comment('Project description');
            $table->string('takf_version')->nullable()->comment('Taking of project');
            $table->enum('takf_status', ['0', '1', '2'])->default('0')->comment('Taking off project status');
            $table->string('bill_version')->nullable()->comment('bill of project');
            $table->enum('bill_status', ['0', '1', '2'])->default('0')->comment('bill off project status');
            $table->string('cost_version')->nullable()->comment('cost of project');
            $table->enum('cost_status', ['0', '1', '2'])->default('0')->comment('cost off project status');
            $table->bigInteger('cost_updater_id')->unsigned()->nullable()->comment('cost off project updater');
            $table->bigInteger('project_list_id')->unsigned()->nullable()->comment('This is the parent project where it was copied');
            $table->json('chat_channel_id')->nullable();
            $table->string('channel_chat_id')->nullable();
            $table->json('recent')->nullable();
            $table->text('location')->nullable();
            $table->string('project_list_id_copy')->nullable();
            $table->string('duration')->nullable();
            $table->string('duration_unit')->nullable();
            $table->string('status')->nullable();
            $table->json('meta')->nullable();
            $table->string('type', 125);
            $table->json('project_properties');
            $table->json('allocation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_lists');
    }
};
