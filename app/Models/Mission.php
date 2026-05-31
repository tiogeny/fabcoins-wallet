<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $table = 'missions';

    protected $fillable = [
        'lab_id', 'title', 'description', 'deadline', 'reference_link', 
        'reward_fc', 'status', 'assigned_maker_id', 'target_maker_id', 
        'spots_total', 'spots_filled'
    ];

    /**
     * La misión fue publicada por un Laboratorio
     */
    public function lab()
    {
        return $this->belongsTo(User::class, 'lab_id');
    }

    /**
     * Si la misión es dirigida, pertenece a un Maker deudor específico
     */
    public function targetMaker()
    {
        return $this->belongsTo(User::class, 'target_maker_id');
    }
}