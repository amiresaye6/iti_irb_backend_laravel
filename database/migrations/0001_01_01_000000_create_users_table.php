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
            $table->enum('role', ['student','admin','sample_officer','reviewer','manager','super_admin'])->default('student');
            $table->string('full_name', 255);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->rememberToken();
            $table->string('national_id', 20);
            $table->string('phone_number', 20);
            $table->string('faculty', 150);
            $table->string('department', 150)->nullable();;
            $table->string('id_front_url', 255)->nullable();
            $table->string('id_back_url', 255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->string('token', 64)->unique();
            $table->dateTime('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('email');
            $table->index('expires_at');
        });
        
        // Laravel needs sessions for authentication driver
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
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('sessions');
    }
};
