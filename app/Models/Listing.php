<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}