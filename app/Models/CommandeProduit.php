<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeProduit extends Model
{
    use HasFactory;
    protected $fillable = ['commande_id','produit_id','quantite','prixU','montant_total','promo_id'];
    //un element commandé appartient à une commande
    public function commande() {
        return $this->belongsTo(Commandes::class,'commande_id');
    }
    // un element appartient à un produit
    public function produit()
    {
        return $this->belongsTo(Produits::class);
    }
}
