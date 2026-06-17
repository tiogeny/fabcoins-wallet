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
        Schema::table('users', function (Blueprint $table) {
            $table->string('fab_academy_url')->nullable()->after('email');
            $table->string('instagram_url')->nullable()->after('fab_academy_url');
            $table->string('city')->nullable()->after('instagram_url');
            $table->string('country')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fab_academy_url', 'instagram_url', 'city', 'country']);
        });
    }
};
