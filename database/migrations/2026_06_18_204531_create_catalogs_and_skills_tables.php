<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Catálogo Global de Máquinas
        if (!Schema::hasTable('global_catalog')) {
            Schema::create('global_catalog', function (Blueprint $table) {
                $table->id();
                $table->string('generic_name');
                $table->string('category')->default('machines');
                $table->timestamps();
            });
        }

        // 2. Catálogo de Habilidades
        if (!Schema::hasTable('skills_catalog')) {
            Schema::create('skills_catalog', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('hard'); // hard o soft
                $table->timestamps();
            });
        }

        // 3. Tabla Pivote: Habilidades del Usuario (Creador)
        if (!Schema::hasTable('user_skills')) {
            Schema::create('user_skills', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('skill_id')->constrained('skills_catalog')->onDelete('cascade');
                $table->timestamps();
            });
        }
        
        // 4. Activos del Laboratorio (Si por alguna razón faltaba)
        if (!Schema::hasTable('lab_assets')) {
            Schema::create('lab_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('catalog_id')->constrained('global_catalog')->onDelete('cascade');
                $table->string('custom_name');
                $table->decimal('set_price_fc', 10, 2);
                $table->string('status')->default('active');
                $table->integer('useful_life_hours')->default(1000);
                $table->integer('consumed_hours')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('lab_assets');
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('skills_catalog');
        Schema::dropIfExists('global_catalog');
    }
};