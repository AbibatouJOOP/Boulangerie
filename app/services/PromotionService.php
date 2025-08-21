<?php

namespace App\services;

use App\Http\Requests\PromotionRequest;
use App\Models\Promotions;
use Illuminate\Http\Request;

class PromotionService
{

    public function index()
    {
        $promotions = Promotions::all();
        return $promotions;
    }

    public function store(array $request)
    {
        //Metier
        $promotion = Promotions::create($request);
        return $promotion;
    }


    public function show($id)
    {
        //Promotion::find($id);
        $promotion = Promotions::findOrFail($id);
        return response()->json($promotion, 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function update(array $request, $id)
    {
        $promotion=this.show($id);
        $promotion->update($request);
        return $promotion;
    }


    public function destroy($id)
    {
        Promotions::destroy($id);
    }
}
