<?php

namespace App\services;

use App\Http\Requests\ProduitRequest;
use App\Models\Produits;
use Illuminate\Http\Request;

class ProduitService
{

    public function index()
    {
        // Inclure la relation avec la catégorie
        $produits = Produits::with('categorie')->get();
        return $produits;
    }

    public function store(array $data)
    {
        $produit = Produits::create($data);
        return $produit->load('categorie'); // Charger la relation
    }


    public function show($id)
    {
        $produit = Produits::with('categorie')->findOrFail($id);
        return $produit; // Retourner directement le modèle, pas une response
    }


    public function update(array $data, $id)
    {
        $produit = Produits::findOrFail($id);
        $produit->update($data);
        return $produit->load('categorie');
    }


    public function destroy($id)
    {
        Produits::destroy($id);
    }
}










