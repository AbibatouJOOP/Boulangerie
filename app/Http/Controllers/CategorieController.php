<?php

namespace App\Http\Controllers;
use App\services\CategorieService
use App\Http\Requests\CategorieRequest
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    protected $categorieService;
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        this->$categorieService=new CategorieService();
    }
    public function index()
    {
        $categorie=$categorieService->index();
        return response()->json($categorie,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategorieRequest $request)
    {
        $categorie = $this->$categorieService->store($request->validated());
        return response()->json($categorie,201,[], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categorie = $this->$categorieService->show($id);
        return response()->json($categorie,200,[], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategorieRequest $request, string $id)
    {
        $categorie = $this->offreService->update($request->validated(),$id);
        return response->json(
            [
                "message"=>"offre bien modifier",
                "offre"=> $offre
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
