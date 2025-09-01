<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiements extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'statut',
        'mode_paiement',
        'montant_payé',
        'date_paiement',
        'numero_telephone',
        'operateur',
        'reference_transaction'
    ];

    protected $casts = [
        'montant_payé' => 'decimal:2',
        'date_paiement' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Un paiement appartient à une commande
    public function commande()
    {
        return $this->belongsTo(Commandes::class, 'commande_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    public function scopeByMode($query, $mode)
    {
        return $query->where('mode_paiement', $mode);
    }

    public function scopeByOperateur($query, $operateur)
    {
        return $query->where('operateur', $operateur);
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_paiement', today());
    }

    // Vérifier si le paiement est en ligne
    public function getEstEnLigneAttribute()
    {
        return $this->mode_paiement === 'en_ligne';
    }
}
