<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabAsset extends Model
{
    protected $table = 'lab_assets';

    protected $fillable = [
        'lab_id', 'catalog_id', 'asset_type', 'custom_name', 
        'useful_life_hours', 'consumed_hours', 'tokenization_pct', 
        'set_price_fc', 'generated_fc', 'status', 'expires_at'
    ];

    // Conexión exacta de la llave foránea con el catálogo
    public function categoriaGlobal()
    {
        return $this->belongsTo(GlobalCatalog::class, 'catalog_id');
    }
}