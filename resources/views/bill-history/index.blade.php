@extends('layouts.app')

@section('title', 'Bill History - Smart Retail POS')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/mockup.css') }}">
@endsection

@section('content')
<div class="page-container">

    <h1 class="page-title">Bill History</h1>

    <div class="mockup-note">
        Sample bills shown below. Real bill history will be saved once the backend is connected.
    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bill No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>000125</td>
                    <td>2026-08-10</td>
                    <td>Walk-in</td>
                    <td>3</td>
                    <td>Rs. 450</td>
                    <td><span class="payment-badge payment-badge--cash">Cash</span></td>
                    <td><button class="btn-view">View</button></td>
                </tr>
                <tr>
                    <td>000124</td>
                    <td>2026-08-10</td>
                    <td>Sita Sharma</td>
                    <td>2</td>
                    <td>Rs. 220</td>
                    <td><span class="payment-badge payment-badge--qr">QR</span></td>
                    <td><button class="btn-view">View</button></td>
                </tr>
                <tr>
                    <td>000123</td>
                    <td>2026-08-09</td>
                    <td>Walk-in</td>
                    <td>5</td>
                    <td>Rs. 890</td>
                    <td><span class="payment-badge payment-badge--cash">Cash</span></td>
                    <td><button class="btn-view">View</button></td>
                </tr>
                <tr>
                    <td>000122</td>
                    <td>2026-08-09</td>
                    <td>Ram Thapa</td>
                    <td>1</td>
                    <td>Rs. 120</td>
                    <td><span class="payment-badge payment-badge--qr">QR</span></td>
                    <td><button class="btn-view">View</button></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@endsection