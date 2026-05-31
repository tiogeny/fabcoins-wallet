<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * CAMPOS PERMITIDOS PARA GUARDARSE DE FORMA MASIVA
     * (Mapeados directamente desde tu SQL original)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
        'role',
        'avatar_url',
        'bio',
        'address',
        'force_password_change',
        'reputation_score',
        'deuda_inicial_fc',
        'deuda_fc',
        'deuda_lab_id',
        'preferred_lang',
        'latitude',
        'longitude',
        'onboarding_completed'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'force_password_change' => 'boolean',
        'onboarding_completed' => 'boolean',
    ];

    // =========================================================
    // 📊 RELACIONES DE TU ECOSISTEMA ECONÓMICO
    // =========================================================

    /**
     * Un usuario (Lab o Maker) tiene muchas transacciones contables
     */
    public function transacciones()
    {
        return $this->hasMany(Transaction::class, 'user_id'); [cite: 3]
    }

    /**
     * Un Laboratorio es dueño de muchos activos operativos (máquinas, espacios)
     */
    public function activos()
    {
        return $this->hasMany(LabAsset::class, 'lab_id'); [cite: 3]
    }

    /**
     * Un Laboratorio publica muchas misiones de trabajo
     */
    public function misiones()
    {
        return $this->hasMany(Mission::class, 'lab_id'); [cite: 3]
    }

    // =========================================================
    // 🧠 ATRIBUTOS DINÁMICOS (MAGIA DE LARAEL)
    // =========================================================

    /**
     * Tu Ecuación Contable Original convertida a código nativo.
     * Calcula el saldo neto del usuario en tiempo real sin consultas SQL sucias.
     */
    public function getSaldoTotalAttribute()
    {
        // Sumamos todas las operaciones que inyectan dinero (ingresos y emisiones mint)
        $ingresos = $this->transacciones()->whereIn('type', ['income', 'mint'])->sum('amount');
        
        // Sumamos todas las operaciones que retiran o congelan dinero (gastos, escrow y quema)
        $egresos  = $this->transacciones()->whereIn('type', ['expense', 'escrow', 'burn'])->sum('amount');

        return $ingresos - $egresos;
    }
}