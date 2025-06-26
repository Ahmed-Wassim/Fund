<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'investor_id',
        'offered_amount',
        'requested_percentage',
        'message',
        'status',
        'parent_offer_id',
        'expires_at'
    ];

    protected $casts = [
        'offered_amount' => 'decimal:2',
        'requested_percentage' => 'decimal:2',
        'expires_at' => 'datetime',
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

    public function parentOffer()
    {
        return $this->belongsTo(Offer::class, 'parent_offer_id');
    }

    public function childOffers()
    {
        return $this->hasMany(Offer::class, 'parent_offer_id');
    }

    public function deal()
    {
        return $this->hasOne(Deal::class, 'accepted_offer_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeFromInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    // Methods
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canBeAccepted()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }
}
