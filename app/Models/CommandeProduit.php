<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeProduit extends Model
{
    use HasFactory;

    protected $table = 'commande_produits'; // Assurer le bon nom de table

    protected $fillable = [
        'commande_id',
        'produit_id',
        'quantite',
        'prixU',           // Prix unitaire au moment de la commande
        'montant_total',   // Montant total pour cette ligne
        'promo_id'
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prixU' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function commande()
    {
        return $this->belongsTo(Commandes::class, 'commande_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produits::class, 'produit_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promo_id');
    }

    // Accesseurs pour compatibilité avec le frontend
    public function getPrixUnitaireAttribute()
    {
        return $this->prixU;
    }

    // Calculer le prix avec promotion
    public function getPrixAvecPromoAttribute()
    {
        if ($this->promotion && $this->promotion->reduction) {
            return $this->prixU * (1 - $this->promotion->reduction / 100);
        }
        return $this->prixU;
    }

    // Calculer le montant total avec promotion
    public function getMontantAvecPromoAttribute()
    {
        return $this->getPrixAvecPromoAttribute() * $this->quantite;
    }

    // Calculer l'économie réalisée
    public function getEconomieAttribute()
    {
        if ($this->promotion && $this->promotion->reduction) {
            $prixOriginal = $this->prixU * $this->quantite;
            return $prixOriginal - $this->getMontantAvecPromoAttribute();
        }
        return 0;
    }
}
