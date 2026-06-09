<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 255)->nullable();
            $table->enum('type',['submission','assignment','status_change','certificate','decision','auth','modify_application','payment','other']);
            $table->timestamp('created_at')->useCurrent();

            $table->index('application_id');
            $table->index('user_id');
        });
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
