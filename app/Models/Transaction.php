<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // Nombre exacto de la tabla en MySQL
    protected $table = 'transactions';

    protected $fillable = ['user_id', 'description', 'amount', 'type'];

    /**
     * Una transacción pertenece a un único usuario (Lab o Maker)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}