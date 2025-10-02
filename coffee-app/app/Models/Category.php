<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /**
     * Allow mass-assignment for API create/update.
     */
    protected $fillable = [
        'cat_name',
        'cat_description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
