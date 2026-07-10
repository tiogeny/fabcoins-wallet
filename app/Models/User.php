<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\MailService; // 🚀 IMPORTAMOS NUESTRO MOTOR DE CORREOS

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * CAMPOS PERMITIDOS PARA GUARDARSE DE FORMA MASIVA
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
        'social_linkedin',    // 👈 NUEVO Y CORRECTO
        'social_github',      // 👈 NUEVO Y CORRECTO
        'social_portfolio',   // 👈 NUEVO Y CORRECTO
        'fab_academy_url',    // 👈 Nombre correcto
        'instagram_url',      // 👈 Nombre correcto
        'city',               // 👈 NUEVO
        'country',            // 👈 NUEVO
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
    // RELACIONES DE ELORENT
    // =========================================================

    public function transacciones()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function activos()
    {
        return $this->hasMany(LabAsset::class, 'lab_id');
    }

    public function misiones()
    {
        return $this->hasMany(Mission::class, 'lab_id');
    }

    // =========================================================
    // ATRIBUTOS DINÁMICOS
    // =========================================================

    public function getSaldoTotalAttribute()
    {
        $ingresos = $this->transacciones()->whereIn('type', ['income', 'mint'])->sum('amount');
        $egresos  = $this->transacciones()->whereIn('type', ['expense', 'escrow', 'burn'])->sum('amount');

        return $ingresos - $egresos;
    }

    /**
     * 🔒 OVERRIDE: Intercepta el correo de recuperar contraseña nativo de Laravel
     * y lo redirige a nuestro MailService Premium Industrial.
     */
    public function sendPasswordResetNotification($token)
    {
        // Construye la URL segura que Laravel espera para procesar el cambio de clave
        $urlSecure = url(route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));

        // Despacha nuestra plantilla con el diseño Dark de FabCoins
        MailService::recuperarPassword($this->email, $urlSecure);
    }
}