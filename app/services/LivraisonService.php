<?php

namespace App\services;

use App\Http\Requests\LivraisonRequest;
use App\Models\Livraisons;
use Illuminate\Http\Request;

class LivraisonService
{

    public function index()
    {
        $livraisons = Livraisons::all();
        return $livraisons;
    }

    public function store(array $request)
    {
        //Metier
        $livraison = Livraisons::create($request);
        return $livraison;
    }


    public function show($id)
    {
        //Livraison::find($id);
        $livraison = Livraisons::findOrFail($id);
        return response()->json($livraison, 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function update(array $request, $id)
    {
        $livraison=this.show($id);
        $livraison->update($request);
        return $livraison;
    }


    public function destroy($id)
    {
        Livraisons::destroy($id);
    }
}
