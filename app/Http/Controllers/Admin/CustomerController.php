<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Show the customer list, with optional search and each
     * customer's purchase summary.
     */
    public function index(Request $request)
    {
        $query = Customer::withCount('sales')
            ->withSum('sales', 'total')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate((int) \App\Models\Setting::get('items_per_page', 20))->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Update a customer's details.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete a customer.
     * Their past sales are kept (customer_id is nullable on sales,
     * per our original database design), just unlinked from this
     * customer record.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer deleted successfully.');
    }
}