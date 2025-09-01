<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommandeRequest;
use App\services\CommandeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommandeController extends Controller
{
    protected $commandeService;

    public function __construct(CommandeService $commandeService)
    {
        $this->commandeService = $commandeService;
    }

    /**
     * Récupérer toutes les commandes avec gestion des erreurs
     */
    public function index()
    {
        try {
            $commandes = $this->commandeService->index();

            // Log pour debug
            Log::info('Commandes récupérées:', [
                'count' => $commandes->count(),
                'first_commande' => $commandes->first() ? [
                    'id' => $commandes->first()->id,
                    'produits_count' => $commandes->first()->produitCommander->count(),
                    'montant_total' => $commandes->first()->montant_total
                ] : null
            ]);

            return response()->json($commandes, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des commandes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la récupération des commandes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les commandes d'un client
     */
    public function getByClient()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $commandes = $this->commandeService->getByClient($user->id);

            // Log pour debug
            Log::info('Commandes client récupérées:', [
                'client_id' => $user->id,
                'count' => $commandes->count()
            ]);

            return response()->json($commandes, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des commandes client:', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la récupération des commandes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle commande
     */
    public function store(CommandeRequest $request)
    {
        try {
            $data = $request->validated();
            $data['client_id'] = auth()->id();

            $commande = $this->commandeService->store($data);

            return response()->json([
                'message' => 'Commande créée avec succès',
                'commande' => $commande
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de commande:', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la création de la commande',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une commande spécifique
     */
    public function show($id)
    {
        try {
            $commande = $this->commandeService->show($id);
            return response()->json($commande, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Commande non trouvée',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateStatut(Request $request, $id)
    {
        try {
            $request->validate([
                'statut' => 'required|string|in:en_préparation,prete,en_livraison,livrée,annulée'
            ]);

            $commande = $this->commandeService->update([
                'statut' => $request->statut
            ], $id);

            return response()->json([
                'message' => 'Statut mis à jour avec succès',
                'commande' => $commande
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du statut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une commande
     */
    public function destroy($id)
    {
        try {
            $this->commandeService->destroy($id);
            return response()->json(['message' => 'Commande supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Vérifier la disponibilité des produits
     */
    public function verifierDisponibilite(Request $request)
    {
        try {
            $request->validate([
                'produits' => 'required|array',
                'produits.*.produit_id' => 'required|integer',
                'produits.*.quantite' => 'required|integer|min:1'
            ]);

            $disponibilite = $this->commandeService->verifierDisponibilite($request->produits);
            return response()->json($disponibilite, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
