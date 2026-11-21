@extends('layouts.admin')
@section('title', 'Newsletter Subscribers')
@section('header', 'Newsletter Subscribers')
@section('subheader', 'Manage email addresses that signed up for the newsletter from the website footer.')

@section('content')
<div class="overflow-hidden bg-white shadow rounded-2xl">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Subscriber List</h2>
            <p class="text-xs text-slate-500 mt-0.5">All newsletter sign-ups from the website footer.</p>
        </div>
    </div>

    <div class="p-4 sm:p-6">
        @if(session('success'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Subscribed At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($subscribers as $subscriber)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $subscriber->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $subscriber->subscribed_at?->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-12 text-center text-slate-400 text-sm">No newsletter subscribers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $subscribers->links() }}</div>
    </div>
</div>
@endsection