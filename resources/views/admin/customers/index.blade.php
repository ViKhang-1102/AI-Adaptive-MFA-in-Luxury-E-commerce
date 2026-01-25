@extends('layouts.app')
@section('title', 'Manage Customers')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Customers Management</h1>
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
                        <th class="px-6 py-3 text-left">Status</th>
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
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="text-blue-600 hover:underline">View</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="ml-4 text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline ml-4" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
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
