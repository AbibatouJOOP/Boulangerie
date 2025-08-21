<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commandes extends Model
{
    use HasFactory;
    protected $fillable = ['client_id','montant_total','statut'];
    //une commande appartient à un utilisateur
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }
    //une commande contient plusieurs produit via commandelivre
    public function produitCommander() {
        return $this->hasMany(CommandeProduit::class);
    }
    public function paiement()
    {
        return $this->hasOne(Paiements::class);
    }
}
