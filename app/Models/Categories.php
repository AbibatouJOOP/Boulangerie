<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
    protected $fillable = ['nom','description'];
    //un categorie peut avoir plusieurs produits
    public function produits() {
        return $this->hasMany(Produits::class);
    }
}
