@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="row">
    <div class="col-12">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"><form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h4>Shop Profile</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" class="form-control" name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" name="shop_address" value="{{ old('shop_address', $settings['shop_address']) }}">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="shop_phone" value="{{ old('shop_phone', $settings['shop_phone']) }}">
                    </div>
                    <p class="text-muted" style="font-size:0.85rem;">
                        This is a general reference profile. Your printed receipts use whichever details are set in each individual <a href="{{ route('admin.bill-templates.index') }}">Bill Design</a>.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Billing Preferences</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Default Discount (Rs.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="default_discount" value="{{ old('default_discount', $settings['default_discount'] ?? 0) }}" style="max-width:200px;">
                        <small class="text-muted">Pre-fills the discount field on the billing screen. Cashiers can still change it per bill.</small>
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Threshold</label>
                        <input type="number" step="0.001" min="0" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 5) }}" style="max-width:200px;">
                        <small class="text-muted">Suggested minimum stock level when adding a new product. You can still set a different value per product.</small>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>Digital Wallet QR</h4>
                </div>
                <div class="card-body">
                    @if ($settings['payment_qr_path'])
                        <img src="{{ asset('storage/' . $settings['payment_qr_path']) }}" style="width:120px; height:120px; object-fit:contain; border:1px solid var(--color-border); border-radius:4px; padding:6px; margin-bottom:10px; display:block;">
                    @endif
                    <div class="form-group">
                        <label>Upload QR Image</label>
                        <input type="file" class="form-control-file" name="payment_qr" accept="image/*">
                        <small class="text-muted">This replaces the demo QR on all printed receipts.</small>
                    </div>
                </div>
            </div>



            <button type="submit" class="btn btn-primary">Save Settings</button>

        </form>

    </div>
</div>
@endsection