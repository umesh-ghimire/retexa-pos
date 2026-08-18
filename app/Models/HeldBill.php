<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldBill extends Model
{
    protected $fillable = [
        'held_by',
        'label',
        'customer_name',
        'customer_phone',
        'discount',
        'items',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'items' => 'array',
    ];

    /**
     * The cashier/admin who put this bill on hold.
     */
    public function heldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }
}