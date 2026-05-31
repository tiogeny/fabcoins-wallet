<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lab_assets', function (Blueprint $table) {
            $table->id();
            
            // Claves foráneas: se conecta con usuarios (el lab dueño) y con el catálogo global
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
            $table->date('expires_at')->nullable();
            $table->timestamps(); // Controla la fecha de registro automáticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_assets');
    }
};
