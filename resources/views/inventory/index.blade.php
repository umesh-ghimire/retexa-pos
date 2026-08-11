@extends('layouts.app')

@section('title', 'Inventory - Smart Retail POS')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
@endsection

@section('content')
<div class="inventory-page">

    <div class="inventory-header">
        <h1>Inventory</h1>
        <button class="btn-add-product" id="addProductBtn">+ Add Product</button>
    </div>

    <div class="inventory-toolbar">
        <input type="text" id="inventorySearchInput" placeholder="Search products...">
    </div>

    <div class="inventory-table-wrapper">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                {{-- Rows filled by JavaScript --}}
            </tbody>
        </table>
        <p class="empty-inventory-message" id="emptyInventoryMessage" style="display:none;">
            No products found.
        </p>
    </div>

</div>

{{-- ADD / EDIT PRODUCT MODAL --}}
<div class="product-modal-overlay" id="productModalOverlay" style="display:none;">
    <div class="product-modal-box">

        <h2 id="productModalTitle">Add Product</h2>

        <form id="productForm">
            <input type="hidden" id="productId">

            <label>Product Name</label>
            <input type="text" id="productNameInput" required>

            <label>Category</label>
            <input type="text" id="productCategoryInput" placeholder="e.g. Beverages">

            <label>Selling Price (Rs.)</label>
            <input type="number" id="productPriceInput" min="0" step="0.01" required>

            <label>Stock Quantity</label>
            <input type="number" id="productStockInput" min="0" required>

            <label>Status</label>
            <select id="productStatusInput">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div class="product-modal-actions">
                <button type="button" class="btn-cancel-product" id="cancelProductBtn">Cancel</button>
                <button type="submit" class="btn-save-product">Save Product</button>
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/data/dummy-products.js') }}"></script>
    <script src="{{ asset('js/inventory.js') }}"></script>
@endsection