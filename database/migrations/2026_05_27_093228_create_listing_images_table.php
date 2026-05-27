<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();

            // Auction listing that owns this image
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            // Image file path stored in local/public storage or cloud storage
            $table->string('image_path');

            // Optional alt text for accessibility and SEO
            $table->string('alt_text')->nullable();

            // Main/cover image for the listing
            $table->boolean('is_primary')->default(false);

            // Controls image display order
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('listing_id');
            $table->index('is_primary');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};