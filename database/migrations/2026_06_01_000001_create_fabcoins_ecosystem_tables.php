<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Catálogo Global
        Schema::create('global_catalog', function (Blueprint $table) {
            $table->id();
            $table->enum('asset_type', ['machine', 'service', 'workshop', 'space', 'material'])->default('machine');
            $table->string('generic_name', 150);
            $table->string('generic_name_en', 255)->nullable();
            $table->string('measurement_unit', 50);
            $table->decimal('suggested_price_fc', 10, 2);
        });

        // 2. Configuraciones Globales
        Schema::create('global_settings', function (Blueprint $table) {
            $table->string('setting_key', 50)->primary();
            $table->string('setting_value', 255);
        });

        // 3. Catálogo de Habilidades
        Schema::create('skills_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->enum('type', ['hard', 'soft']);
        });

        // 4. Tablas Técnicas de Sistema (Caché y Seguridad)
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

        // 5. Activos de los Laboratorios
        Schema::create('lab_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('catalog_id')->constrained('global_catalog')->onDelete('cascade');
            $table->enum('asset_type', ['machine', 'service', 'workshop', 'space', 'material'])->default('machine');
            $table->string('custom_name', 150);
            $table->integer('useful_life_hours')->default(1000);
            $table->decimal('consumed_hours', 12, 2)->default(0.00);
            $table->integer('tokenization_pct')->default(30);
            $table->decimal('usd_value', 10, 2)->nullable();
            $table->integer('useful_life_units')->nullable();
            $table->decimal('wear_pct', 5, 2)->nullable();
            $table->decimal('allocation_pct', 5, 2)->nullable();
            $table->decimal('generated_fc', 12, 2)->default(0.00);
            $table->enum('status', ['active', 'retired'])->default('active');
            $table->decimal('set_price_fc', 10, 2);
            $table->timestamp('registered_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        // 6. Misiones de Trabajo
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description');
            $table->date('deadline');
            $table->string('reference_link', 255)->nullable();
            $table->decimal('reward_fc', 12, 2);
            $table->enum('status', ['open', 'assigned', 'completed', 'cancelled'])->default('open');
            $table->integer('assigned_maker_id')->nullable();
            $table->integer('target_maker_id')->nullable();
            $table->integer('spots_total')->default(1);
            $table->integer('spots_filled')->default(0);
            $table->timestamps();
        });

        // 7. Postulaciones de Makers
        Schema::create('mission_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('applied_at')->useCurrent();
            $table->tinyInteger('is_reviewed')->default(0);
            $table->timestamps();
        });

        // 8. Créditos de Financiamiento (ISA)
        Schema::create('financing_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount_initial', 10, 2);
            $table->decimal('amount_remaining', 10, 2);
            $table->string('description', 255)->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        // 9. Campanita de Notificaciones
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('message', 255);
            $table->string('type', 30)->default('info'); // Flexible string para evitar colisiones del enum original
            $table->tinyInteger('is_read')->default(0);
            $table->timestamps();
        });

        // 10. Libro de Órdenes de Alquiler de Máquinas
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('lab_assets')->onDelete('cascade');
            $table->decimal('hours_requested', 10, 2);
            $table->decimal('total_fc', 12, 2);
            $table->date('reservation_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->tinyInteger('is_reviewed')->default(0);
            $table->timestamps();
        });

        // 11. Reseñas y Estrellas
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->enum('context_type', ['mission', 'market'])->default('mission');
            $table->integer('context_id');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 12. Avales de Habilidades
        Schema::create('skill_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('skills_catalog')->onDelete('cascade');
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->integer('review_id')->nullable();
            $table->timestamps();
        });

        // 13. Libro Contable de Transacciones Globales
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('description', 255);
            $table->decimal('amount', 12, 2);
            $table->string('type', 30);
            $table->timestamps();
        });

        // 14. Relación Cruzada de Habilidades del Maker
        Schema::create('user_skills', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('skills_catalog')->onDelete('cascade');
            $table->primary(['user_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        // Eliminación en orden inverso para evitar colisiones
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('skill_endorsements');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('financing_agreements');
        Schema::dropIfExists('mission_applications');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('lab_assets');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('skills_catalog');
        Schema::dropIfExists('global_settings');
        Schema::dropIfExists('global_catalog');
    }
};