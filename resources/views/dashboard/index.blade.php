@extends('layouts.app')

@section('title', 'Dashboard - Smart Retail POS')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/mockup.css') }}">
@endsection

@section('content')
<div class="page-container">

    <h1 class="page-title">Dashboard</h1>

    <div class="mockup-note">
        This is a visual mockup with sample data. Real numbers will appear once the backend is connected.
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card__label">Today's Sales</div>
            <div class="stat-card__value">Rs. 12,450</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">This Month</div>
            <div class="stat-card__value">Rs. 284,900</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Total Bills</div>
            <div class="stat-card__value">318</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Low Stock Items</div>
            <div class="stat-card__value">4</div>
        </div>
    </div>

    <div class="dashboard-section">
        <h3>Best-Selling Products</h3>
        <ul class="simple-list">
            <li><span>Rice</span> <span class="muted">142 sold</span></li>
            <li><span>Coca Cola</span> <span class="muted">98 sold</span></li>
            <li><span>Recharge Card</span> <span class="muted">76 sold</span></li>
            <li><span>Red Bull</span> <span class="muted">61 sold</span></li>
        </ul>
    </div>

    <div class="dashboard-section">
        <h3>Low Stock Products</h3>
        <ul class="simple-list">
            <li><span>Cooking Oil 1L</span> <span class="muted">6 left</span></li>
            <li><span>Salt 1kg</span> <span class="muted">8 left</span></li>
            <li><span>Soap</span> <span class="muted">3 left</span></li>
        </ul>
    </div>

    <div class="dashboard-section">
        <h3>Recent Transactions</h3>
        <ul class="simple-list">
            <li><span>Bill #000125</span> <span class="muted">Rs. 450</span></li>
            <li><span>Bill #000124</span> <span class="muted">Rs. 220</span></li>
            <li><span>Bill #000123</span> <span class="muted">Rs. 890</span></li>
        </ul>
    </div>

</div>
@endsection