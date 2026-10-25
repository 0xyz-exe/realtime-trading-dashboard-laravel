<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trading Portfolio & Markets') }}
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('trade.terminal') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow transition">
                    Open Trading Terminal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Portfolio Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Net Worth</span>
                    <div class="text-3xl font-extrabold text-gray-900 mt-2 font-mono">{{ $stats['total_portfolio'] }}</div>
                    <span class="text-xs text-gray-400 mt-1 block">Cash + active positions</span>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Available Cash</span>
                    <div class="text-3xl font-extrabold text-emerald-600 mt-2 font-mono">{{ $stats['cash_balance'] }}</div>
                    <a href="{{ route('portfolio.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline mt-1 inline-block">+ Add simulated funds</a>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Assets Value</span>
                    <div class="text-3xl font-extrabold text-indigo-600 mt-2 font-mono">{{ $stats['holdings_value'] }}</div>
                    <span class="text-xs text-gray-400 mt-1 block">Mark-to-market valuation</span>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Open Orders</span>
                    <div class="text-3xl font-extrabold text-amber-600 mt-2 font-mono">{{ $stats['open_orders_count'] }}</div>
                    <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline mt-1 inline-block">View order history &rarr;</a>
                </div>
            </div>

            <!-- Market Tickers Grid -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Markets & 24h Tickers</h3>
                    <span class="text-xs text-gray-500">Live order engine prices</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($marketTickers as $ticker)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-emerald-300 transition flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-900 text-sm">{{ $ticker->symbol }}</span>
                                    <span class="text-xs font-semibold {{ $ticker->change_24h >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $ticker->change_24h >= 0 ? '+' : '' }}{{ $ticker->change_24h }}%
                                    </span>
                                </div>
                                <div class="text-lg font-extrabold text-gray-900 mt-2 font-mono">{{ $ticker->formatted_price }}</div>
                                <div class="text-[11px] text-gray-400 mt-1">
                                    24h Vol: ${{ number_format($ticker->volume_cents / 100000000, 1) }}M
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <a href="{{ route('trade.terminal', $ticker) }}" class="w-full text-center py-1.5 px-3 bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-700 rounded-lg text-xs font-bold transition">
                                    Trade &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Two-Column Layout: Holdings & Recent Executed Trades -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Current Holdings -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 text-base">Active Positions</h3>
                        <a href="{{ route('portfolio.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">Portfolio Breakdown &rarr;</a>
                    </div>
                    @if($activeHoldings->isEmpty())
                        <div class="p-8 text-center text-gray-400 text-xs">
                            No open positions. Use the Trade Terminal to buy your first asset.
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($activeHoldings as $holding)
                                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $holding->tradingPair->name }} ({{ $holding->tradingPair->base_asset }})</div>
                                        <div class="text-xs text-gray-500 font-mono">Qty: {{ number_format((float)$holding->quantity, 4) }} &bull; Avg Buy: ${{ number_format($holding->avg_buy_price_cents / 100, 2) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold font-mono text-sm text-gray-900">${{ number_format($holding->current_value_cents / 100, 2) }}</div>
                                        <a href="{{ route('trade.terminal', $holding->tradingPair) }}" class="text-[11px] font-semibold text-emerald-600 hover:underline">Trade</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 text-base">Recent Order Activity</h3>
                        <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">All Orders &rarr;</a>
                    </div>
                    @if($recentOrders->isEmpty())
                        <div class="p-8 text-center text-gray-400 text-xs">
                            No orders placed yet.
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($recentOrders as $order)
                                <div class="p-4 flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-3">
                                        <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] {{ $order->side === 'buy' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $order->side }}
                                        </span>
                                        <div>
                                            <span class="font-bold text-gray-900">{{ $order->tradingPair->symbol }}</span>
                                            <span class="text-gray-400">({{ ucfirst($order->type) }})</span>
                                            <div class="text-gray-500 font-mono">{{ (float)$order->quantity }} @ ${{ number_format($order->price_cents / 100, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-bold text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $order->status === 'filled' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'open' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
