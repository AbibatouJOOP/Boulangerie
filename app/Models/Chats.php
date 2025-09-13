<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'employe_id',
        'message',
        'est_lu',
        'emeteur_type', // 'CLIENT', 'EMPLOYE', 'ADMIN'
        'emeteur_id'    // ID de celui qui envoie le message
    ];

    protected $casts = [
        'est_lu' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['formatted_date', 'sender_name'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function employe()
    {
        return $this->belongsTo(User::class, 'employe_id');
    }

    // Relation avec l'expéditeur réel du message
    public function sender()
    {
        return $this->belongsTo(User::class, 'emeteur_id');
    }

    // Accesseur pour formater la date
    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : null;
    }

    // Accesseur pour obtenir le nom de l'expéditeur
    public function getSenderNameAttribute()
    {
        $sender = User::find($this->emeteur_id);
        return $sender ? $sender->nomComplet : 'Utilisateur inconnu';
    }

    // Scope pour filtrer par conversation (client spécifique)
    public function scopeConversation($query, $clientId, $employeId = null)
    {
        $query = $query->where('client_id', $clientId);

        if ($employeId) {
            $query->where('employe_id', $employeId);
        }

        return $query;
    }

    // Scope pour les messages non lus
    public function scopeUnread($query)
    {
        return $query->where('est_lu', false);
    }

    // Scope pour ordonner par date
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    // Scope pour les messages d'un type d'expéditeur spécifique
    public function scopeBySenderType($query, $senderType)
    {
        return $query->where('emeteur_type', $senderType);
    }

    // Scope pour les conversations d'un client
    public function scopeClientConversations($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Scope pour les conversations où un employé est impliqué
    public function scopeEmployeConversations($query, $employeId)
    {
        return $query->where('employe_id', $employeId);
    }
}
