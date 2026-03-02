@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Thanh toán bị hủy</h3>
    <p>Giao dịch PayPal đã bị hủy. Vui lòng thử lại.</p>
    <a href="{{ url('/') }}" class="btn btn-secondary">Trở lại </a>
</div>
@endsection
