<?php

namespace App\services;

use App\Http\Requests\ProduitRequest;
use App\Models\Produits;
use Illuminate\Http\Request;

class ProduitService
{

    public function index()
    {
        $produits = Produits::all();
        return $produits;
    }

    public function store(array $request)
    {
        //Metier
        $produit = Produits::create($request);
        return $produit;
    }


    public function show($id)
    {
        //Produits::find($id);
        $produit = Produits::findOrFail($id);
        return response()->json($produit, 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function update(array $request, $id)
    {
        $produit=this.show($id);
        $produit->update($request);
        return $produit;
    }


    public function destroy($id)
    {
        Produits::destroy($id);
    }
}
