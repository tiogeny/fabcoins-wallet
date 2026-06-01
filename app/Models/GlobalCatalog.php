<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalCatalog extends Model
{
    // ⚙️ OBLIGAMOS A LARAVEL A USAR EL SINGULAR REAL DE TU SQL
    protected $table = 'global_catalog';

    // Tu volcado original no tiene columnas created_at/updated_at en esta tabla
    public $timestamps = false; 
}