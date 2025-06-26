<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'investor_id',
        'accepted_offer_id',
        'final_amount',
        'final_percentage',
        'deal_date',
        'status'
    ];

    protected $casts = [
        'final_amount' => 'decimal:2',
        'final_percentage' => 'decimal:2',
        'deal_date' => 'datetime',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function acceptedOffer()
    {
        return $this->belongsTo(Offer::class, 'accepted_offer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('deal_date', '>=', now()->subDays($days));
    }

    public function scopeHighestAmount($query)
    {
        return $query->orderBy('final_amount', 'desc');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->final_amount, 2);
    }
}
