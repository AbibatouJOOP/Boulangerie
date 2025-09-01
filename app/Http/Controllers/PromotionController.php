<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromotionRequest;
use App\services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index()
    {
        try {
            $promotions = $this->promotionService->index();
            return response()->json($promotions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des promotions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'reduction' => 'required|numeric|min:1|max:100',
                'dateDebut' => 'required|date',
                'dateFin' => 'required|date|after:dateDebut',
                'actif' => 'boolean',
                'produits' => 'sometimes|array',
                'produits.*' => 'exists:produits,id'
            ]);

            $promotion = $this->promotionService->store($validatedData);

            return response()->json([
                'message' => 'Promotion créée avec succès',
                'promotion' => $promotion
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données de validation invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $promotion = $this->promotionService->show($id);
            return response()->json($promotion, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Promotion non trouvée',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:1000',
                'reduction' => 'sometimes|numeric|min:1|max:100',
                'dateDebut' => 'sometimes|date',
                'dateFin' => 'sometimes|date|after:dateDebut',
                'actif' => 'sometimes|boolean',
                'produits' => 'sometimes|array',
                'produits.*' => 'exists:produits,id'
            ]);

            $promotion = $this->promotionService->update($validatedData, $id);

            return response()->json([
                'message' => 'Promotion mise à jour avec succès',
                'promotion' => $promotion
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données de validation invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Vérifier si la promotion peut être supprimée
            if (!$this->promotionService->peutEtreSupprimer($id)) {
                return response()->json([
                    'error' => 'Cette promotion ne peut pas être supprimée car elle est utilisée dans des commandes en cours'
                ], 409);
            }

            $this->promotionService->destroy($id);

            return response()->json([
                'message' => 'Promotion supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les promotions actives
     */
    public function actives()
    {
        try {
            $promotions = $this->promotionService->getPromotionsActives();
            return response()->json($promotions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des promotions actives',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les produits en promotion
     */
    public function produitsEnPromotion()
    {
        try {
            $produits = $this->promotionService->getProduitsEnPromotion();
            return response()->json($produits, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des produits en promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le prix avec promotion pour un produit
     */
    public function calculerPrix($produitId)
    {
        try {
            $resultat = $this->promotionService->calculerPrixAvecPromotion($produitId);
            return response()->json($resultat, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du calcul du prix',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une promotion
     */
    public function toggle(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'actif' => 'required|boolean'
            ]);

            $promotion = $this->promotionService->togglePromotion($id, $validatedData['actif']);

            return response()->json([
                'message' => $promotion->actif ? 'Promotion activée' : 'Promotion désactivée',
                'promotion' => $promotion
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la modification du statut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dupliquer une promotion
     */
    public function dupliquer(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:1000',
                'reduction' => 'sometimes|numeric|min:1|max:100',
                'dateDebut' => 'sometimes|date',
                'dateFin' => 'sometimes|date|after:dateDebut',
                'actif' => 'sometimes|boolean'
            ]);

            $nouvellePromotion = $this->promotionService->dupliquer($id, $validatedData);

            return response()->json([
                'message' => 'Promotion dupliquée avec succès',
                'promotion' => $nouvellePromotion
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la duplication',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Associer un produit à une promotion
     */
    public function associerProduit(Request $request, $id)
    {
        try {
            // Log des données reçues
            Log::info('Données reçues pour association:', [
                'promotion_id' => $id,
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Vérifier que la promotion existe
            $promotion = \App\Models\Promotion::find($id);
            if (!$promotion) {
                Log::error("Promotion non trouvée avec ID: $id");
                return response()->json([
                    'error' => 'Promotion non trouvée'
                ], 404);
            }

            // Validation des données
            $validatedData = $request->validate([
                'produit_id' => 'required|exists:produits,id',
                'montant_reduction' => 'nullable|numeric|min:0'
            ]);

            Log::info('Données validées:', $validatedData);

            // Vérifier que le produit existe
            $produit = \App\Models\Produits::find($validatedData['produit_id']);
            if (!$produit) {
                Log::error("Produit non trouvé avec ID: " . $validatedData['produit_id']);
                return response()->json([
                    'error' => 'Produit non trouvé'
                ], 404);
            }

            // Tentative d'association
            $association = $this->promotionService->associerProduitPromotion(
                $id,
                $validatedData['produit_id'],
                $validatedData['montant_reduction'] ?? null
            );

            Log::info('Association créée avec succès:', ['association' => $association]);

            return response()->json([
                'message' => 'Produit associé à la promotion avec succès',
                'association' => $association
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur de validation:', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Données de validation invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'association:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors de l\'association',
                'message' => $e->getMessage(),
                'details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }


    /**
     * Dissocier un produit d'une promotion
     */
    public function dissocierProduit($id, $produitId)
    {
        try {
            $this->promotionService->dissocierProduitPromotion($id, $produitId);

            return response()->json([
                'message' => 'Produit dissocié de la promotion avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la dissociation',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
