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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            
            $table->string('title', 255);
            $table->text('description');
            $table->date('deadline');
            $table->string('reference_link', 255)->nullable();
            $table->decimal('reward_fc', 12, 2);
            $table->enum('status', ['open', 'assigned', 'completed', 'cancelled'])->default('open');
            
            // Registramos si hay un Maker asignado o si es una misión dirigida por crédito
            $table->integer('assigned_maker_id')->nullable();
            $table->foreignId('target_maker_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->integer('spots_total')->default(1);
            $table->integer('spots_filled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
