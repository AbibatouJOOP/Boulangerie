<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\ProduitService;
use App\Http\Requests\ProduitRequest;

class ProduitController extends Controller
{
    protected $produitService;
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
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


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produit = $this->produitService->show($id);
        return response()->json($produit, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
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


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->produitService->destroy($id);
        return response()->json(null, 204);
    }
}











