<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@fabcoins.co';

        // Evitar duplicaciones accidentales en ejecuciones consecutivas
        if (!DB::table('users')->where('email', $email)->exists()) {
            DB::table('users')->insert([
                'name' => 'Super Administrador',
                'email' => $email,
                'password' => Hash::make('admin123'), // Cambiar inmediatamente en producción
                'role' => 'superadmin',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Super+Admin&background=3498db&color=fff',
                'slug' => Str::slug('Super Administrador') . '-' . rand(100, 999),
                'force_password_change' => 0,
                'preferred_lang' => 'es',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}