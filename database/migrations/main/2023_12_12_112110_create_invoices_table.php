<?php

use App\Models\Invoice;
use App\Models\ProjectList;
use App\Models\Team;
use App\Models\User;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(ProjectList::class);
            $table->json("meta");
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('invoice_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Invoice::class);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Team::class); //where team
            $table->json("permission"); //where permission jsonIN

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_users');
    }
};
