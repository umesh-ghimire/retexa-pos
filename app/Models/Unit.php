<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'short_code',
        'status',
    ];

    /**
     * A unit can have many products.
     */

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * A unit can also be used directly on manual sale items.
     */

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
