<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'reduction',
        'dateDebut',
        'dateFin',
        'actif'
    ];

    protected $casts = [
        'reduction' => 'decimal:2',
        'dateDebut' => 'datetime',
        'dateFin' => 'datetime',
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Une promotion peut s'appliquer à plusieurs produits
    public function produits()
    {
        return $this->belongsToMany(Produits::class, 'promotion_produits', 'promo_id', 'produit_id')
            ->withPivot('montant_reduction')
            ->withTimestamps();
    }

    // Une promotion est utilisée dans plusieurs commandes
    public function commandeProduits()
    {
        return $this->hasMany(CommandeProduit::class, 'promo_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('actif', true)
            ->where('dateDebut', '<=', now())
            ->where('dateFin', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('dateFin', '<', now());
    }

    public function scopeFuture($query)
    {
        return $query->where('dateDebut', '>', now());
    }
}
