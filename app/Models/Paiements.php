<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiements extends Model
{
    use HasFactory;
    protected $fillable = ['commande_id','statut','mode_paiement','montant_paye','date_paiement'];
    //un paiement appartient à une commande
    public function commande() {
        return $this->belongsTo(Commandes::class);
    }
     
}
