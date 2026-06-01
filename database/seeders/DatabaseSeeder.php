<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DatabaseSeeder extends Seeder
{
    /**
     * Motor de Hidratación Contable y Adaptación de Ecosistema
     */
    public function run(): void
    {
        // 1. Desactivar restricciones de llaves foráneas para permitir la importación masiva cruda
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        // 2. Leer e inyectar el volcado SQL real de producción desde la raíz del proyecto
        $sqlPath = base_path('fabcoins.sql');
        if (file_exists($sqlPath)) {
            DB::unprepared(file_get_contents($sqlPath));
        }

        // 3. 🛡️ ADAPTACIÓN: Tabla 'users' (Evita errores 1054 de columnas faltantes en Auth/Breeze)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->string('remember_token', 100)->nullable();
                }
            });
        }

        // 4. 🛡️ ADAPTACIÓN: Tabla 'lab_assets' (Previene fallos de Eloquent Timestamps al emitir tokens)
        if (Schema::hasTable('lab_assets')) {
            Schema::table('lab_assets', function (Blueprint $table) {
                if (!Schema::hasColumn('lab_assets', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('lab_assets', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        // 5. ⚙️ SISTEMA: Creación de tablas técnicas internas requeridas por el núcleo de Laravel
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }

        // 6. Reactivar restricciones de seguridad de llaves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }
}