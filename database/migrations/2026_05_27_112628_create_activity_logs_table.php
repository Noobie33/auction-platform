<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // User who performed the action; null means system action
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Example: user.login, bid.placed, listing.created, report.actioned
            $table->string('action', 100);

            // Flexible reference to any related record/model
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Extra details, example: old/new status, bid amount, reason
            $table->json('properties')->nullable();

            // Security/audit data
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 512)->nullable();

            // Logs should be immutable, so created_at only
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('action');
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};