<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'business_name',
        'description',
        'business_photo',
        'valuation',
        'money_needed',
        'percentage_offered',
        'location',
        'employees_count',
        'founded_year',
        'business_model',
        'target_market',
        'competitive_advantages',
        'financial_highlights',
        'is_active'
    ];

    protected $casts = [
        'valuation' => 'decimal:2',
        'money_needed' => 'decimal:2',
        'percentage_offered' => 'decimal:2',
        'financial_highlights' => 'array',
        'is_active' => 'boolean',
        'founded_year' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByMoneyRange($query, $min, $max)
    {
        return $query->whereBetween('money_needed', [$min, $max]);
    }

    public function scopeByPercentageRange($query, $min, $max)
    {
        return $query->whereBetween('percentage_offered', [$min, $max]);
    }

    // Accessors
    public function getBusinessPhotoUrlAttribute()
    {
        return $this->business_photo ? asset('storage/' . $this->business_photo) : null;
    }

    public function getFormattedValuationAttribute()
    {
        return number_format($this->valuation, 2);
    }

    public function getFormattedMoneyNeededAttribute()
    {
        return number_format($this->money_needed, 2);
    }
}
