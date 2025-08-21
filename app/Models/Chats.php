<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
    use HasFactory;
    protected $fillable = ['client_id','employe_id','message','est_lu'];
     // Expéditeur
    public function expediteur()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
     // Recepteur
    public function recepteur()
    {
        return $this->belongsTo(User::class, 'employe_id');
    }
}
