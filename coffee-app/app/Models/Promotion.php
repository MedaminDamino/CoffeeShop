<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $fillable = [
        'code_promo',
        'promo_description',
        'promo_discount_type',
        'promo_discount_value',
        'promo_start_date',
        'promo_end_date',
        'promo_usage_limit',
        'promo_is_active',
    ];

    protected $casts = [
        'promo_discount_value' => 'decimal:2',
        'promo_start_date' => 'date',
        'promo_end_date' => 'date',
        'promo_usage_limit' => 'integer',
        'promo_is_active' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }
}
