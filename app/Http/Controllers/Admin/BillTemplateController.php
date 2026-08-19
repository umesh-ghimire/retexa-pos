<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillTemplate;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BillTemplateController extends Controller
{
    /**
     * Show the list of all bill designs.
     */
    public function index()
    {
        $templates = BillTemplate::orderByDesc('is_default')->orderBy('name')->get();
        $latestSale = \App\Models\Sale::with(['items.product', 'items.unit', 'customer', 'createdBy'])->latest()->first();
        $printerVars = \App\Models\Setting::printerCssVars();

        return view('admin.bill-templates.index', compact('templates', 'latestSale', 'printerVars'));
    }

    /**
     * Save a new bill design.
     */
    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('bill-logos', 'public');
        }

        BillTemplate::create($validated);

        return back()->with('success', 'Bill design created successfully.');
    }

    /**
     * Update an existing bill design.
     */
   public function update(Request $request, BillTemplate $billTemplate)
    {
        $validated = $this->validateTemplate($request, $billTemplate->id);

        if ($request->hasFile('logo')) {
            if ($billTemplate->logo_path) {
                Storage::disk('public')->delete($billTemplate->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('bill-logos', 'public');
        }

        $billTemplate->update($validated);

        return back()->with('success', 'Bill design updated successfully.');
    }

    /**
     * Create a copy of an existing design (including its logo file, if any).
     */
    public function duplicate(BillTemplate $billTemplate)
    {
        $copy = $billTemplate->replicate();
        $copy->name = $billTemplate->name . ' (Copy)';
        $copy->is_default = false;

        if ($billTemplate->logo_path) {
            $newPath = 'bill-logos/' . uniqid() . '_' . basename($billTemplate->logo_path);
            Storage::disk('public')->copy($billTemplate->logo_path, $newPath);
            $copy->logo_path = $newPath;
        }

        $copy->save();

        return back()->with('success', "Duplicated as \"{$copy->name}\".");
    }

    /**
     * Mark one template as the active/default design.
     */
    public function setDefault(BillTemplate $billTemplate)
    {
        DB::transaction(function () use ($billTemplate) {
            BillTemplate::where('is_default', true)->update(['is_default' => false]);
            $billTemplate->update(['is_default' => true]);
        });

        return back()->with('success', "\"{$billTemplate->name}\" is now the active bill design.");
    }

    /**
     * Delete a bill design. The active design cannot be deleted.
     */
    public function destroy(BillTemplate $billTemplate)
    {
        if ($billTemplate->is_default) {
            return back()->withErrors([
                'template' => 'You cannot delete the active design. Set another design as default first.',
            ]);
        }

        if ($billTemplate->logo_path) {
            Storage::disk('public')->delete($billTemplate->logo_path);
        }

        $billTemplate->delete();

        return back()->with('success', 'Bill design deleted successfully.');
    }

    /**
     * Shared validation rules for store() and update().
     * Boolean toggle fields are handled separately since unchecked
     * checkboxes are simply not sent by the browser at all.
     */
    private function validateTemplate(Request $request, ?int $templateId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:bill_templates,name,' . $templateId],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'header_text' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'vat_pan_number' => ['nullable', 'string', 'max:100'],
            'vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_spacing' => ['required', 'in:tight,normal,relaxed'],
            'section_spacing' => ['required', 'in:tight,normal,relaxed'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'section_order' => ['nullable', 'string'],
        ]);

        $decodedOrder = json_decode($validated['section_order'] ?? '', true);
        $validated['section_order'] = is_array($decodedOrder) ? $decodedOrder : BillTemplate::DEFAULT_SECTION_ORDER;

        $toggleFields = [
            'show_logo', 'show_customer', 'show_bill_number', 'show_date', 'show_sku', 'show_barcode',
            'show_quantity', 'show_unit', 'show_price', 'show_subtotal', 'show_discount',
            'show_cash_received', 'show_change', 'show_qr',
            'show_cashier', 'show_payment_method', 'calculate_vat',
        ];

        foreach ($toggleFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        return $validated;
    }
}