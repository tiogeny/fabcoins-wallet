<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🧼 1. LIMPIEZA ATÓMICA DE TABLAS
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
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
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 👤 2. INSERCIÓN DE USUARIOS CON ALINEACIÓN SIMÉTRICA
        DB::table('users')->insert([
            [
                'id' => 1, 'name' => 'Fab Lab Perú', 'email' => 'peru@fablab.com', 
                'password' => Hash::make('password'), 'role' => 'lab', 'slug' => 'fab-lab-peru',
                'reputation_score' => 5.0, 'preferred_lang' => 'es', 'city' => 'Lima', 'country' => 'Perú',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 6, 'name' => 'TinkiLab', 'email' => 'hola@tinkilab.com', 
                'password' => Hash::make('password'), 'role' => 'lab', 'slug' => 'tinkilab',
                'reputation_score' => 4.8, 'preferred_lang' => 'en', 'city' => 'Lima', 'country' => 'Perú',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2, 'name' => 'Ana Código', 'email' => 'ana@creator.com', 
                'password' => Hash::make('password'), 'role' => 'creator', 'slug' => 'ana-codigo',
                'reputation_score' => 5.0, 'preferred_lang' => 'es', 'city' => null, 'country' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3, 'name' => 'Luis Maker', 'email' => 'luis@creator.com', 
                'password' => Hash::make('password'), 'role' => 'creator', 'slug' => 'luis-maker',
                'reputation_score' => 4.5, 'preferred_lang' => 'es', 'city' => null, 'country' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 8, 'name' => 'Carlos Designer', 'email' => 'carlos@creator.com', 
                'password' => Hash::make('password'), 'role' => 'creator', 'slug' => 'carlos-designer',
                'reputation_score' => 4.2, 'preferred_lang' => 'es', 'city' => null, 'country' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 9, 'name' => 'Elena Engineer', 'email' => 'elena@creator.com', 
                'password' => Hash::make('password'), 'role' => 'creator', 'slug' => 'elena-engineer',
                'reputation_score' => 0.0, 'preferred_lang' => 'en', 'city' => null, 'country' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4, 'name' => 'Super Administrador', 'email' => 'admin@fabcoins.co', 
                'password' => Hash::make('password'), 'role' => 'superadmin', 'slug' => 'super-administrador',
                'reputation_score' => 0.0, 'preferred_lang' => 'es', 'city' => null, 'country' => null,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 🪙 3. MINT INICIAL (10,000 FC TOTAL)
        DB::table('transactions')->insert([
            ['user_id' => 1, 'description' => 'Transformación (Mint) Inicial de Capacidad', 'amount' => 6000.00, 'type' => 'mint', 'created_at' => now()->subDays(10)],
            ['user_id' => 6, 'description' => 'Transformación (Mint) Inicial de Capacidad', 'amount' => 4000.00, 'type' => 'mint', 'created_at' => now()->subDays(10)]
        ]);

        // ⚙️ 4. INFRAESTRUCTURA OPERATIVA
        DB::table('lab_assets')->insert([
            ['id' => 1, 'lab_id' => 1, 'catalog_id' => 1, 'asset_type' => 'machine', 'custom_name' => 'Ultimaker S5', 'useful_life_hours' => 2000.00, 'consumed_hours' => 100.00, 'tokenization_pct' => 30, 'generated_fc' => 6000.00, 'status' => 'active', 'set_price_fc' => 10.00, 'expires_at' => '2027-12-31', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'lab_id' => 6, 'catalog_id' => 2, 'asset_type' => 'machine', 'custom_name' => 'Trotec Laser', 'useful_life_hours' => 1500.00, 'consumed_hours' => 50.00, 'tokenization_pct' => 50, 'generated_fc' => 4000.00, 'status' => 'active', 'set_price_fc' => 30.00, 'expires_at' => '2027-12-31', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 🎯 5. MISIONES INDUSTRIALES
        DB::table('missions')->insert([
            ['id' => 1, 'lab_id' => 1, 'title' => 'Diseño Carcasa 3D', 'description' => 'Modelado de componentes', 'deadline' => now()->subDays(2), 'reward_fc' => 300.00, 'status' => 'completed', 'spots_total' => 1, 'spots_filled' => 1, 'created_at' => now()->subDays(5)],
            ['id' => 2, 'lab_id' => 6, 'title' => 'Desarrollo Firmware IoT', 'description' => 'Código en C++', 'deadline' => now()->subDays(1), 'reward_fc' => 200.00, 'status' => 'completed', 'spots_total' => 1, 'spots_filled' => 1, 'created_at' => now()->subDays(4)],
            ['id' => 3, 'lab_id' => 1, 'title' => 'Asistencia de Laboratorio', 'description' => 'Soporte técnico', 'deadline' => now()->addDays(5), 'reward_fc' => 150.00, 'status' => 'open', 'spots_total' => 2, 'spots_filled' => 0, 'created_at' => now()]
        ]);

        DB::table('mission_applications')->insert([
            ['mission_id' => 1, 'creator_id' => 2, 'status' => 'accepted', 'is_reviewed' => 1, 'created_at' => now()->subDays(4)],
            ['mission_id' => 2, 'creator_id' => 3, 'status' => 'accepted', 'is_reviewed' => 1, 'created_at' => now()->subDays(3)]
        ]);

        // 🏢 6. ÓRDENES DE ALQUILER REALES SINCRO
        DB::table('orders')->insert([
            ['id' => 1, 'creator_id' => 2, 'asset_id' => 1, 'hours_requested' => 5.00, 'total_fc' => 50.00, 'reservation_date' => now()->subDays(2), 'status' => 'completed', 'is_reviewed' => 1, 'created_at' => now()->subDays(2)],
            ['id' => 2, 'creator_id' => 3, 'asset_id' => 2, 'hours_requested' => 3.00, 'total_fc' => 90.00, 'reservation_date' => now()->subDays(1), 'status' => 'completed', 'is_reviewed' => 1, 'created_at' => now()->subDays(1)]
        ]);

        // 🎓 7. ACUERDOS ISA DE HONOR
        DB::table('financing_agreements')->insert([
            ['id' => 1, 'lab_id' => 1, 'creator_id' => 8, 'amount_initial' => 500.00, 'amount_remaining' => 470.00, 'description' => 'Crédito académico', 'status' => 'active', 'created_at' => now()->subDays(8)],
            ['id' => 2, 'lab_id' => 6, 'creator_id' => 9, 'amount_initial' => 200.00, 'amount_remaining' => 200.00, 'description' => 'Financiamiento Taller', 'status' => 'active', 'created_at' => now()->subDays(5)]
        ]);

        // 📜 8. LIBRO MAYOR TOTALMENTE BALANCEADO (CON ESTADOS FINANCIEROS DE TRANSICIÓN VIVOS)
        DB::table('transactions')->insert([
            // LAB 1 (Fab Lab Perú): Mint (6000) - Escrow Abierto Misión 3 (300) - Egreso Consumado Misión 1 (300) = 5400 FC Líquidos
            ['user_id' => 1, 'description' => 'Garantía formal liquidada por Misión #1 (Pago enviado a Ana Código)', 'amount' => 300.00, 'type' => 'expense', 'created_at' => now()->subDays(5)],
            ['user_id' => 1, 'description' => 'Retención preventiva en Escrow para Misión #3 (2 cupos abiertos)', 'amount' => 300.00, 'type' => 'escrow', 'created_at' => now()],
            ['user_id' => 1, 'description' => 'Capacidad de infraestructura quemada por Orden #1', 'amount' => 50.00, 'type' => 'consumed', 'created_at' => now()->subDays(2)],
            ['user_id' => 1, 'description' => 'Amortización de crédito recibida de Carlos Designer', 'amount' => 30.00, 'type' => 'consumed', 'created_at' => now()],

            // LAB 6 (TinkiLab): Mint (4000) - Egreso Consumado Misión 2 (200) = 3800 FC Líquidos
            ['user_id' => 6, 'description' => 'Garantía formal liquidada por Misión #2 (Pago enviado a Luis Maker)', 'amount' => 200.00, 'type' => 'expense', 'created_at' => now()->subDays(4)],
            ['user_id' => 6, 'description' => 'Capacidad de infraestructura quemada por Orden #2', 'amount' => 90.00, 'type' => 'consumed', 'created_at' => now()->subDays(1)],

            // ANA CÓDIGO (ID 2): Gana 300, gasta 50. Saldo Líquido = 250 FC
            ['user_id' => 2, 'description' => 'Pago recibido por Misión #1', 'amount' => 300.00, 'type' => 'income', 'created_at' => now()->subDays(4)],
            ['user_id' => 2, 'description' => 'Servicio consumido (Orden #1)', 'amount' => 50.00, 'type' => 'expense', 'created_at' => now()->subDays(2)],

            // LUIS MAKER (ID 3): Gana 200, gasta 90 en Orden #2, envía 30 P2P. Saldo Líquido = 80 FC
            ['user_id' => 3, 'description' => 'Pago recibido por Misión #2', 'amount' => 200.00, 'type' => 'income', 'created_at' => now()->subDays(3)],
            ['user_id' => 3, 'description' => 'Servicio consumido (Orden #2)', 'amount' => 90.00, 'type' => 'expense', 'created_at' => now()->subDays(1)],
            ['user_id' => 3, 'description' => 'Envío P2P de remesa directa a Carlos Designer', 'amount' => 30.00, 'type' => 'expense', 'created_at' => now()],

            // CARLOS DESIGNER (ID 8): Recibe 30, amortiza 30. Saldo Líquido = 0 FC
            ['user_id' => 8, 'description' => 'Recibido P2P de Luis Maker', 'amount' => 30.00, 'type' => 'income', 'created_at' => now()],
            ['user_id' => 8, 'description' => 'Abono voluntario de Crédito al Laboratorio', 'amount' => 30.00, 'type' => 'expense', 'created_at' => now()]
        ]);
    }
}