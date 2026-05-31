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
        Schema::create('global_catalog', function (Blueprint $table) {
            $table->id(); // Crea automáticamente un INT AUTO_INCREMENT llamado 'id'
            $table->enum('asset_type', ['machine', 'service', 'workshop', 'space', 'material'])->default('machine');
            $table->string('generic_name', 150);
            $table->string('generic_name_en', 255)->nullable(); // nullable significa DEFAULT NULL
            $table->string('measurement_unit', 50);
            $table->decimal('suggested_price_fc', 10, 2);
            $table->timestamps(); // Esto creará 'created_at' y 'updated_at' automáticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_catalogs');
    }
};
