<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('global_catalog')->truncate();
        DB::table('skills_catalog')->truncate();
        DB::table('global_settings')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        // 1. Sembrar Configuración Monetaria
        DB::table('global_settings')->insert(['setting_key' => 'tokenization_pct', 'setting_value' => '35']);

        // 2. Sembrar Catálogo Técnico de Máquinas y Servicios Original
        DB::table('global_catalog')->insert([
            ['id' => 1, 'asset_type' => 'machine', 'generic_name' => 'Impresora 3D FDM (Filamento)', 'generic_name_en' => '3D Printer FDM (Filament)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 5.00],
            ['id' => 2, 'asset_type' => 'machine', 'generic_name' => 'Impresora 3D SLA (Resina)', 'generic_name_en' => '3D Printer SLA (Resin)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 8.00],
            ['id' => 3, 'asset_type' => 'machine', 'generic_name' => 'Cortadora Láser (CO2)', 'generic_name_en' => 'Laser Cutter (CO2)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 20.00],
            ['id' => 4, 'asset_type' => 'machine', 'generic_name' => 'Router (Formato Grande)', 'generic_name_en' => 'CNC Router (Large Format)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 35.00],
            ['id' => 5, 'asset_type' => 'machine', 'generic_name' => 'Plotter de Corte (Vinilo)', 'generic_name_en' => 'Vinyl Cutter', 'measurement_unit' => 'hora', 'suggested_price_fc' => 10.00],
            ['id' => 6, 'asset_type' => 'machine', 'generic_name' => 'Fresadora de Precisión (PCBs)', 'generic_name_en' => 'Precision Milling (PCBs)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 15.00],
            ['id' => 7, 'asset_type' => 'machine', 'generic_name' => 'Escáner 3D', 'generic_name_en' => '3D Scanner', 'measurement_unit' => 'hora', 'suggested_price_fc' => 12.00],
            ['id' => 8, 'asset_type' => 'machine', 'generic_name' => 'Otro Equipo / Maquinaria (Comodín)', 'generic_name_en' => 'Other Equipment / Machinery', 'measurement_unit' => 'hora', 'suggested_price_fc' => 10.00],
            ['id' => 9, 'asset_type' => 'service', 'generic_name' => 'Consultoría en Modelado 3D (CAD)', 'generic_name_en' => '3D Modeling Consultancy (CAD)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 25.00],
            ['id' => 10, 'asset_type' => 'service', 'generic_name' => 'Asesoría en Electrónica / Programación', 'generic_name_en' => 'Electronics / Programming Advisory', 'measurement_unit' => 'hora', 'suggested_price_fc' => 30.00],
            ['id' => 11, 'asset_type' => 'service', 'generic_name' => 'Diseño de Placas Electrónicas (PCB)', 'generic_name_en' => 'PCB Design', 'measurement_unit' => 'hora', 'suggested_price_fc' => 35.00],
            ['id' => 12, 'asset_type' => 'service', 'generic_name' => 'Acompañamiento en Prototipado', 'generic_name_en' => 'Prototyping Support', 'measurement_unit' => 'hora', 'suggested_price_fc' => 20.00],
            ['id' => 13, 'asset_type' => 'service', 'generic_name' => 'Operación de Máquina Asistida', 'generic_name_en' => 'Assisted Machine Operation', 'measurement_unit' => 'hora', 'suggested_price_fc' => 15.00],
            ['id' => 14, 'asset_type' => 'service', 'generic_name' => 'Otro Servicio Profesional (Comodín)', 'generic_name_en' => 'Other Professional Service', 'measurement_unit' => 'hora', 'suggested_price_fc' => 25.00],
            ['id' => 15, 'asset_type' => 'workshop', 'generic_name' => 'Inducción de Seguridad Básica', 'generic_name_en' => 'Basic Safety Induction', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 15.00],
            ['id' => 16, 'asset_type' => 'workshop', 'generic_name' => 'Taller Práctico: Impresión 3D', 'generic_name_en' => 'Practical Workshop: 3D Printing', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 40.00],
            ['id' => 17, 'asset_type' => 'workshop', 'generic_name' => 'Taller Práctico: Corte Láser', 'generic_name_en' => 'Practical Workshop: Laser Cutting', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 50.00],
            ['id' => 18, 'asset_type' => 'workshop', 'generic_name' => 'Bootcamp Fabricación Digital', 'generic_name_en' => 'Digital Fabrication Bootcamp', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 150.00],
            ['id' => 19, 'asset_type' => 'workshop', 'generic_name' => 'Curso: Programación con Arduino', 'generic_name_en' => 'Course: Arduino Programming', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 80.00],
            ['id' => 20, 'asset_type' => 'workshop', 'generic_name' => 'Otro Taller / Evento (Comodín)', 'generic_name_en' => 'Other Workshop / Event', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 50.00],
            ['id' => 21, 'asset_type' => 'space', 'generic_name' => 'Estación de Trabajo (Coworking Fab)', 'generic_name_en' => 'Workstation (Fab Coworking)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 3.00],
            ['id' => 22, 'asset_type' => 'space', 'generic_name' => 'Mesa de Ensamblaje / Herramientas', 'generic_name_en' => 'Assembly Table / Tools', 'measurement_unit' => 'hora', 'suggested_price_fc' => 5.00],
            ['id' => 23, 'asset_type' => 'space', 'generic_name' => 'Cabina de Pintura / Acabados', 'generic_name_en' => 'Paint Booth / Finishing', 'measurement_unit' => 'hora', 'suggested_price_fc' => 8.00],
            ['id' => 24, 'asset_type' => 'space', 'generic_name' => 'Sala de Reuniones / Ideación', 'generic_name_en' => 'Meeting / Ideation Room', 'measurement_unit' => 'hora', 'suggested_price_fc' => 10.00],
            ['id' => 25, 'asset_type' => 'space', 'generic_name' => 'Sala de Capacitación / Aulas', 'generic_name_en' => 'Training Room / Classrooms', 'measurement_unit' => 'hora', 'suggested_price_fc' => 15.00],
            ['id' => 26, 'asset_type' => 'space', 'generic_name' => 'Otro Espacio Físico (Comodín)', 'generic_name_en' => 'Other Space', 'measurement_unit' => 'hora', 'suggested_price_fc' => 5.00]
        ]);

        // 3. Sembrar Catálogo de Habilidades de Reputación
        DB::table('skills_catalog')->insert([
            ['id' => 1, 'name' => 'Impresión 3D (FDM)', 'type' => 'hard'],
            ['id' => 2, 'name' => 'Impresión 3D (Resina)', 'type' => 'hard'],
            ['id' => 3, 'name' => 'Corte Láser', 'type' => 'hard'],
            ['id' => 4, 'name' => 'Mecanizado CNC', 'type' => 'hard'],
            ['id' => 5, 'name' => 'Diseño CAD / Fusión 360', 'type' => 'hard'],
            ['id' => 6, 'name' => 'Electrónica / Arduino', 'type' => 'hard'],
            ['id' => 7, 'name' => 'Soldadura SMD', 'type' => 'hard'],
            ['id' => 8, 'name' => 'Programación Python', 'type' => 'hard'],
            ['id' => 9, 'name' => 'Puntualidad Extrema', 'type' => 'soft'],
            ['id' => 10, 'name' => 'Resolución de Problemas', 'type' => 'soft'],
            ['id' => 11, 'name' => 'Comunicación Clara', 'type' => 'soft'],
            ['id' => 12, 'name' => 'Trabajo en Equipo', 'type' => 'soft'],
            ['id' => 13, 'name' => 'Cuidado de Maquinaria', 'type' => 'soft']
        ]);

        // 4. 🔥 CREACIÓN SOLICITADA: Tu usuario de pruebas listo para operar
        User::create([
            'name' => 'tinkiLab',
            'email' => 'hola@tinkilab.com',
            'password' => Hash::make('admin123'),
            'role' => 'lab',
            'slug' => 'tinkilab',
            'force_password_change' => 0,
            'onboarding_completed' => 1,
            'preferred_lang' => 'es'
        ]);
    }
}