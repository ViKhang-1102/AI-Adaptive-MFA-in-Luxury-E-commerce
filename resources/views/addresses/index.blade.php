@extends('layouts.app')

@section('title', 'My Addresses')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Addresses</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add New Address -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">Add New Address</h3>
            <form action="{{ route('addresses.store') }}" method="POST" class="space-y-3">
                @csrf
                
                <input type="text" name="label" placeholder="Label (e.g., Home, Office)" 
                    class="w-full px-3 py-2 border rounded" maxlength="255">

                <input type="text" name="recipient_name" placeholder="Recipient Name" 
                    class="w-full px-3 py-2 border rounded" required>

                <input type="text" name="recipient_phone" placeholder="Phone Number" 
                    class="w-full px-3 py-2 border rounded" required>

                <textarea name="address" placeholder="Full Address" rows="3"
                    class="w-full px-3 py-2 border rounded" required></textarea>

                <label class="flex items-center">
                    <input type="checkbox" name="is_default" value="1" class="mr-2">
                    <span>Set as default address</span>
                </label>

                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Add Address
                </button>
            </form>
        </div>

        <!-- Address List -->
        <div class="lg:col-span-2 space-y-4">
            @if($addresses->isEmpty())
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <p class="text-gray-600">No addresses yet. Add one above.</p>
            </div>
            @else
                @foreach($addresses as $address)
                <div class="bg-white p-6 rounded-lg shadow">
                    @if($address->is_default)
                    <div class="mb-3 inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-bold">
                        Default Address
                    </div>
                    @endif
                    
                    <h3 class="font-bold text-lg">{{ $address->label ?? 'Address' }}</h3>
                    <p class="text-gray-700">{{ $address->recipient_name }}</p>
                    <p class="text-gray-600 text-sm">{{ $address->recipient_phone }}</p>
                    <p class="text-gray-600 mt-2">{{ $address->address }}</p>

                    <div class="mt-4 space-x-2">
                        <button onclick="editAddress({{ $address->id }})" class="text-blue-600 hover:underline">
                            Edit
                        </button>
                        <form action="{{ route('addresses.destroy', $address) }}" method="POST" class="inline" 
                            onsubmit="return confirm('Delete this address?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<script>
function editAddress(id) {
    alert('Edit functionality to be implemented');
}
</script>
@endsection
