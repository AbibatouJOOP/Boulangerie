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
        Schema::rename('commande_produit', 'commande_produits');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commande_produits', function (Blueprint $table) {
            //
        });
    }
};
