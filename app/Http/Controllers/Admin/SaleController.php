<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Show the bill history list, with optional search/filter.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['items.product', 'items.unit', 'customer', 'billTemplate'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->paginate(20)->withQueryString();
        $paymentQrPath = \App\Models\Setting::get('payment_qr_path');
        $paymentQrUrl = $paymentQrPath ? asset('storage/' . $paymentQrPath) : null;

        return view('admin.bills.index', compact('sales', 'paymentQrUrl'));
    }
}