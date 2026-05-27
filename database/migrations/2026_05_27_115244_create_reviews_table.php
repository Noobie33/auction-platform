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

            // Review must belong to a completed transaction
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->restrictOnDelete();

            // User who writes the review
            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->restrictOnDelete();

            // User who receives the review
            $table->foreignId('reviewee_id')
                ->constrained('users')
                ->restrictOnDelete();

            // buyer or seller role during this transaction
            $table->enum('reviewer_role', ['buyer', 'seller']);

            // Rating should be validated in application logic from 1 to 5
            $table->unsignedTinyInteger('rating');

            // Optional review text
            $table->text('comment')->nullable();

            // Optional reply from the reviewed user
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();

            // Admin can hide inappropriate reviews without deleting them
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            // One user can review once per transaction
            $table->unique(['transaction_id', 'reviewer_id']);

            $table->index('transaction_id');
            $table->index('reviewer_id');
            $table->index('reviewee_id');
            $table->index('rating');
            $table->index('is_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};