<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'bill_number',
        'customer_id',
        'created_by',
        'bill_template_id',
        'subtotal',
        'discount',
        'total',
        'cash_received',
        'change_amount',
        'payment_method',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * The customer this bill was made for (optional - walk-in has none).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The cashier/admin who processed this sale.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Which bill template/design was used for this sale's receipt.
     */
    public function billTemplate(): BelongsTo
    {
        return $this->belongsTo(BillTemplate::class);
    }

    /**
     * A sale has many line items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}