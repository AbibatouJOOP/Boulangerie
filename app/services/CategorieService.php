<?php

namespace App\services;

use App\Http\Requests\CategorieRequest;
use App\Models\Categories;
use Illuminate\Http\Request;

class CategorieService
{

    public function index()
    {
        $categories = Categories::all();
        return $categories;
    }

    public function store(array $request)
    {
        //Metier
        $categorie = Categories::create($request);
        return $categorie;
    }


    public function show($id)
    {
        //Categorie::find($id);
        $categorie = Categories::findOrFail($id);
        return $categorie;
    }


    public function update(array $request, $id)
    {
        $categorie = Categories::findOrFail($id);
        $categorie->update($request);
        return $categorie;
    }


    public function destroy($id)
    {
        Categories::destroy($id);
    }
}
