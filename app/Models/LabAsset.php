<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabAsset extends Model
{
    protected $table = 'lab_assets';

    protected $fillable = [
        'lab_id', 'catalog_id', 'asset_type', 'custom_name', 
        'useful_life_hours', 'consumed_hours', 'tokenization_pct', 
        'usd_value', 'useful_life_units', 'wear_pct', 'allocation_pct', 
        'generated_fc', 'status', 'set_price_fc', 'expires_at'
    ];

    /**
     * El activo pertenece a un Laboratorio
     */
    public function lab()
    {
        return $this->belongsTo(User::class, 'lab_id');
    }

    /**
     * El activo corresponde a una categoría del catálogo global
     */
    public function categoriaGlobal()
    {
        return $this->belongsTo(GlobalCatalog::class, 'catalog_id');
    }
}