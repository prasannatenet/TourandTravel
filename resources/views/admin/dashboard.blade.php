@extends('layouts.admin')
@section('title', 'Dashboard')
@section('header', 'Dashboard Overview')
@section('subheader', 'Track tours, bookings and revenue at a glance.')
@section('content')
@if(session('hotel_request_success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
        <i class="fa-solid fa-check-circle text-emerald-500"></i>
        {{ session('hotel_request_message') }}
        <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-600 hover:text-emerald-800"><i class="fa-solid fa-times"></i></button>
    </div>
@endif
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-6">
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-600">
<p class="text-xs font-semibold text-slate-500 uppercase">Total Tours</p>
<p class="text-2xl font-bold mt-1">{{ $stats['total_tours'] }}</p>
<p class="text-xs text-green-600 mt-1">Live from database</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-emerald-500">
<p class="text-xs font-semibold text-slate-500 uppercase">Total Bookings</p>
<p class="text-2xl font-bold mt-1">{{ $stats['total_bookings'] }}</p>
<p class="text-xs text-slate-500 mt-1">{{ $stats['confirmed_bookings'] }} confirmed · {{ $stats['cancelled_bookings'] }} cancelled</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-amber-500">
<p class="text-xs font-semibold text-slate-500 uppercase">Pending Bookings</p>
<p class="text-2xl font-bold mt-1">{{ $stats['pending_bookings'] }}</p>
<p class="text-xs text-amber-600 mt-1">Needs review</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-600">
    <p class="text-xs font-semibold text-slate-500 uppercase">Hotel Requests</p>
    <p class="text-2xl font-bold mt-1">{{ $stats['hotel_requests_pending'] }}</p>
    <p class="text-xs text-blue-600 mt-1">Waiting for hotel confirmation</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-violet-600">
<p class="text-xs font-semibold text-slate-500 uppercase">Monthly Revenue</p>
<p class="text-2xl font-bold mt-1">Rs.{{ number_format($stats['monthly_revenue']) }}</p>
<p class="text-xs text-slate-500 mt-1">{{ $stats['total_users'] }} users · {{ $stats['total_admins'] }} admins</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-rose-600">
<p class="text-xs font-semibold text-slate-500 uppercase">Total Revenue</p>
<p class="text-2xl font-bold mt-1">Rs.{{ number_format($stats['total_revenue']) }}</p>
<p class="text-xs text-slate-500 mt-1">All-time bookings</p>
</div>
</div>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
<div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold">Bookings Overview</h2>
<p class="text-xs text-slate-500 mb-4">Last 6 months</p>
<div class="h-64"><canvas id="bookingsChart"></canvas></div>
</div>
<div class="bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold mb-4">Revenue Overview</h2>
<p class="text-xs text-slate-500 mb-4">Last 6 months</p>
<div class="h-64"><canvas id="revenueChart"></canvas></div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
<div class="bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold mb-4">Booking Status</h2>
<div class="space-y-3">
@forelse ($statusDistribution as $status => $count)
<div class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-50">
<span class="text-sm text-slate-600">{{ ucfirst($status) }}</span>
<span class="font-semibold text-slate-900">{{ $count }}</span>
</div>
@empty
<p class="text-sm text-slate-500">No bookings yet.</p>
@endforelse
</div>
</div>
<div class="bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold mb-4">Top Packages</h2>
<div class="space-y-3">
@forelse ($topPackages as $pkg)
<div class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-50">
<span class="text-sm text-slate-600 truncate">{{ $pkg['name'] }}</span>
<span class="font-semibold text-slate-900">{{ $pkg['bookings'] }} booking{{ $pkg['bookings'] > 1 ? 's' : '' }}</span>
</div>
@empty
<p class="text-sm text-slate-500">No packages booked yet.</p>
@endforelse
</div>
</div>
<div class="bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold mb-4">Quick Actions</h2>
<div class="grid grid-cols-3 gap-2 text-center text-sm mb-3">
<a href="{{ route('admin.countries.index') }}" class="p-3 rounded-xl bg-blue-50 hover:bg-blue-100"><span class="block text-xl font-bold">{{ $stats['total_countries'] }}</span><span class="text-xs">Countries</span></a>
<a href="{{ route('admin.states.index') }}" class="p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100"><span class="block text-xl font-bold">{{ $stats['total_states'] }}</span><span class="text-xs">States</span></a>
<a href="{{ route('admin.cities.index') }}" class="p-3 rounded-xl bg-violet-50 hover:bg-violet-100"><span class="block text-xl font-bold">{{ $stats['total_destinations'] }}</span><span class="text-xs">Cities</span></a>
</div>
<p class="text-sm text-slate-600">Destination flow: Country - State - City.</p>
</div>
</div>
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
<div class="px-6 py-4 border-b"><h2 class="font-bold">Recent Bookings</h2></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr>
<th class="text-left px-6 py-3">ID</th><th class="text-left px-6 py-3">Customer</th><th class="text-left px-6 py-3">Tour</th><th class="text-left px-6 py-3">Amount</th><th class="text-left px-6 py-3">Status</th>
</tr></thead>
<tbody class="divide-y">
@foreach ($recentBookings as $b)
<tr class="hover:bg-slate-50"><td class="px-6 py-3 font-semibold">{{ $b['id'] }}</td><td class="px-6 py-3">{{ $b['customer'] }}</td><td class="px-6 py-3">{{ $b['tour'] }}</td><td class="px-6 py-3">Rs.{{ number_format($b['amount']) }}</td><td class="px-6 py-3">{{ $b['status'] }}</td></tr>
@endforeach
@if(empty($recentBookings))
<tr><td colspan="5" class="px-6 py-3 text-center text-slate-500">No recent bookings.</td></tr>
@endif
</tbody></table></div>
</div>
<div class="bg-white rounded-2xl shadow-sm overflow-hidden mt-6">
<div class="px-6 py-4 border-b"><h2 class="font-bold">Recent Users</h2></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr>
<th class="text-left px-6 py-3">Name</th><th class="text-left px-6 py-3">Email</th><th class="text-left px-6 py-3">Role</th><th class="text-left px-6 py-3">Joined</th>
</tr></thead>
<tbody class="divide-y">
@foreach ($recentUsers as $user)
<tr class="hover:bg-slate-50">
<td class="px-6 py-3 font-semibold">{{ $user->name }}</td>
<td class="px-6 py-3">{{ $user->email }}</td>
<td class="px-6 py-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs {{ $user->is_admin ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $user->is_admin ? 'Admin' : 'User' }}</span></td>
<td class="px-6 py-3">{{ $user->created_at?->format('Y-m-d') }}</td>
</tr>
@endforeach
@if(empty($recentUsers))
<tr><td colspan="4" class="px-6 py-3 text-center text-slate-500">No users yet.</td></tr>
@endif
</tbody></table></div>
</div>
@endsection
@push('scripts')
<script>
const bookingsLabels = @json($chartLabels ?? []);
const bookingsData = @json($chartData ?? []);
const revenueData = @json($revenueChartData ?? []);

const bookingsCtx = document.getElementById('bookingsChart');
if (bookingsCtx) {
    new Chart(bookingsCtx, {
        type: 'bar',
        data: {
            labels: bookingsLabels,
            datasets: [{
                label: 'Bookings',
                data: bookingsData,
                backgroundColor: '#2563eb',
                borderRadius: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });
}

const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: bookingsLabels,
            datasets: [{
                label: 'Revenue (Rs.)',
                data: revenueData,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
        },
    });
}
</script>
@endpush
