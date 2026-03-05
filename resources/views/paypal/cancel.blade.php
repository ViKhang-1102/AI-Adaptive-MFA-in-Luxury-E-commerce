@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Payment Cancelled</h3>
    <p>PayPal transaction was cancelled. Please try again.</p>
    <a href="{{ url('/') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">Return to Home</a>
</div>
@endsection
