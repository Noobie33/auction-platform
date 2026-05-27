<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();

            // User who saved/watched the auction listing
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Auction listing saved by the user
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            $table->timestamps();

            // Prevents the same user from saving the same listing multiple times
            $table->unique(['user_id', 'listing_id']);

            $table->index('user_id');
            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};