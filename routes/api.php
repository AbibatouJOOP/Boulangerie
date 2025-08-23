<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;

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
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    Route::post('/logout', [AuthController::class, 'logout']);
    /*Route::get('/user', function (Request $request) {
        return $request->user();
    });*/
    Route::middleware('role:ADMIN')->group(function () {
        Route::apiResource('categories', CategorieController::class);
        Route::apiResource('produits', ProduitController::class);
    });
    Route::middleware('role:ADMIN,AMPLOYE')->group(function () {
        Route::apiResource('commandes', CommandeController::class);
    });
    Route::middleware('role:CLIENT')->group(function () {
        Route::get('produits', [ProduitController::class, 'index']);   // Voir les produits
        Route::post('commandes', [CommandeController::class, 'store']); // Passer une commande
    });
    return $request->user();
});


