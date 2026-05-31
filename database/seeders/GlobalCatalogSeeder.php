<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GlobalCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 SENTENCIA INFAVIBLE: Forzamos a MySQL a apagar el chequeo de llaves de golpe
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        // Ahora sí limpiará la tabla sin importar qué dependencias existan
        DB::table('global_catalog')->truncate();

        // 🔥 Volvemos a encender la seguridad inmediatamente
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $items = [
            // --- ⚙️ MÁQUINAS / EQUIPOS ---
            ['asset_type' => 'machine', 'generic_name' => 'Impresora 3D FDM (Filamento)', 'generic_name_en' => '3D Printer FDM (Filament)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 5.00],
            ['asset_type' => 'machine', 'generic_name' => 'Impresora 3D SLA (Resina)', 'generic_name_en' => '3D Printer SLA (Resin)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 8.00],
            ['asset_type' => 'machine', 'generic_name' => 'Cortadora Láser (CO2)', 'generic_name_en' => 'Laser Cutter (CO2)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 20.00],
            ['asset_type' => 'machine', 'generic_name' => 'Router (Formato Grande)', 'generic_name_en' => 'CNC Router (Large Format)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 35.00],
            ['asset_type' => 'machine', 'generic_name' => 'Plotter de Corte (Vinilo)', 'generic_name_en' => 'Vinyl Cutter', 'measurement_unit' => 'hora', 'suggested_price_fc' => 10.00],
            ['asset_type' => 'machine', 'generic_name' => 'Fresadora de Precisión (PCBs)', 'generic_name_en' => 'Precision Milling (PCBs)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 15.00],
            ['asset_type' => 'machine', 'generic_name' => 'Escáner 3D', 'generic_name_en' => '3D Scanner', 'measurement_unit' => 'hora', 'suggested_price_fc' => 12.00],
            ['asset_type' => 'machine', 'generic_name' => 'Otro Equipo / Maquinaria (Comodín)', 'generic_name_en' => 'Other Equipment / Machinery', 'measurement_unit' => 'hora', 'suggested_price_fc' => 10.00],

            // --- 🧠 SERVICIOS ESPECIALIZADOS ---
            ['asset_type' => 'service', 'generic_name' => 'Consultoría en Modelado 3D (CAD)', 'generic_name_en' => '3D Modeling Consultancy (CAD)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 25.00],
            ['asset_type' => 'service', 'generic_name' => 'Asesoría en Electrónica / Programación', 'generic_name_en' => 'Electronics / Programming Advisory', 'measurement_unit' => 'hora', 'suggested_price_fc' => 30.00],
            ['asset_type' => 'service', 'generic_name' => 'Diseño de Placas Electrónicas (PCB)', 'generic_name_en' => 'PCB Design', 'measurement_unit' => 'hora', 'suggested_price_fc' => 35.00],
            ['asset_type' => 'service', 'generic_name' => 'Acompañamiento en Prototipado', 'generic_name_en' => 'Prototyping Support', 'measurement_unit' => 'hora', 'suggested_price_fc' => 20.00],
            ['asset_type' => 'service', 'generic_name' => 'Operación de Máquina Asistida', 'generic_name_en' => 'Assisted Machine Operation', 'measurement_unit' => 'hora', 'suggested_price_fc' => 15.00],
            ['asset_type' => 'service', 'generic_name' => 'Otro Servicio Profesional (Comodín)', 'generic_name_en' => 'Other Professional Service', 'measurement_unit' => 'hora', 'suggested_price_fc' => 25.00],

            // --- 🎓 TALLERES / CURSOS ---
            ['asset_type' => 'workshop', 'generic_name' => 'Inducción de Seguridad Básica', 'generic_name_en' => 'Basic Safety Induction', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 15.00],
            ['asset_type' => 'workshop', 'generic_name' => 'Taller Práctico: Impresión 3D', 'generic_name_en' => 'Practical Workshop: 3D Printing', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 40.00],
            ['asset_type' => 'workshop', 'generic_name' => 'Taller Práctico: Corte Láser', 'generic_name_en' => 'Practical Workshop: Laser Cutting', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 50.00],
            ['asset_type' => 'workshop', 'generic_name' => 'Bootcamp Fabricación Digital', 'generic_name_en' => 'Digital Fabrication Bootcamp', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 150.00],
            ['asset_type' => 'workshop', 'generic_name' => 'Curso: Programación con Arduino', 'generic_name_en' => 'Course: Arduino Programming', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 80.00],
            ['asset_type' => 'workshop', 'generic_name' => 'Otro Taller / Evento (Comodín)', 'generic_name_en' => 'Other Workshop / Event', 'measurement_unit' => 'cupo', 'suggested_price_fc' => 50.00],

            // --- 🏢 ESPACIOS FÍSICOS ---
            ['asset_type' => 'space', 'generic_name' => 'Estación de Trabajo (Coworking Fab)', 'generic_name_en' => 'Workstation (Fab Coworking)', 'measurement_unit' => 'hora', 'suggested_price_fc' => 3.00],
            ['asset_type' => 'space', 'generic_name' => 'Mesa de Ensamblaje / Herramientas', 'generic_name_en' => 'Assembly Table / Tools', 'measurement_unit' => 'hora', 'suggested_price_fc' => 5.00],
            ['asset_type' => 'space', 'generic_name' => 'Cabina de Pintura / Acabados', 'generic_name_en' => 'Paint Booth / Finishing', 'measurement_unit' => 'hora', 'suggested_price_fc' => 8.00],
            ['asset_type' => 'space', 'generic_name' => 'Sala de Reuniones / Ideación', 'generic_name_en' => 'Meeting / Ideation Room', 'measurement_unit' => 'hora', 'suggested_price_fc' => 10.00],
            ['asset_type' => 'space', 'generic_name' => 'Sala de Capacitación / Aulas', 'generic_name_en' => 'Training Room / Classrooms', 'measurement_unit' => 'hora', 'suggested_price_fc' => 15.00],
            ['asset_type' => 'space', 'generic_name' => 'Otro Espacio Físico (Comodín)', 'generic_name_en' => 'Other Space', 'measurement_unit' => 'hora', 'suggested_price_fc' => 5.00],
        ];

        DB::table('global_catalog')->insert($items);
    }
}