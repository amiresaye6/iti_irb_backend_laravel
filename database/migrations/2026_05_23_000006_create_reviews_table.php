<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users');
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->useCurrent();
            $table->enum('assignment_status', [
                'awaiting_acceptance', 'accepted', 'refused', 'timed_out'
            ])->default('awaiting_acceptance');
            $table->enum('decision', [
                'pending', 'approved', 'needs_modification', 'rejected'
            ])->default('pending');
            $table->string('review_document')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
