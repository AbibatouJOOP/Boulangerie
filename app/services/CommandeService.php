<?php

namespace App\services;

use App\Http\Requests\CommandeRequest;
use App\Models\Commandes;
use Illuminate\Http\Request;

class CommandeService
{

    public function index()
    {
        $commandes = Commandes::all();
        return $commandes;
    }

    public function store(array $request)
    {
        //Metier
        $commande = Commandes::create($request);
        return $commande;
    }


    public function show($id)
    {
        //Commande::find($id);
        $commande = Commandes::findOrFail($id);
        return response()->json($commande, 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function update(array $request, $id)
    {
        $commande=this.show($id);
        $commande->update($request);
        return $commande;
    }


    public function destroy($id)
    {
        Commandes::destroy($id);
    }
}
