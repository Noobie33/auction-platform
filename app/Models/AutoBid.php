<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoBid extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'max_amount',
        'current_auto_bid_amount',
        'is_active',
        'last_triggered_at',
        'note',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'current_auto_bid_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}