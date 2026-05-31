<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financing_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('maker_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('active'); // active, completed, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financing_agreements');
    }
};