<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraisons extends Model
{
    use HasFactory;
    protected $fillable = ['commande_id','employe_id','statut','date_livraison','adresse_livraison'];
}
