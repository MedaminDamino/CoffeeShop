<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * Allow mass-assignment for API create/update.
     */
    protected $fillable = [
        'category_id',
        'prod_name',
        'prod_description',
        'prod_price',
        'prod_image_url',
        'prod_is_active',
        'prod_meta',
    ];

    protected $casts = [
        'prod_price' => 'decimal:2',
        'prod_is_active' => 'boolean',
        'prod_meta' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
