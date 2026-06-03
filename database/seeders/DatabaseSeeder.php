<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('financing_agreements')->truncate();
        DB::table('orders')->truncate();
        DB::table('transactions')->truncate();
        DB::table('notifications')->truncate();
        DB::table('missions')->truncate();
        DB::table('lab_assets')->truncate();
        DB::table('global_catalog')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        // Inyección de Cuentas Maestras
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Beno (Super Admin)',
                'email' => 'beno@fabcoins.org',
                'password' => Hash::make('admin123'),
                'role' => 'superadmin',
                'slug' => 'beno-admin',
                'reputation_score' => 5.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Fab Lab Perú',
                'email' => 'contacto@fablabperu.org',
                'password' => Hash::make('lab123'),
                'role' => 'lab',
                'slug' => 'fab-lab-peru',
                'reputation_score' => 5.00,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 🔥 INYECCIÓN DEL CATÁLOGO SIMPLIFICADO SOLICITADO
        DB::table('global_catalog')->insert([
            // 1. MÁQUINAS (Nombres directos del hardware estructural)
            ['id' => 1, 'asset_type' => 'machine', 'generic_name' => 'Impresora 3D', 'suggested_price_fc' => 15.00],
            ['id' => 2, 'asset_type' => 'machine', 'generic_name' => 'Cortadora Láser', 'suggested_price_fc' => 45.00],
            ['id' => 3, 'asset_type' => 'machine', 'generic_name' => 'Fresadora', 'suggested_price_fc' => 60.00],
            ['id' => 4, 'asset_type' => 'machine', 'generic_name' => 'Escaner', 'suggested_price_fc' => 20.00],
            ['id' => 5, 'asset_type' => 'machine', 'generic_name' => 'Ploter', 'suggested_price_fc' => 25.00],
            ['id' => 6, 'asset_type' => 'machine', 'generic_name' => 'Cortadora de Vinil', 'suggested_price_fc' => 15.00],

            // 2. SERVICIOS (Asesorías y Talleres Formativos integrados)
            ['id' => 7, 'asset_type' => 'service', 'generic_name' => 'Asesoría Especializada', 'suggested_price_fc' => 50.00],
            ['id' => 8, 'asset_type' => 'service', 'generic_name' => 'Taller Académico Intensivo', 'suggested_price_fc' => 100.00],

            // 3. LABS (Infraestructura Espacial)
            ['id' => 9, 'asset_type' => 'lab', 'generic_name' => 'Estación de Trabajo / Espacio', 'suggested_price_fc' => 10.00]
        ]);

        // Alerta inicial de control de la bitácora
        DB::table('notifications')->insert([
            'user_id' => 2,
            'message' => 'Ecosistema listo bajo los 3 Macro-Ejes. Procede con el enlistamiento de infraestructura.',
            'type' => 'info',
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}