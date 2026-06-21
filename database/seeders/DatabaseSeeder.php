<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🧼 1. VACIADO DE TABLAS (CLEAN SLATE CONTROLADO DESDE EL SEEDER)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Tablas de memoria y sesiones
        DB::table('cache')->truncate();
        DB::table('sessions')->truncate();
        
        // Tablas core de diccionarios y catálogos
        DB::table('global_catalog')->truncate();
        DB::table('global_settings')->truncate();
        DB::table('skills_catalog')->truncate();
        
        // Tablas operativas de transacciones e infraestructura
        DB::table('transactions')->truncate();
        DB::table('orders')->truncate();
        DB::table('mission_applications')->truncate();
        DB::table('missions')->truncate();
        DB::table('financing_agreements')->truncate();
        DB::table('lab_assets')->truncate();
        DB::table('user_skills')->truncate();
        DB::table('reviews')->truncate();
        DB::table('notifications')->truncate();
        DB::table('users')->truncate();
        
        // ⚠️ NOTA TÉCNICA: La tabla 'migrations' NO se vacía para proteger el Schema Dump
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 👤 2. INYECCIÓN DEL TRONO CENTRAL (SUPERADMIN REAL)
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'FabCoins Central',
                'email' => 'central@fabcoins.co',
                'password' => Hash::make('central123'),
                'role' => 'superadmin',
                'slug' => Str::slug('FabCoins Central'),
                'reputation_score' => 5.0,
                'force_password_change' => 0,
                'preferred_lang' => 'es',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}