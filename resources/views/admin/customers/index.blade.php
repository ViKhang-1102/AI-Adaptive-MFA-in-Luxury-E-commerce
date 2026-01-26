@extends('layouts.app')
@section('title', 'Manage Customers')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 font-semibold">
                ← Dashboard
            </a>
            <h1 class="text-3xl font-bold">Customers Management</h1>
        </div>
        <a href="{{ route('admin.customers.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold">
            <i class="fas fa-plus"></i> Add Customer
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex gap-4">
            <input type="text" name="search" placeholder="Search by name or email" class="flex-1 px-4 py-2 border rounded" value="{{ request('search') }}">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Search</button>
        </form>
    </div>

    @if($customers->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No customers found.
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Phone</th>
                        <th class="px-6 py-3 text-left">Joined</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">{{ $customer->name }}</td>
                        <td class="px-6 py-3">{{ $customer->email }}</td>
                        <td class="px-6 py-3">{{ $customer->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-3">{{ $customer->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Delete this customer permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
