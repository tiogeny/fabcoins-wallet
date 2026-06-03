<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🛠️ TABLAS TÉCNICAS DEL SISTEMA
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

        // 1. Catálogo Global (Alimentado por el Superadmin)
        Schema::create('global_catalog', function (Blueprint $table) {
            $table->id();
            $table->enum('asset_type', ['machine', 'service', 'lab'])->default('machine');
            $table->string('generic_name', 150);
            $table->string('generic_name_en', 255)->nullable();
            $table->string('measurement_unit', 50)->default('hours');
            $table->decimal('suggested_price_fc', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Activos de los Laboratorios (Workspace 1 y 2)
        Schema::create('lab_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('catalog_id')->constrained('global_catalog')->onDelete('cascade');
            $table->enum('asset_type', ['machine', 'service', 'lab'])->default('machine');
            $table->string('subcategory', 50)->nullable(); 
            $table->string('custom_name', 150);
            $table->decimal('useful_life_hours', 10, 2)->default(1000.00);
            $table->decimal('consumed_hours', 10, 2)->default(0.00);
            $table->integer('tokenization_pct')->default(0); 
            $table->decimal('generated_fc', 12, 2)->default(0.00);
            $table->string('status', 30)->default('enlisted'); 
            $table->decimal('set_price_fc', 10, 2)->default(0.00);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        // 3. Misiones de Trabajo (Workspace 3)
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description');
            $table->date('deadline');
            $table->string('reference_link', 255)->nullable();
            $table->decimal('reward_fc', 12, 2);
            $table->string('status', 30)->default('open'); 
            $table->integer('assigned_maker_id')->nullable();
            $table->integer('target_maker_id')->nullable();
            $table->integer('spots_total')->default(1);
            $table->integer('spots_filled')->default(0);
            $table->timestamps();
        });

        // 4. 🔥 REINTEGRACIÓN: Postulaciones de Makers (Requerido por el Contador del Ledger)
        Schema::create('mission_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending'); // pending, accepted, rejected
            $table->tinyInteger('is_reviewed')->default(0);
            $table->timestamps();
        });

        // 5. Campanita de Notificaciones
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('message', 255);
            $table->string('type', 30)->default('info');
            $table->tinyInteger('is_read')->default(0);
            $table->timestamps();
        });

        // 6. Libro Contable de Transacciones Globales (Beno's Ledger)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('description', 255);
            $table->decimal('amount', 12, 2);
            $table->string('type', 30); 
            $table->timestamps();
        });

        // 7. Libro de Órdenes (Alquileres del Mercado)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('lab_assets')->onDelete('cascade');
            $table->decimal('hours_requested', 10, 2);
            $table->decimal('total_fc', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        // 8. Créditos de Financiamiento (ISA)
        Schema::create('financing_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount_initial', 10, 2);
            $table->decimal('amount_remaining', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        // 9. 🔥 REINTEGRACIÓN: Reseñas y Estrellas (Requerido por el Explorador de Makers)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->string('context_type', 30)->default('mission');
            $table->integer('context_id');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('financing_agreements');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mission_applications');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('lab_assets');
        Schema::dropIfExists('global_catalog');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};