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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('lab_assets')->onDelete('cascade');
            
            $table->decimal('hours_requested', 10, 2);
            $table->decimal('total_fc', 12, 2);
            $table->date('reservation_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
