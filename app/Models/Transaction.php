<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'listing_id',
        'bid_id',
        'buyer_id',
        'seller_id',
        'amount',
        'commission_rate',
        'commission_amount',
        'seller_payout',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_deadline',
        'paid_at',
        'shipping_status',
        'shipping_carrier',
        'tracking_number',
        'shipping_address',
        'shipped_at',
        'delivered_at',
        'buyer_confirmed_receipt',
        'receipt_confirmed_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'refunded_at',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'seller_payout' => 'decimal:2',
        'payment_deadline' => 'datetime',
        'paid_at' => 'datetime',
        'shipping_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'buyer_confirmed_receipt' => 'boolean',
        'receipt_confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}