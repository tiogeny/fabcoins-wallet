<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'maker_id', 'asset_id', 'hours_requested', 'total_fc', 
        'reservation_date', 'status', 'is_reviewed'
    ];

    /**
     * La orden fue realizada por un Maker
     */
    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    /**
     * La orden reservó un activo específico del laboratorio
     */
    public function activo()
    {
        return $this->belongsTo(LabAsset::class, 'asset_id');
    }
}