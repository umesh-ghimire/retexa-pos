<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Show the inventory overview: stock levels + recent movement history.
     */
    public function index()
    {
        $products = Product::with(['category', 'unit'])
            ->orderBy('name')
            ->get();

        $recentMovements = StockMovement::with(['product', 'createdBy'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.inventory.index', compact('products', 'recentMovements'));
    }

    /**
     * Adjust a product's stock (restock, manual removal, or correction).
     */
    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out,set'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $success = $product->adjustStock(
            $validated['type'],
            $validated['quantity'],
            $validated['note'] ?? null,
            Auth::id()
        );

        if (! $success) {
            return back()->withErrors(['quantity' => 'This adjustment would make stock negative. Please check the amount.']);
        }

        return back()->with('success', 'Stock updated successfully.');
    }
}