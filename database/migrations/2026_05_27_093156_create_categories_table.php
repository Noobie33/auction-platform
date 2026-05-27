<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Category name shown to users
            $table->string('name');

            // URL-friendly unique name, example: electronics, used-laptops
            $table->string('slug')->unique();

            // Optional category details for SEO and admin explanation
            $table->text('description')->nullable();

            // Allows subcategories, example: Electronics > Mobile Phones
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Allows admin to hide a category without deleting old records
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('parent_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};