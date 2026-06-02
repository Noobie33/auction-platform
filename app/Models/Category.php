<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Limit query to active categories only.
     * Used by: index view display, parent dropdown in create/edit forms.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Limit query to root-level categories (no parent).
     * Used by: index view grouping, parent dropdown (only roots are selectable parents).
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // ── Safe-Delete Guard ──────────────────────────────────────────────────────

    /**
     * Returns true if any listing references this category.
     *
     * withTrashed() is mandatory: soft-deleted listings still hold the
     * category_id foreign key. The DB uses restrictOnDelete(), so even a
     * trashed listing will cause a FK violation on hard delete.
     */
    public function hasListings(): bool
    {
        return $this->listings()->withTrashed()->exists();
    }

    // ── Slug Generation ────────────────────────────────────────────────────────

    /**
     * Generate a unique slug from a category name.
     *
     * - Converts name to URL-safe base slug via Str::slug().
     * - Checks the categories table for uniqueness.
     * - Appends -2, -3, etc. until a free slug is found.
     * - Excludes $excludeId from the uniqueness check (required during update
     *   so the category's own current slug does not block itself).
     *
     * @param  string   $name       The category name to slugify.
     * @param  int|null $excludeId  The ID of the category being updated, or null on create.
     */
    public static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $base    = Str::slug($name);
        $slug    = $base;
        $counter = 2;

        while (
            static::where('slug', $slug)
                ->when($excludeId, fn (Builder $q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}