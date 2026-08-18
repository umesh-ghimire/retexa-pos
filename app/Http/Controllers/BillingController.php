<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Customer;
use App\Models\HeldBill;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    /**
     * Show the billing/POS screen.
     */
 public function index()
{
    $template = BillTemplate::where('is_default', true)->first();

    $paymentQrPath = \App\Models\Setting::get('payment_qr_path');
    $paymentQrUrl = $paymentQrPath
        ? asset('storage/' . $paymentQrPath)
        : null;

    $shopLogoUrl = ($template && $template->show_logo && $template->logo_path)
        ? asset('storage/' . $template->logo_path)
        : null;

    $printerPaperWidthMm = (float) \App\Models\Setting::get(
        'printer_paper_width_mm',
        72
    );

        $printerVars = \App\Models\Setting::printerCssVars();


     return view('billing.index', [
        'shopName' => $template->shop_name ?? 'My Shop',
        'template' => $template,
        'defaultDiscount' => \App\Models\Setting::get('default_discount', 0),
        'paymentQrUrl' => $paymentQrUrl,
        'shopLogoUrl' => $shopLogoUrl,
        'printerVars' => $printerVars,
        'printerPaperWidthMm' => $printerPaperWidthMm,
        'isOwner' => auth()->user()->isOwner(),
    ]);
}

    /**
     * Save a completed sale: creates the bill, its line items,
     * deducts stock for any inventory-linked items, and returns
     * everything needed to render the printable receipt.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,qr,credit'],
            'show_qr' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated) {

                // Find or create the customer, if any details were given
                $customerId = null;
                if (! empty($validated['customer_phone'])) {
                    $customer = Customer::firstOrCreate(
                        ['phone' => $validated['customer_phone']],
                        ['name' => $validated['customer_name'] ?? null]
                    );
                    $customerId = $customer->id;
                } elseif (! empty($validated['customer_name'])) {
                    $customerId = Customer::create(['name' => $validated['customer_name']])->id;
                }

                $activeTemplate = BillTemplate::where('is_default', true)->first();

                // Create the sale with a temporary bill number (fixed right after)
                $sale = Sale::create([
                    'bill_number' => 'TEMP-' . uniqid(),
                    'customer_id' => $customerId,
                    'created_by' => Auth::id(),
                    'bill_template_id' => $activeTemplate->id ?? null,
                    'subtotal' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'cash_received' => $validated['cash_received'],
                    'change_amount' => 0,
                    'payment_method' => $validated['payment_method'],
                    'show_qr' => $validated['show_qr'] ?? null,
                ]);

                $subtotal = 0;

                foreach ($validated['items'] as $itemData) {
                    $product = null;
                    $unitPrice = (float) $itemData['price'];
                    $quantity = (float) $itemData['quantity'];

                    if (! empty($itemData['product_id'])) {
                        $product = Product::lockForUpdate()->find($itemData['product_id']);

                        // Trust the database price, not whatever the browser sent
                        $unitPrice = (float) $product->price;

                        if ($product->stock < $quantity) {
                            throw ValidationException::withMessages([
                                'items' => "Not enough stock for \"{$product->name}\". Available: {$product->stock}.",
                            ]);
                        }
                    }

                    $lineTotal = round($unitPrice * $quantity, 2);
                    $subtotal += $lineTotal;

                    $sale->items()->create([
                        'product_id' => $product?->id,
                        'unit_id' => $itemData['unit_id'] ?? null,
                        'item_name' => $itemData['name'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    if ($product) {
                        $product->adjustStock('out', $quantity, "Sold in bill #{$sale->id}", Auth::id());
                    }
                }

                $discount = min((float) ($validated['discount'] ?? 0), $subtotal);
                $total = max($subtotal - $discount, 0);
                $changeAmount = max($validated['cash_received'] - $total, 0);
                $dueAmount = max($total - $validated['cash_received'], 0);

                $sale->update([
                    'bill_number' => str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'change_amount' => $changeAmount,
                    'due_amount' => $dueAmount,
                ]);

                return $sale;
            });
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $sale->load(['items.product', 'items.unit', 'customer', 'createdBy']);

        return response()->json([
            'bill_number' => $sale->bill_number,
            'date' => $sale->created_at->format('Y-m-d'),
            'created_at' => $sale->created_at->toIso8601String(),
            'customer_name' => $sale->customer->name ?? null,
            'cashier_name' => optional($sale->createdBy)->name,
            'customer_phone' => $sale->customer->phone ?? null,
            'show_qr' => (bool) $sale->show_qr,
            'subtotal' => (float) $sale->subtotal,
            'discount' => (float) $sale->discount,
            'total' => (float) $sale->total,
            'cash_received' => (float) $sale->cash_received,
            'change_amount' => (float) $sale->change_amount,
            'due_amount' => (float) $sale->due_amount,
            'items' => $sale->items->map(fn ($item) => [
                'name' => $item->item_name,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'sku' => $item->product->sku ?? null,
                'unit' => $item->unit->short_code ?? null,
            ]),
        ]);
    }

    /**
     * Look up a single product by its exact barcode, for the
     * barcode scanner input on the billing screen.
     */
    public function lookupBarcode(Request $request)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
        ]);

        $product = Product::with('unit')
            ->where('barcode', $validated['barcode'])
            ->where('status', 'active')
            ->first();

        if (! $product) {
            return response()->json(['message' => 'No product found with this barcode.'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => (float) $product->stock,
            'unit_id' => $product->unit_id,
            'unit' => $product->unit->short_code ?? null,
        ]);
    }

    /**
     * Live product search for the billing screen's unified
     * search/scan box. Read-only, matches name or SKU.
     */
    public function searchProducts(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with('unit')
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'stock' => (float) $product->stock,
                    'unit_id' => $product->unit_id,
                    'unit' => $product->unit->short_code ?? null,
                ];
            });

        return response()->json($products);
    }

    /**
     * List the current cashier's held bills, newest first, for the
     * "Held Bills" panel on the billing screen.
     */
    public function heldBills()
    {
        $heldBills = HeldBill::where('held_by', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($heldBill) {
                $items = $heldBill->items ?? [];
                $itemCount = count($items);
                $subtotal = array_reduce($items, fn ($carry, $item) => $carry + (float) ($item['line_total'] ?? 0), 0);
                $total = max($subtotal - (float) $heldBill->discount, 0);

                return [
                    'id' => $heldBill->id,
                    'label' => $heldBill->label,
                    'customer_name' => $heldBill->customer_name,
                    'customer_phone' => $heldBill->customer_phone,
                    'item_count' => $itemCount,
                    'total' => $total,
                    'held_at' => $heldBill->created_at->toIso8601String(),
                ];
            });

        return response()->json($heldBills);
    }

    /**
     * Put the current in-progress bill on hold so the cashier can
     * start a fresh bill and come back to this one later.
     */
    public function holdBill(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.unit_label' => ['nullable', 'string'],
        ]);

        $countSoFar = HeldBill::where('held_by', Auth::id())->count();

        $heldBill = HeldBill::create([
            'held_by' => Auth::id(),
            'label' => 'Held Bill #' . ($countSoFar + 1),
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'discount' => $validated['discount'] ?? 0,
            'items' => $validated['items'],
        ]);

        return response()->json(['id' => $heldBill->id, 'label' => $heldBill->label]);
    }

    /**
     * Restore a held bill back onto the billing screen and remove it
     * from the held list.
     */
    public function restoreHeldBill(HeldBill $heldBill)
    {
        if ($heldBill->held_by !== Auth::id()) {
            abort(403);
        }

        $data = [
            'customer_name' => $heldBill->customer_name,
            'customer_phone' => $heldBill->customer_phone,
            'discount' => (float) $heldBill->discount,
            'items' => $heldBill->items,
        ];

        $heldBill->delete();

        return response()->json($data);
    }

    /**
     * Discard a held bill without restoring it.
     */
    public function destroyHeldBill(HeldBill $heldBill)
    {
        if ($heldBill->held_by !== Auth::id()) {
            abort(403);
        }

        $heldBill->delete();

        return response()->json(['success' => true]);
    }
}