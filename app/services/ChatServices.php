<?php

namespace App\services;

use App\Http\Requests\ChatRequest;
use App\Models\Chats;
use App\Models\User;
use Illuminate\Http\Request;

class ChatServices
{
    public function index(): array|\Illuminate\Database\Eloquent\Collection
    {
        return Chats::with(['client:id,nomComplet,email', 'employe:id,nomComplet,email'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Récupérer toutes les conversations d'un client avec tous les employés/admins
    public function getClientConversations(int $clientId): array|\Illuminate\Database\Eloquent\Collection
    {
        return Chats::with(['client:id,nomComplet,email', 'employe:id,nomComplet,email'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Récupérer les messages d'une conversation spécifique
    public function getConversationMessages(int $clientId, int $employeId = null): array|\Illuminate\Database\Eloquent\Collection
    {
        $query = Chats::with(['client:id,nomComplet,email', 'employe:id,nomComplet,email'])
            ->where('client_id', $clientId);

        if ($employeId) {
            $query->where('employe_id', $employeId);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    // Récupérer toutes les conversations pour un employé/admin
    public function getEmployeConversations(int $employeId): array|\Illuminate\Database\Eloquent\Collection
    {
        return Chats::with(['client:id,nomComplet,email', 'employe:id,nomComplet,email'])
            ->where('employe_id', $employeId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Récupérer toutes les conversations (pour admin)
    public function getAllConversations(): array|\Illuminate\Database\Eloquent\Collection
    {
        return Chats::with(['client:id,nomComplet,email', 'employe:id,nomComplet,email'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Récupérer les conversations uniques (groupées par client)
    public function getUniqueConversations(): array
    {
        $conversations = Chats::with(['client:id,nomComplet,email'])
            ->selectRaw('client_id, MAX(created_at) as last_message_date, COUNT(*) as message_count')
            ->groupBy('client_id')
            ->orderBy('last_message_date', 'desc')
            ->get();

        return $conversations->toArray();
    }

    public function store(array $data): Chats
    {
        $data['est_lu'] = $data['est_lu'] ?? false;

        // Si c'est un message d'un client, on peut laisser employe_id vide au début
        // Il sera assigné quand un employé/admin répond
        if (!isset($data['employe_id']) && $this->isClient($data['client_id'])) {
            $data['employe_id'] = null;
        }

        return Chats::create($data);
    }

    public function show(int $id): \Illuminate\Database\Eloquent\Builder|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        return Chats::with(['client:id,nomComplet,email', 'employe:id,nomComplet,email'])
            ->findOrFail($id);
    }

    public function update(array $data, int $id): Chats
    {
        $chat = Chats::findOrFail($id);
        $chat->update($data);
        return $chat->load(['client:id,nomComplet,email', 'employe:id,nomComplet,email']);
    }

    public function destroy(int $id): bool
    {
        return Chats::destroy($id) > 0;
    }

    public function markConversationAsRead(int $clientId, int $employeId, int $currentUserId): int
    {
        $currentUserType = $this->getUserType($currentUserId);

        return Chats::where('client_id', $clientId)
            ->where(function($query) use ($employeId) {
                if ($employeId) {
                    $query->where('employe_id', $employeId);
                }
            })
            ->where('est_lu', false)
            ->where('emeteur_type', '!=', $currentUserType)
            ->update(['est_lu' => true]);
    }

    // Méthode pour répondre à un message de client
    public function replyToClient(int $clientId, int $employeId, string $message): Chats
    {
        return $this->store([
            'client_id' => $clientId,
            'employe_id' => $employeId,
            'message' => $message,
            'emeteur_type' => 'EMPLOYE', // ou 'ADMIN'
            'emeteur_id' => $employeId,
            'est_lu' => false
        ]);
    }

    // Vérifier si un utilisateur est client
    private function isClient(int $userId): bool
    {
        $user = User::find($userId);
        return $user && $user->role === 'CLIENT';
    }

    // Obtenir le type d'utilisateur
    private function getUserType(int $userId): string
    {
        $user = User::find($userId);
        return $user ? $user->role : 'UNKNOWN';
    }

    public function getUnreadCount(int $userId): int
    {
        $userType = $this->getUserType($userId);

        $query = Chats::where('est_lu', false);

        if ($userType === 'CLIENT') {
            $query->where('client_id', $userId)
                ->where('emeteur_type', '!=', 'CLIENT');
        } else {
            // Pour employés et admins, compter tous les messages non lus des clients
            $query->where('emeteur_type', 'CLIENT');
        }

        return $query->count();
    }

    // Assigner un employé à une conversation
    public function assignEmployeeToConversation(int $clientId, int $employeId): bool
    {
        return Chats::where('client_id', $clientId)
                ->whereNull('employe_id')
                ->update(['employe_id' => $employeId]) > 0;
    }
}
