<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'unit_id',
        'name',
        'sku',
        'barcode',
        'price',
        'cost_price',
        'stock',
        'min_stock_level',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
    ];

    /**
     * A product belongs to one category (optional).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product belongs to one unit (optional).
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * A product can appear in many sale items.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * A product can have many stock movement records (its history).
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    /**
     * Helper: is this product's stock at or below its minimum level?
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock_level;
    }

    /**
     * Safely change this product's stock and log the change.
     *
     * @param  string  $type  'in' (add), 'out' (remove), or 'set' (correction to exact value)
     */
    public function adjustStock(string $type, float $quantity, ?string $note, ?int $userId): bool
    {
        $stockBefore = (float) $this->stock;

        $stockAfter = match ($type) {
            'in' => $stockBefore + $quantity,
            'out' => $stockBefore - $quantity,
            'set' => $quantity,
        };

        // Never allow stock to go negative
        if ($stockAfter < 0) {
            return false;
        }

        $this->stockMovements()->create([
            'created_by' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'note' => $note,
        ]);

        $this->update(['stock' => $stockAfter]);

        return true;
    }
}