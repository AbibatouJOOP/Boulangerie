<?php

namespace App\services;

use App\Http\Requests\PaiementRequest;
use App\Models\Paiements;
use Illuminate\Http\Request;

class PaiementService
{

    public function index()
    {
        $paiements = Paiements::all();
        return $paiements;
    }

    public function store(array $request)
    {
        //Metier
        $paiement = Paiements::create($request);
        return $paiement;
    }


    public function show($id)
    {
        //Paiements::find($id);
        $paiement = Paiements::findOrFail($id);
        return response()->json($paiement, 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function update(array $request, $id)
    {
        $paiement=this.show($id);
        $paiement->update($request);
        return $paiement;
    }


    public function destroy($id)
    {
        Paiements::destroy($id);
    }
}
