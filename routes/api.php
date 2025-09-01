<?php

use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\PaiementController;
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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) { return $request->user(); });
    // Pour les admin
    Route::middleware('role:ADMIN')->group(function() {
        Route::apiResource('categories', CategorieController::class);
        Route::apiResource('produits', ProduitController::class);
        Route::delete('commandes/{id}', [CommandeController::class, 'destroy']);
    });

    // Pour les admins et employés
    Route::middleware('role:ADMIN,EMPLOYE')->group(function() {
        // Voir toutes les commandes
        Route::get('commandes', [CommandeController::class, 'index']);
        Route::get('commandes/{id}', [CommandeController::class, 'show']);
        // Mettre à jour le statut
        Route::put('commandes/{id}', [CommandeController::class, 'update']);
        Route::put('/commande/{commandeId}/livraison', [LivraisonController::class, 'updateStatutLivraison']);
        Route::put('/commande/{commandeId}/paiement', [PaiementController::class, 'updateStatutPaiement']);
        Route::apiResource('livraisons', LivraisonController::class);
        Route::apiResource('paiements', PaiementController::class);
    });

    // Pour les clients
    Route::middleware('role:CLIENT')->group(function() {
        Route::get('produitsClient', [ProduitController::class, 'indexClient']);
        // Le client crée sa commande
        Route::post('commandes', [CommandeController::class, 'store']);
        // Le client voit uniquement ses commandes
        Route::get('mes-commandes', [CommandeController::class, 'mesCommandes']);
    });

});


