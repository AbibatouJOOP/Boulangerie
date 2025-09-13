<?php

namespace App\Http\Controllers;
use App\services\ChatServices;
use Illuminate\Http\Request;
use App\Http\Requests\ChatRequest;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatServices $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $messages = [];

            if ($user->role === 'CLIENT') {
                $messages = $this->chatService->getClientConversations($user->id);
            } elseif ($user->role === 'EMPLOYE') {
                $messages = $this->chatService->getEmployeConversations($user->id);
            } else { // ADMIN
                $messages = $this->chatService->getAllConversations();
            }

            return response()->json($messages, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des messages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Récupérer toutes les conversations uniques (pour la liste des conversations)
    public function getConversationsList(): \Illuminate\Http\JsonResponse
    {
        try {
            $conversations = $this->chatService->getUniqueConversations();
            return response()->json($conversations, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des conversations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getConversation($clientId, $employeId = null): \Illuminate\Http\JsonResponse
    {
        try {
            $messages = $this->chatService->getConversationMessages($clientId, $employeId);
            return response()->json($messages, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de la conversation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Nouvelle méthode pour récupérer les messages d'un client spécifique
    public function getClientMessages($clientId): \Illuminate\Http\JsonResponse
    {
        try {
            $messages = $this->chatService->getClientConversations($clientId);
            return response()->json($messages, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des messages du client',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            $validatedData = $request->validate([
                'client_id' => 'required|integer|exists:users,id',
                'employe_id' => 'nullable|integer|exists:users,id',
                'message' => 'required|string|max:1000',
            ]);

            // Ajouter les informations de l'expéditeur
            $validatedData['emeteur_type'] = $user->role;
            $validatedData['emeteur_id'] = $user->id;
            $validatedData['est_lu'] = false;

            // Si c'est un employé/admin qui répond, s'assurer que employe_id est défini
            if (in_array($user->role, ['EMPLOYE', 'ADMIN']) && !$validatedData['employe_id']) {
                $validatedData['employe_id'] = $user->id;
            }

            $message = $this->chatService->store($validatedData);

            // Charger les relations pour la réponse
            $message->load(['client:id,nomComplet,email', 'employe:id,nomComplet,email', 'sender:id,nomComplet,email']);

            return response()->json($message, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'envoi du message',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Méthode spécifique pour répondre à un client
    public function replyToClient(Request $request, $clientId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            if (!in_array($user->role, ['EMPLOYE', 'ADMIN'])) {
                return response()->json([
                    'error' => 'Accès non autorisé'
                ], 403);
            }

            $validatedData = $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $message = $this->chatService->replyToClient(
                $clientId,
                $user->id,
                $validatedData['message']
            );

            $message->load(['client:id,nomComplet,email', 'employe:id,nomComplet,email', 'sender:id,nomComplet,email']);

            return response()->json($message, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la réponse au client',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            $id = (int) $id; // convertir en entier
            $message = $this->chatService->show($id);
            return response()->json($message, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Message non trouvé',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'est_lu' => 'boolean'
            ]);

            $message = $this->chatService->update($validatedData, $id);
            return response()->json($message, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        try {
            $deleted = $this->chatService->destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Message supprimé avec succès'], 200);
            }
            return response()->json(['error' => 'Message non trouvé'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Marquer une conversation comme lue
    public function markConversationAsRead(Request $request, $clientId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $employeId = $request->input('employe_id');

            $count = $this->chatService->markConversationAsRead($clientId, $employeId, $user->id);

            return response()->json([
                'message' => 'Conversation marquée comme lue',
                'updated_count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du marquage',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Récupérer le nombre de messages non lus
    public function getUnreadCount(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $count = $this->chatService->getUnreadCount($user->id);

            return response()->json(['unread_count' => $count], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du compteur',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Assigner un employé à une conversation
    public function assignEmployee(Request $request, $clientId): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'employe_id' => 'required|integer|exists:users,id',
            ]);

            $assigned = $this->chatService->assignEmployeeToConversation(
                $clientId,
                $validatedData['employe_id']
            );

            if ($assigned) {
                return response()->json(['message' => 'Employé assigné avec succès'], 200);
            } else {
                return response()->json(['message' => 'Aucune conversation à assigner'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'assignation',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
