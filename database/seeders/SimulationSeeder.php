<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SimulationSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('skills_catalog')->truncate();
        DB::table('user_skills')->truncate();
        DB::table('global_catalog')->truncate();
        DB::table('lab_assets')->truncate();
        DB::table('missions')->truncate();
        DB::table('mission_applications')->truncate();
        DB::table('orders')->truncate();
        DB::table('transactions')->truncate();
        DB::table('financing_agreements')->truncate();
        DB::table('reviews')->truncate();
        DB::table('notifications')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();
        $password = Hash::make('password123');

        // CATÁLOGOS BASE
        DB::table('skills_catalog')->insert([
            ['id' => 1, 'name' => 'Modelado 3D', 'type' => 'hard', 'created_at' => $now->format('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'Programación C++', 'type' => 'hard', 'created_at' => $now->format('Y-m-d H:i:s')],
            ['id' => 3, 'name' => 'Trabajo en Equipo', 'type' => 'soft', 'created_at' => $now->format('Y-m-d H:i:s')],
        ]);

        DB::table('global_catalog')->insert([
            ['id' => 1, 'generic_name' => 'Impresión 3D FDM', 'created_at' => $now->format('Y-m-d H:i:s')],
            ['id' => 2, 'generic_name' => 'Corte Láser CNC', 'created_at' => $now->format('Y-m-d H:i:s')],
        ]);

        // ACTORES
        $labA = DB::table('users')->insertGetId([
            'name' => 'Fab Lab Perú', 'email' => 'peru@fablab.com', 'password' => $password,
            'role' => 'lab', 'slug' => 'fab-lab-peru', 'reputation_score' => 5.0,
            'city' => 'Lima', 'country' => 'Perú', 'created_at' => $now->copy()->subDays(12)->format('Y-m-d H:i:s')
        ]);

        $creator1 = DB::table('users')->insertGetId([
            'name' => 'Ana Código', 'email' => 'ana@creator.com', 'password' => $password,
            'role' => 'creator', 'slug' => 'ana-codigo', 'reputation_score' => 5.0,
            'created_at' => $now->copy()->subDays(12)->format('Y-m-d H:i:s')
        ]);
        DB::table('user_skills')->insert(['user_id' => $creator1, 'skill_id' => 1, 'created_at' => $now->format('Y-m-d H:i:s')]);

        $creator2 = DB::table('users')->insertGetId([
            'name' => 'Luis Maker', 'email' => 'luis@creator.com', 'password' => $password,
            'role' => 'creator', 'slug' => 'luis-maker', 'reputation_score' => 4.5,
            'deuda_fc' => 30, 'deuda_inicial_fc' => 100, 'deuda_lab_id' => $labA, 'created_at' => $now->copy()->subDays(12)->format('Y-m-d H:i:s')
        ]);

        // EMISIÓN BASE CONTABLE
        DB::table('transactions')->insert([
            'user_id' => $labA, 'description' => 'Transformación (Mint) Inicial', 
            'amount' => 5000, 'type' => 'mint', 'created_at' => $now->copy()->subDays(10)->format('Y-m-d H:i:s')
        ]);

        // ASSETS EN INVENTARIO
        $asset3D = DB::table('lab_assets')->insertGetId([
            'lab_id' => $labA, 'catalog_id' => 1, 'custom_name' => 'Prusa i3 MK3S',
            'set_price_fc' => 10, 'status' => 'active', 'useful_life_hours' => 1000, 'consumed_hours' => 2, 'created_at' => $now->copy()->subDays(9)->format('Y-m-d H:i:s')
        ]);
        
        $assetLaser = DB::table('lab_assets')->insertGetId([
            'lab_id' => $labA, 'catalog_id' => 2, 'custom_name' => 'Epilog Zing 24',
            'set_price_fc' => 50, 'status' => 'active', 'useful_life_hours' => 2000, 'consumed_hours' => 2, 'created_at' => $now->copy()->subDays(9)->format('Y-m-d H:i:s')
        ]);

        // FLUJO 1: MISIÓN DE ANA (100 FC)
        $misionAna = DB::table('missions')->insertGetId([
            'lab_id' => $labA, 'title' => 'Diseño de Carcasa IoT', 'description' => 'Modelar carcasa.',
            'reward_fc' => 100, 'spots_total' => 1, 'spots_filled' => 1, 'status' => 'completed',
            'created_at' => $now->copy()->subDays(8)->format('Y-m-d H:i:s'), 'deadline' => $now->copy()->subDays(4)->format('Y-m-d H:i:s')
        ]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Reserva en Escrow (Misión 1)', 'amount' => 100, 'type' => 'escrow', 'created_at' => $now->copy()->subDays(8)->format('Y-m-d H:i:s')]);
        DB::table('mission_applications')->insert(['mission_id' => $misionAna, 'creator_id' => $creator1, 'status' => 'accepted', 'is_reviewed' => true, 'created_at' => $now->copy()->subDays(7)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $creator1, 'description' => 'Pago por Misión #1', 'amount' => 100, 'type' => 'income', 'created_at' => $now->copy()->subDays(6)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Liberación (Misiones #1)', 'amount' => 0, 'type' => 'info', 'created_at' => $now->copy()->subDays(6)->format('Y-m-d H:i:s')]);

        // FLUJO 2: ALQUILER DE ANA (20 FC CONSUMIDOS)
        DB::table('orders')->insert([
            'creator_id' => $creator1, 'asset_id' => $asset3D, 'hours_requested' => 2, 'total_fc' => 20,
            'status' => 'completed', 'reservation_date' => $now->copy()->subDays(5)->format('Y-m-d'), 'created_at' => $now->copy()->subDays(5)->format('Y-m-d H:i:s')
        ]);
        DB::table('transactions')->insert(['user_id' => $creator1, 'description' => 'Servicio consumido (Orden #1)', 'amount' => 20, 'type' => 'expense', 'created_at' => $now->copy()->subDays(5)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Pago por reserva completada (Orden #1)', 'amount' => 20, 'type' => 'consumed', 'created_at' => $now->copy()->subDays(5)->format('Y-m-d H:i:s')]);

        // FLUJO 3: CRÉDITO LUIS Y COBRANZA DIRIGIDA (60 FC CONSUMIDOS)
        $creditoLuis = DB::table('financing_agreements')->insertGetId([
            'creator_id' => $creator2, 'lab_id' => $labA, 'amount_initial' => 100, 'amount_remaining' => 30,
            'description' => 'Financiamiento automático para Epilog Zing.', 'status' => 'active', 'created_at' => $now->copy()->subDays(4)->format('Y-m-d H:i:s')
        ]);
        // Orden de respaldo en el mercado de Luis para que no figure vacío
        DB::table('orders')->insert([
            'creator_id' => $creator2, 'asset_id' => $assetLaser, 'hours_requested' => 2, 'total_fc' => 100,
            'status' => 'completed', 'reservation_date' => $now->copy()->subDays(4)->format('Y-m-d'), 'created_at' => $now->copy()->subDays(4)->format('Y-m-d H:i:s')
        ]);
        
        $misionLuis = DB::table('missions')->insertGetId([
            'lab_id' => $labA, 'title' => 'Limpieza de Taller', 'description' => 'Mantenimiento general.',
            'reward_fc' => 60, 'target_creator_id' => $creator2, 'spots_total' => 1, 'spots_filled' => 1, 'status' => 'completed',
            'created_at' => $now->copy()->subDays(3)->format('Y-m-d H:i:s'), 'deadline' => $now->copy()->subDays(2)->format('Y-m-d H:i:s')
        ]);
        DB::table('mission_applications')->insert(['mission_id' => $misionLuis, 'creator_id' => $creator2, 'status' => 'accepted', 'is_reviewed' => true, 'created_at' => $now->copy()->subDays(3)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Reserva en Escrow (Misión Luis)', 'amount' => 60, 'type' => 'escrow', 'created_at' => $now->copy()->subDays(3)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Retorno de Crédito Fab (Misiones #'.$misionLuis.')', 'amount' => 60, 'type' => 'consumed', 'created_at' => $now->copy()->subDays(2)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Liberación (Misiones #'.$misionLuis.'): Amortizados 60 FC', 'amount' => 0, 'type' => 'info', 'created_at' => $now->copy()->subDays(2)->format('Y-m-d H:i:s')]);

        // FLUJO 4: TRANSFERENCIA P2P Y ABONO VOLUNTARIO (10 FC CONSUMIDOS)
        DB::table('transactions')->insert(['user_id' => $creator1, 'description' => 'Envío P2P a Luis Maker', 'amount' => 20, 'type' => 'expense', 'created_at' => $now->copy()->subHours(5)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $creator2, 'description' => 'Recibido P2P de Ana Código', 'amount' => 20, 'type' => 'income', 'created_at' => $now->copy()->subHours(5)->format('Y-m-d H:i:s')]);

        DB::table('transactions')->insert(['user_id' => $creator2, 'description' => 'Abono voluntario de Crédito al Laboratorio', 'amount' => 10, 'type' => 'expense', 'created_at' => $now->copy()->subHours(2)->format('Y-m-d H:i:s')]);
        DB::table('transactions')->insert(['user_id' => $labA, 'description' => 'Abono recibido de Luis Maker', 'amount' => 10, 'type' => 'consumed', 'created_at' => $now->copy()->subHours(2)->format('Y-m-d H:i:s')]);

        // NOTIFICACIONES VIVAS
        DB::table('notifications')->insert([
            ['user_id' => $creator1, 'message' => '¡Felicidades! La misión Diseño de Carcasa fue completada.', 'type' => 'success', 'is_read' => false, 'created_at' => $now->format('Y-m-d H:i:s')],
            ['user_id' => $creator2, 'message' => '💰 Ana Código te ha enviado 20 FC.', 'type' => 'info', 'is_read' => false, 'created_at' => $now->format('Y-m-d H:i:s')],
            ['user_id' => $labA, 'message' => 'Has recibido un abono voluntario de 10 FC de Luis Maker.', 'type' => 'success', 'is_read' => false, 'created_at' => $now->format('Y-m-d H:i:s')],
        ]);
    }
}