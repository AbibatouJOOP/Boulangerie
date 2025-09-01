<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour PostgreSQL, utiliser une requête raw avec USING
        DB::statement('ALTER TABLE promotion_produits ALTER COLUMN montant_reduction TYPE decimal(8,2) USING montant_reduction::decimal(8,2)');
    }

    public function down(): void
    {
        // Revenir à string
        DB::statement('ALTER TABLE promotion_produits ALTER COLUMN montant_reduction TYPE varchar USING montant_reduction::varchar');
    }
};
