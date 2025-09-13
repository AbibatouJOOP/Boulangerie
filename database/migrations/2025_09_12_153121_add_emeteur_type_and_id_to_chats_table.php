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
        Schema::table('chats', function (Blueprint $table) {
            $table->string('emeteur_type')->after('message')->default('CLIENT'); // CLIENT | EMPLOYE | ADMIN
            $table->unsignedBigInteger('emeteur_id')->after('emeteur_type')->nullable();

            // Si tu veux supprimer l'ancien champ
            $table->dropColumn('emeteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            //
        });
    }
};
