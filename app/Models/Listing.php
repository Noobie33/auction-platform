<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Listing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'slug',
        'description',
        'condition',
        'location',
        'starting_price',
        'reserve_price',
        'current_bid',
        'min_bid_increment',
        'buy_now_price',
        'status',
        'starts_at',
        'ends_at',
        'auto_extend',
        'is_featured',
        'view_count',
        'winner_id',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'current_bid' => 'decimal:2',
        'min_bid_increment' => 'decimal:2',
        'buy_now_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_extend' => 'boolean',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class);
    }
    
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function autoBids(): HasMany
    {
        return $this->hasMany(AutoBid::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Limit query to listings belonging to a specific seller.
     *
     * Used by every seller-facing listing query: index, edit, publish, cancel.
     * Never query the listings table on seller routes without this scope.
     */
    public function scopeForSeller(Builder $query, int $sellerId): Builder
    {
        return $query->where('seller_id', $sellerId);
    }

    // ── Ownership & Status Helpers ─────────────────────────────────────────────

    /**
     * Returns true if this listing belongs to the given user.
     *
     * Called at the top of every mutating controller method to prevent
     * seller A from editing, publishing, or cancelling seller B's listings.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->seller_id === $user->id;
    }

    /**
     * Returns true if the listing is in draft status.
     *
     * Only draft listings may be edited. edit() and update() call
     * abort_if(!$listing->isDraft(), 403) after the ownership check.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Returns true if the listing is currently published.
     *
     * Used by the index view to conditionally show the Cancel button
     * and by publish() to guard against double-publishing.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Returns true if the listing can be cancelled.
     *
     * Both draft and active listings are technically cancellable at the
     * model level. The controller cancel() method may add a UX nudge
     * suggesting deletion for drafts, but that preference is not encoded here.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['draft', 'active'], true);
    }

    // ── Slug Generation ────────────────────────────────────────────────────────

    /**
     * Generate a unique slug from a listing title.
     *
     * - Converts the title to a URL-safe base slug via Str::slug().
     * - Checks active and soft-deleted listings via withTrashed() because the
     *   database unique index still prevents duplicate slugs even when a listing
     *   is soft-deleted.
     * - Appends -2, -3, etc. until a free slug is found.
     * - Excludes $excludeId so that updating with an unchanged title preserves
     *   the existing slug without incorrectly generating a -2 suffix.
     *
     * @param  string   $title      The listing title to slugify.
     * @param  int|null $excludeId  ID of the listing being updated, or null on create.
     */
    public static function generateSlug(string $title, ?int $excludeId = null): string
    {
        $base    = Str::slug($title);
        $slug    = $base;
        $counter = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($excludeId, fn (Builder $q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}