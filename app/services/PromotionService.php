<?php

namespace App\services;

use App\Models\Produits;
use App\Models\Promotion;
use App\Models\PromotionProduit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionService
{
    public function index()
    {
        return Promotion::with(['produits'])->orderBy('created_at', 'desc')->get();
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                // Créer la promotion
                $promotion = Promotion::create([
                    'nom' => $data['nom'],
                    'description' => $data['description'] ?? null,
                    'reduction' => $data['reduction'],
                    'dateDebut' => $data['dateDebut'],
                    'dateFin' => $data['dateFin'],
                    'actif' => $data['actif'] ?? true
                ]);

                // Associer les produits si fournis
                if (isset($data['produits']) && is_array($data['produits']) && count($data['produits']) > 0) {
                    foreach ($data['produits'] as $produitId) {
                        $this->associerProduitPromotion($promotion->id, $produitId, null);
                    }
                }

                return $promotion->load(['produits']);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création de la promotion: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    public function show($id)
    {
        return Promotion::with(['produits'])->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            try {
                $promotion = Promotion::findOrFail($id);

                // Mettre à jour les infos de base
                $promotion->update([
                    'nom' => $data['nom'] ?? $promotion->nom,
                    'description' => $data['description'] ?? $promotion->description,
                    'reduction' => $data['reduction'] ?? $promotion->reduction,
                    'dateDebut' => $data['dateDebut'] ?? $promotion->dateDebut,
                    'dateFin' => $data['dateFin'] ?? $promotion->dateFin,
                    'actif' => $data['actif'] ?? $promotion->actif
                ]);

                // Mettre à jour les associations produits si fournies
                if (isset($data['produits'])) {
                    // Supprimer les anciennes associations
                    PromotionProduit::where('promo_id', $promotion->id)->delete();

                    // Créer les nouvelles associations
                    if (is_array($data['produits']) && count($data['produits']) > 0) {
                        foreach ($data['produits'] as $produitId) {
                            $this->associerProduitPromotion($promotion->id, $produitId, null);
                        }
                    }
                }

                return $promotion->load(['produits']);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la mise à jour de la promotion: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $promotion = Promotion::findOrFail($id);

            // Supprimer les associations produits
            PromotionProduit::where('promo_id', $promotion->id)->delete();

            // Supprimer la promotion
            return $promotion->delete();
        });
    }

    /**
     * Obtenir les promotions actives
     */
    public function getPromotionsActives()
    {
        $now = Carbon::now();

        return Promotion::with(['produits'])
            ->where('actif', true)
            ->where('dateDebut', '<=', $now)
            ->where('dateFin', '>=', $now)
            ->orderBy('reduction', 'desc')
            ->get();
    }

    /**
     * Obtenir la promotion active pour un produit spécifique
     */
    public function getPromotionActiveForProduit($produitId)
    {
        $now = Carbon::now();

        return Promotion::whereHas('produits', function ($query) use ($produitId) {
            $query->where('produit_id', $produitId);
        })
            ->where('actif', true)
            ->where('dateDebut', '<=', $now)
            ->where('dateFin', '>=', $now)
            ->orderBy('reduction', 'desc')
            ->first();
    }

    /**
     * Obtenir tous les produits en promotion
     */
    public function getProduitsEnPromotion()
    {
        $now = Carbon::now();

        return Produits::whereHas('promotions', function ($query) use ($now) {
            $query->where('actif', true)
                ->where('dateDebut', '<=', $now)
                ->where('dateFin', '>=', $now);
        })->with(['promotions' => function ($query) use ($now) {
            $query->where('actif', true)
                ->where('dateDebut', '<=', $now)
                ->where('dateFin', '>=', $now)
                ->orderBy('reduction', 'desc');
        }])->get();
    }

    /**
     * Calculer le prix avec promotion pour un produit
     */
    public function calculerPrixAvecPromotion($produitId, $prixOriginal = null)
    {
        $promotion = $this->getPromotionActiveForProduit($produitId);

        if (!$promotion) {
            $produit = Produits::findOrFail($produitId);
            return [
                'prix_original' => $produit->prix,
                'prix_avec_promo' => $produit->prix,
                'reduction_pourcentage' => 0,
                'economie' => 0,
                'promotion' => null
            ];
        }

        if ($prixOriginal === null) {
            $produit = Produits::findOrFail($produitId);
            $prixOriginal = $produit->prix;
        }

        // Calculer le prix avec réduction
        $prixAvecPromo = $prixOriginal * (1 - $promotion->reduction / 100);

        return [
            'prix_original' => $prixOriginal,
            'prix_avec_promo' => $prixAvecPromo,
            'reduction_pourcentage' => $promotion->reduction,
            'economie' => $prixOriginal - $prixAvecPromo,
            'promotion' => $promotion
        ];
    }

    /**
     * Associer un produit à une promotion
     */
    public function associerProduitPromotion($promoId, $produitId, $montantReduction = null)
    {
        try {
            // Vérifier si la promotion existe
            $promotion = Promotion::findOrFail($promoId);

            // Vérifier si le produit existe
            $produit = Produits::findOrFail($produitId);

            // Vérifier si l'association existe déjà
            $existing = PromotionProduit::where('promo_id', $promoId)
                ->where('produit_id', $produitId)
                ->first();

            if ($existing) {
                // Mettre à jour si nécessaire
                if ($montantReduction !== null) {
                    $existing->update(['montant_reduction' => $montantReduction]);
                }
                return $existing;
            }

            // Créer la nouvelle association
            return PromotionProduit::create([
                'promo_id' => $promoId,
                'produit_id' => $produitId,
                'montant_reduction' => $montantReduction
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'association produit-promotion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dissocier un produit d'une promotion
     */
    public function dissocierProduitPromotion($promoId, $produitId)
    {
        try {
            $deleted = PromotionProduit::where('promo_id', $promoId)
                ->where('produit_id', $produitId)
                ->delete();

            if ($deleted === 0) {
                throw new \Exception('Association non trouvée');
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la dissociation produit-promotion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Activer/Désactiver une promotion
     */
    public function togglePromotion($id, $actif = null)
    {
        $promotion = Promotion::findOrFail($id);

        if ($actif === null) {
            $actif = !$promotion->actif;
        }

        $promotion->update(['actif' => $actif]);

        return $promotion;
    }

    /**
     * Vérifier si une promotion peut être supprimée
     */
    public function peutEtreSupprimer($id)
    {
        $promotion = Promotion::findOrFail($id);

        // Vérifier s'il y a des commandes en cours avec cette promotion
        $commandesEnCours = DB::table('commande_produits')
            ->where('promo_id', $id)
            ->join('commandes', 'commandes.id', '=', 'commande_produits.commande_id')
            ->whereIn('commandes.statut', ['en_préparation', 'prete', 'en_livraison'])
            ->count();

        return $commandesEnCours === 0;
    }

    /**
     * Dupliquer une promotion
     */
    public function dupliquer($id, array $nouvellesDonnees = [])
    {
        return DB::transaction(function () use ($id, $nouvellesDonnees) {
            $promotionOriginale = $this->show($id);

            $nouvellePromotion = Promotion::create([
                'nom' => $nouvellesDonnees['nom'] ?? $promotionOriginale->nom . ' (Copie)',
                'description' => $nouvellesDonnees['description'] ?? $promotionOriginale->description,
                'reduction' => $nouvellesDonnees['reduction'] ?? $promotionOriginale->reduction,
                'dateDebut' => $nouvellesDonnees['dateDebut'] ?? Carbon::now(),
                'dateFin' => $nouvellesDonnees['dateFin'] ?? Carbon::now()->addMonth(),
                'actif' => $nouvellesDonnees['actif'] ?? false
            ]);

            // Copier les associations produits
            foreach ($promotionOriginale->produits as $produit) {
                $this->associerProduitPromotion(
                    $nouvellePromotion->id,
                    $produit->id,
                    $produit->pivot->montant_reduction ?? null
                );
            }

            return $nouvellePromotion->load(['produits']);
        });
    }
}
