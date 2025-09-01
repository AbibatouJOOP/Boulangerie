<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produits extends Model
{
    use HasFactory;
    protected $fillable = ['nom','description','stock','prix','image','categorie_id'];
    //un produit appartient à une catégorie
    public function categorie() {
        return $this->belongsTo(Categories::class);
    }

    //un produit peut avoir plusieurs promotion
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_produit')
            ->withPivot('montant_reduction')
            ->withTimestamps();
    }


    //un produit peut etre dans plusieurs commande
    public function commandeProduit() {
        return $this->hasMany(CommandeProduit::class);
    }
}
