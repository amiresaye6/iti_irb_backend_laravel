<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('serial_number', 50)->unique()->nullable();
            $table->text('title')->nullable();
            $table->string('principal_investigator', 255)->nullable();
            $table->text('co_investigators')->nullable();
            $table->text('keywords')->nullable();
            $table->enum('current_stage', [
                'pending_admin','under_review', 'approved_by_reviewer', 'awaiting_payment', 'approved', 'rejected'
            ])->default('pending_admin');
            $table->boolean('is_blinded')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('student_id');
            $table->index('current_stage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
