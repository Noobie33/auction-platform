<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // User who reported the listing
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Listing being reported
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            // Example: spam, fraud, counterfeit, inappropriate, wrong_category, other
            $table->string('reason', 100);

            // Extra explanation from reporter
            $table->text('description')->nullable();

            // pending = waiting, reviewed = seen, dismissed = no issue, actioned = admin took action
            $table->enum('status', ['pending', 'reviewed', 'dismissed', 'actioned'])
                ->default('pending');

            // Admin who reviewed/actioned this report
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('reporter_id');
            $table->index('listing_id');
            $table->index('status');
            $table->index(['reporter_id', 'listing_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};