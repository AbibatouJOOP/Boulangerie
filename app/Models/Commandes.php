<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commandes extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'montant_total',
        'statut',
        'infos_livraison',
        'infos_paiement'
    ];

    protected $casts = [
        'infos_livraison' => 'json',
        'infos_paiement' => 'json',
        'montant_total' => 'decimal:2'
    ];

    // Une commande appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Alias pour compatibilité
    public function client()
    {
        return $this->user();
    }

    // Une commande contient plusieurs produits via CommandeProduit
    public function produitCommander()
    {
        return $this->hasMany(CommandeProduit::class, 'commande_id');
    }

    // Une commande a un paiement
    public function paiement()
    {
        return $this->hasOne(Paiements::class, 'commande_id');
    }

    // Une commande peut avoir une livraison
    public function livraison()
    {
        return $this->hasOne(Livraisons::class, 'commande_id');
    }

    // Accesseur pour le nom complet du client
    public function getNomClientAttribute()
    {
        if ($this->user) {
            return $this->user->nomComplet;
        }
        return 'Client inconnu';
    }

    // Méthode pour calculer le total
    public function calculerTotal()
    {
        return $this->produitCommander->sum(function ($item) {
            return $item->quantite * $item->prixU;
        });
    }
}
