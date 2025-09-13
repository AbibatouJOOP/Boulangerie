<?php

namespace App\services;

use App\Http\Requests\ProduitRequest;
use App\Models\Produits;
use Illuminate\Http\Request;

class ProduitService
{

    // Seuils d'alerte de stock
    const SEUIL_CRITIQUE = 0;     // Stock épuisé
    const SEUIL_FAIBLE = 5;       // Stock faible
    const SEUIL_MOYEN = 10;       // Stock moyen

    public function index()
    {
        // Inclure la relation avec la catégorie et ajouter les informations de stock
        $produits = Produits::with('categorie')->get();

        // Ajouter les informations d'état du stock pour chaque produit
        $produits->map(function ($produit) {
            $produit->stock_status = $this->getStockStatus($produit->stock);
            $produit->stock_alert_message = $this->getStockAlertMessage($produit->stock);
            $produit->need_restocking = $this->needsRestocking($produit->stock);
            return $produit;
        });

        return $produits;
    }

    public function store(array $data)
    {
        $produit = Produits::create($data);
        $produit = $produit->load('categorie');

        // Ajouter les informations de stock
        $produit->stock_status = $this->getStockStatus($produit->stock);
        $produit->stock_alert_message = $this->getStockAlertMessage($produit->stock);
        $produit->need_restocking = $this->needsRestocking($produit->stock);

        return $produit;
    }

    public function show($id)
    {
        $produit = Produits::with('categorie')->findOrFail($id);

        // Ajouter les informations de stock
        $produit->stock_status = $this->getStockStatus($produit->stock);
        $produit->stock_alert_message = $this->getStockAlertMessage($produit->stock);
        $produit->need_restocking = $this->needsRestocking($produit->stock);

        return $produit;
    }

    public function update(array $data, $id)
    {
        $produit = Produits::findOrFail($id);
        $produit->update($data);
        $produit = $produit->load('categorie');

        // Ajouter les informations de stock
        $produit->stock_status = $this->getStockStatus($produit->stock);
        $produit->stock_alert_message = $this->getStockAlertMessage($produit->stock);
        $produit->need_restocking = $this->needsRestocking($produit->stock);

        return $produit;
    }

    public function destroy($id)
    {
        Produits::destroy($id);
    }

    /**
     * Réapprovisionner un produit
     */
    public function restock($id, $quantite)
    {
        $produit = Produits::findOrFail($id);
        $nouveauStock = $produit->stock + $quantite;

        $produit->update(['stock' => $nouveauStock]);
        $produit = $produit->load('categorie');

        // Ajouter les informations de stock mises à jour
        $produit->stock_status = $this->getStockStatus($produit->stock);
        $produit->stock_alert_message = $this->getStockAlertMessage($produit->stock);
        $produit->need_restocking = $this->needsRestocking($produit->stock);

        return $produit;
    }

    /**
     * Obtenir les produits avec stock faible ou critique
     */
    public function getLowStockProducts()
    {
        $produits = Produits::with('categorie')
            ->where('stock', '<=', self::SEUIL_FAIBLE)
            ->get();

        $produits->map(function ($produit) {
            $produit->stock_status = $this->getStockStatus($produit->stock);
            $produit->stock_alert_message = $this->getStockAlertMessage($produit->stock);
            $produit->need_restocking = $this->needsRestocking($produit->stock);
            return $produit;
        });

        return $produits;
    }

    /**
     * Déterminer le statut du stock
     */
    private function getStockStatus($stock)
    {
        if ($stock <= self::SEUIL_CRITIQUE) {
            return 'critique'; // Rouge
        } elseif ($stock <= self::SEUIL_FAIBLE) {
            return 'faible'; // Orange
        } elseif ($stock <= self::SEUIL_MOYEN) {
            return 'moyen'; // Jaune
        } else {
            return 'bon'; // Vert
        }
    }

    /**
     * Obtenir le message d'alerte approprié
     */
    private function getStockAlertMessage($stock)
    {
        switch ($this->getStockStatus($stock)) {
            case 'critique':
                return 'Stock épuisé ! Réapprovisionnement urgent requis.';
            case 'faible':
                return 'Stock faible. Envisagez un réapprovisionnement.';
            case 'moyen':
                return 'Stock modéré. Surveillance recommandée.';
            default:
                return 'Stock suffisant.';
        }
    }

    /**
     * Déterminer si le produit a besoin d'être réapprovisionné
     */
    private function needsRestocking($stock)
    {
        return $stock <= self::SEUIL_FAIBLE;
    }

    /**
     * Obtenir les statistiques de stock
     */
    public function getStockStatistics()
    {
        $totalProduits = Produits::count();
        $stockCritique = Produits::where('stock', '<=', self::SEUIL_CRITIQUE)->count();
        $stockFaible = Produits::where('stock', '>', self::SEUIL_CRITIQUE)
            ->where('stock', '<=', self::SEUIL_FAIBLE)->count();
        $stockMoyen = Produits::where('stock', '>', self::SEUIL_FAIBLE)
            ->where('stock', '<=', self::SEUIL_MOYEN)->count();
        $stockBon = Produits::where('stock', '>', self::SEUIL_MOYEN)->count();

        return [
            'total' => $totalProduits,
            'critique' => $stockCritique,
            'faible' => $stockFaible,
            'moyen' => $stockMoyen,
            'bon' => $stockBon,
            'pourcentage_critique' => $totalProduits > 0 ? round(($stockCritique / $totalProduits) * 100, 2) : 0,
            'pourcentage_faible' => $totalProduits > 0 ? round(($stockFaible / $totalProduits) * 100, 2) : 0
        ];
    }
}










