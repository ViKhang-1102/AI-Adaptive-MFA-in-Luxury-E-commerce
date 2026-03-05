@extends('layouts.app')

@section('title', 'Messages Inbox')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Message Inbox</h1>

    @if(count($conversations) === 0)
    <div class="bg-white p-6 rounded-md-lg shadow-sm text-center">
        <p class="text-neutral-600">You have no conversations yet.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($conversations as $conv)
        <a href="{{ route('seller.messages.conversation', ['product' => $conv['product']->id, 'other' => $conv['other']->id]) }}"
           class="block bg-white p-4 rounded-md-lg shadow-sm hover:bg-neutral-50 flex justify-between items-center">
            <div>
                <div class="font-semibold">{{ $conv['product']->name }} ({{ $conv['product']->seller->name }})</div>
                <div class="text-sm text-neutral-600">Customer: {{ $conv['other']->name }}</div>
                <div class="text-sm text-neutral-500 truncate" style="max-width:500px;">{{ $conv['last_message'] }}</div>
            </div>
            @if($conv['unread_count'] > 0)
            <span class="bg-red-600 text-white rounded-md-full px-2 py-1 text-xs">{{ $conv['unread_count'] }}</span>
            @endif
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
