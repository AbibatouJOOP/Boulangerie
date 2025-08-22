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
    public function index()
    {
        $produits=$this->produitService->index();
        return response()->json($produits,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProduitRequest $request)
    {
        $produit=$this->produitService->store($request->validated());
        return response()->json($produit,201,[], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produit=$this->produitService->show($id);
        return response()->json($produit,200,[],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProduitRequest $request, string $id)
    {
        $produit = $this->produitService->update($request->validated(),$id);
        return response->json(
            [
                "message"=>"produit bien modifier",
                "produit"=> $produit
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->produitService->destroy($id);
        return response()->json("",204);
    }
}
