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

    /**
     * Generate and assign a unique internal barcode for this product,
     * if it doesn't already have one. Never overwrites an existing
     * barcode (manufacturer or otherwise).
     */
    public function generateBarcode(): string
    {
        if ($this->barcode) {
            return $this->barcode;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () {
            // Lock the counter row so two products generated at the
            // exact same moment can never receive the same number.
            $counterValue = \App\Models\Setting::query()
                ->where('key', 'next_internal_barcode')
                ->lockForUpdate()
                ->value('value') ?? '200000000001';

            $barcode = $counterValue;

            // Defensive check: skip forward if this number is somehow
            // already in use (shouldn't normally happen, but protects
            // against manually-entered internal-range barcodes colliding).
            while (self::where('barcode', $barcode)->exists()) {
                $barcode = (string) ((int) $barcode + 1);
            }

            $this->update(['barcode' => $barcode]);

            \App\Models\Setting::set('next_internal_barcode', (string) ((int) $barcode + 1));

            return $barcode;
        });
    }

    /**
     * Helper: is this barcode one RETEXA generated internally,
     * as opposed to a real manufacturer/EAN/UPC code?
     */
    public function hasInternalBarcode(): bool
    {
        return $this->barcode && str_starts_with($this->barcode, '20') && strlen($this->barcode) === 12;
    }
}