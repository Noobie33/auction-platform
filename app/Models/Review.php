<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'transaction_id',
        'reviewer_id',
        'reviewee_id',
        'reviewer_role',
        'rating',
        'comment',
        'reply',
        'replied_at',
        'is_visible',
    ];

    protected $casts = [
        'rating' => 'integer',
        'replied_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}