<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('categories',\App\Http\Controllers\CategorieController::class);
Route::apiResource('produits',\App\Http\Controllers\ProduitController::class);//->middleware('auth:sanctum');
Route::apiResource('commandes',\App\Http\Controllers\CommandeController::class);//->middleware('auth:sanctum');

