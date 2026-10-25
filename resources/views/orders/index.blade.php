<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order History & Executions') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <form method="GET" action="{{ route('orders.index') }}" class="w-full flex flex-col sm:flex-row gap-3">
                    <select name="status" class="text-xs border-gray-300 rounded-lg">
                        <option value="">All Statuses</option>
                        <option value="open" @selected(request('status') === 'open')>Open Orders</option>
                        <option value="filled" @selected(request('status') === 'filled')>Filled Orders</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled Orders</option>
                    </select>

                    <select name="side" class="text-xs border-gray-300 rounded-lg">
                        <option value="">All Sides</option>
                        <option value="buy" @selected(request('side') === 'buy')>Buy Only</option>
                        <option value="sell" @selected(request('side') === 'sell')>Sell Only</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg">Filter</button>
                    @if(request()->hasAny(['status', 'side']))
                        <a href="{{ route('orders.index') }}" class="px-3 py-2 text-xs text-gray-400 hover:text-gray-600 self-center">Clear</a>
                    @endif
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                @if($orders->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-xs">
                        No orders recorded matching your filter.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="p-4">Time</th>
                                    <th class="p-4">Pair</th>
                                    <th class="p-4">Side / Type</th>
                                    <th class="p-4">Price</th>
                                    <th class="p-4">Quantity</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-mono">
                                @foreach($orders as $o)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 text-gray-500 font-sans">{{ $o->created_at->format('M d, H:i') }}</td>
                                        <td class="p-4 font-bold text-gray-900 font-sans">{{ $o->tradingPair->symbol }}</td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] {{ $o->side === 'buy' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                {{ $o->side }}
                                            </span>
                                            <span class="text-gray-400 font-sans text-[11px] ml-1">{{ ucfirst($o->type) }}</span>
                                        </td>
                                        <td class="p-4">${{ number_format($o->price_cents / 100, 2) }}</td>
                                        <td class="p-4">{{ (float)$o->quantity }}</td>
                                        <td class="p-4 font-bold text-gray-900">${{ number_format($o->total_cents / 100, 2) }}</td>
                                        <td class="p-4 font-sans">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $o->status === 'filled' ? 'bg-emerald-50 text-emerald-700' : ($o->status === 'open' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                                {{ ucfirst($o->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-right font-sans">
                                            @if($o->status === 'open')
                                                <form method="POST" action="{{ route('orders.cancel', $o) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:text-rose-800">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
