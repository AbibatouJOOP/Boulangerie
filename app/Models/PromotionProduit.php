<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionProduit extends Model
{
    use HasFactory;
    protected $fillable = ['produit_id','promo_id','montant_reduction'];
}
