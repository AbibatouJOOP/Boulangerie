<?php

namespace App\Http\Controllers;
use App\Http\Requests\CommandeRequest;
use App\services\CommandeService;
use App\Models\Commandes;

use Illuminate\Http\Request;

class CommandeController extends Controller
{
    protected $commandeService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->commandeService = new CommandeService();
    }

    public function index()
    {
        $commandes=$this->commandeService->index();
        return response()->json($commandes,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommandeRequest $request)
    {
        $commande=$this->commandeService->store($request->validated());
        return response()->json($commande,,201,[], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $commande=$this->commandeService->show($id);
        return response()->json($commande,200,[],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
