@extends('layouts.admin')
@section('title', 'Dashboard')
@section('header', 'Dashboard Overview')
@section('subheader', 'Track tours, bookings and revenue at a glance.')
@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-6">
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-600">
<p class="text-xs font-semibold text-slate-500 uppercase">Total Tours</p>
<p class="text-2xl font-bold mt-1">{{ $stats['total_tours'] }}</p>
<p class="text-xs text-green-600 mt-1">+12% this month</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-emerald-500">
<p class="text-xs font-semibold text-slate-500 uppercase">Total Bookings</p>
<p class="text-2xl font-bold mt-1">{{ $stats['total_bookings'] }}</p>
<p class="text-xs text-green-600 mt-1">+8% this week</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-amber-500">
<p class="text-xs font-semibold text-slate-500 uppercase">Pending Bookings</p>
<p class="text-2xl font-bold mt-1">{{ $stats['pending_bookings'] }}</p>
<p class="text-xs text-amber-600 mt-1">Needs review</p>
</div>
<div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-violet-600">
<p class="text-xs font-semibold text-slate-500 uppercase">Monthly Revenue</p>
<p class="text-2xl font-bold mt-1">Rs.{{ number_format($stats['monthly_revenue']) }}</p>
<p class="text-xs text-slate-500 mt-1">{{ $stats['total_users'] }} users / {{ $stats['total_admins'] }} admins</p>
</div>
</div>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
<div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold">Bookings Overview</h2>
<p class="text-xs text-slate-500 mb-4">Last 6 months</p>
<div class="h-64"><canvas id="bookingsChart"></canvas></div>
</div>
<div class="bg-white rounded-2xl shadow-sm p-6">
<h2 class="font-bold mb-4">Quick Actions</h2>
<div class="grid grid-cols-3 gap-2 text-center text-sm mb-3">
<a href="{{ route('admin.countries.index') }}" class="p-3 rounded-xl bg-blue-50 hover:bg-blue-100"><span class="block text-xl font-bold">{{ $stats['total_countries'] ?? 0 }}</span><span class="text-xs">Countries</span></a>
<a href="{{ route('admin.states.index') }}" class="p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100"><span class="block text-xl font-bold">{{ $stats['total_states'] ?? 0 }}</span><span class="text-xs">States</span></a>
<a href="{{ route('admin.cities.index') }}" class="p-3 rounded-xl bg-violet-50 hover:bg-violet-100"><span class="block text-xl font-bold">{{ $stats['total_destinations'] ?? 0 }}</span><span class="text-xs">Cities</span></a>
</div>
<p class="text-sm text-slate-600">Destination flow: Country - State - City.</p>
<div class="mt-4 p-4 rounded-xl bg-slate-900 text-white text-xs">Next step: add Tours and Bookings tables for live stats.</div>
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
</tbody></table></div>
</div>
@endsection
@push('scripts')
<script>
const ctx = document.getElementById('bookingsChart');
if (ctx) { new Chart(ctx, { type: 'bar', data: { labels: ['May','Jun','Jul','Aug','Sep','Oct'], datasets: [{ label: 'Bookings', data: [18,25,22,30,28,35], backgroundColor: '#2563eb', borderRadius: 8 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } }); }
</script>
@endpush
