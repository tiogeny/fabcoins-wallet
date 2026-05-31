<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('name', 100);
            $table->string('slug', 100)->unique()->nullable();
            $table->enum('role', ['superadmin', 'lab', 'maker']);
            $table->string('avatar_url', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('address', 255)->nullable();
            $table->tinyInteger('force_password_change')->default(1);
            $table->decimal('reputation_score', 3, 2)->default(0.00);
            $table->string('location', 100)->nullable();
            $table->string('profile_pic', 255)->default('default_avatar.png');
            $table->string('portfolio_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('user_tagline', 100)->nullable();
            $table->string('social_linkedin', 255)->nullable();
            $table->string('social_github', 255)->nullable();
            $table->string('social_portfolio', 255)->nullable();
            $table->string('social_instagram', 255)->nullable();
            $table->string('social_fabacademy', 255)->nullable();
            $table->decimal('deuda_inicial_fc', 10, 2)->default(0.00);
            $table->decimal('deuda_fc', 10, 2)->default(0.00);
            $table->unsignedBigInteger('deuda_lab_id')->nullable();
            $table->string('preferred_lang', 2)->default('es');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->tinyInteger('onboarding_completed')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};