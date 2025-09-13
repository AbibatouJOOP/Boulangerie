<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\services\ProduitService;
use App\Http\Requests\ProduitRequest;

class ProduitController extends Controller
{
    protected $produitService;

    public function __construct(){
        $this->produitService = new ProduitService();
    }

    public function index(Request $request)
    {
        // Log pour debug
        \Log::info('Accès à index produits', [
            'user' => $request->user()->email ?? 'non défini',
            'role' => $request->user()->role ?? 'non défini'
        ]);

        $produits = $this->produitService->index();
        return response()->json($produits, 200);
    }

    public function store(ProduitRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Sauvegarde dans storage/app/public/produits
            $path = $request->file('image')->store('produits', 'public');
            $data['image'] = $path; // enregistre le chemin dans la BDD
        }

        $produit = $this->produitService->store($data);

        return response()->json($produit, 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function show(string $id)
    {
        $produit = $this->produitService->show($id);
        return response()->json($produit, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function update(ProduitRequest $request, string $id)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('produits', 'public');
            $data['image'] = $path;
        }

        $produit = $this->produitService->update($data, $id);

        return response()->json([
            "message" => "Produit mis à jour",
            "produit" => $produit
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function indexClient() {
        $produits = $this->produitService->index();
        return response()->json($produits, 200);
    }

    public function destroy(string $id)
    {
        $this->produitService->destroy($id);
        return response()->json(null, 204);
    }

    /**
     * Réapprovisionner un produit
     */
    public function restock(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'quantite' => 'required|integer|min:1|max:10000'
        ]);

        try {
            $produit = $this->produitService->restock($id, $request->quantite);

            return response()->json([
                'message' => "Produit réapprovisionné avec succès. Nouveau stock: {$produit->stock}",
                'produit' => $produit
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du réapprovisionnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les produits avec stock faible
     */
    public function getLowStockProducts(): JsonResponse
    {
        $produits = $this->produitService->getLowStockProducts();
        return response()->json($produits, 200);
    }

    /**
     * Obtenir les statistiques de stock
     */
    public function getStockStatistics(): JsonResponse
    {
        $stats = $this->produitService->getStockStatistics();
        return response()->json($stats, 200);
    }
}











