@extends('layouts.app')

@section('title', 'Settings - Smart Retail POS')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/mockup.css') }}">
@endsection

@section('content')
<div class="page-container">

    <h1 class="page-title">Settings</h1>

    <div class="mockup-note">
        This is a visual mockup. Saving settings will be enabled once the backend is connected.
    </div>

    <div class="settings-section">
        <h3>Shop Information</h3>
        <div class="settings-field">
            <label>Shop Name</label>
            <input type="text" value="ABC Store">
        </div>
        <div class="settings-field">
            <label>Shop Address</label>
            <input type="text" value="Ghorahi, Dang, Nepal">
        </div>
        <div class="settings-field">
            <label>Phone Number</label>
            <input type="text" value="98XXXXXXXX">
        </div>
    </div>

    <div class="settings-section">
        <h3>Billing Preferences</h3>
        <div class="settings-field">
            <label>Default Discount (Rs.)</label>
            <input type="number" value="0">
        </div>
        <div class="settings-field">
            <label>Tax Rate (%)</label>
            <input type="number" value="0">
        </div>
    </div>

    <button class="btn-save-settings">Save Settings</button>

</div>
@endsection