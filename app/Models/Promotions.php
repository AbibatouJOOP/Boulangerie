<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotions extends Model
{
    use HasFactory;
    protected $fillable = ['nom','description','reduction','date_debut','date_fin'];
    // Plusieurs produits peuvent être liés à une promo
    public function produits()
    {
        return $this->belongsToMany(Produits::class);
    }
}
