<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraisons extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'employe_id',
        'statut',
        'date_livraison',
        'adresse_livraison',
        'frais_livraison',
        'note'
    ];

    protected $casts = [
        'date_livraison' => 'datetime',
        'frais_livraison' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Une livraison appartient à une commande
    public function commande()
    {
        return $this->belongsTo(Commandes::class, 'commande_id');
    }

    // Une livraison est assignée à un employé
    public function employe()
    {
        return $this->belongsTo(User::class, 'employe_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    public function scopeByEmploye($query, $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    public function scopeEnRetard($query)
    {
         return $query->where('date_livraison', '<', now())
                 ->where('statut', 'non_livree');
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_livraison', today());
    }
}
