<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <!-- Pair Selector -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 text-lg font-extrabold text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-xl transition">
                        <span>{{ $pair->symbol }}</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1" style="display: none;">
                        @foreach($allPairs as $p)
                            <a href="{{ route('trade.terminal', $p) }}" class="flex items-center justify-between px-4 py-2 hover:bg-gray-50 text-xs font-semibold {{ $p->id === $pair->id ? 'bg-emerald-50 text-emerald-700' : 'text-gray-800' }}">
                                <span>{{ $p->symbol }}</span>
                                <span class="font-mono">{{ $p->formatted_price }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Price & 24h stats ticker -->
                <div class="flex items-baseline space-x-4 text-xs">
                    <span class="text-xl font-extrabold font-mono text-gray-900">{{ $pair->formatted_price }}</span>
                    <span class="font-bold {{ $pair->change_24h >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $pair->change_24h >= 0 ? '+' : '' }}{{ $pair->change_24h }}%
                    </span>
                    <div class="hidden sm:flex space-x-4 text-gray-400">
                        <span>24h High: <strong class="text-gray-700 font-mono">${{ number_format($pair->high_price_cents / 100, 2) }}</strong></span>
                        <span>24h Low: <strong class="text-gray-700 font-mono">${{ number_format($pair->low_price_cents / 100, 2) }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Watchlist Toggle -->
            <form method="POST" action="{{ route('watchlist.toggle', $pair) }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                    ★ Watchlist
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Main Terminal Grid: Chart + Order Box -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Interactive Market Chart Visualization -->
                <div class="lg:col-span-2 bg-gray-950 rounded-2xl p-6 text-white shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-3 text-xs">
                            <span class="font-bold text-gray-400">INTERVAL:</span>
                            <span class="px-2 py-0.5 rounded bg-gray-800 text-emerald-400 font-semibold">1H</span>
                            <span class="px-2 py-0.5 rounded hover:bg-gray-800 text-gray-400 cursor-pointer">4H</span>
                            <span class="px-2 py-0.5 rounded hover:bg-gray-800 text-gray-400 cursor-pointer">1D</span>
                        </div>
                        <div class="text-xs text-gray-400">
                            Market Engine: <span class="text-emerald-400 font-bold">ONLINE</span>
                        </div>
                    </div>

                    <!-- Candlestick SVG visualization -->
                    <div class="h-64 sm:h-80 w-full flex items-end justify-between px-2 pt-6 pb-2 border-b border-gray-800">
                        @php
                            $bars = [
                                ['h' => 45, 'up' => true],
                                ['h' => 60, 'up' => true],
                                ['h' => 50, 'up' => false],
                                ['h' => 75, 'up' => true],
                                ['h' => 65, 'up' => false],
                                ['h' => 80, 'up' => true],
                                ['h' => 90, 'up' => true],
                                ['h' => 70, 'up' => false],
                                ['h' => 85, 'up' => true],
                                ['h' => 95, 'up' => true],
                                ['h' => 88, 'up' => false],
                                ['h' => 110, 'up' => true],
                                ['h' => 105, 'up' => false],
                                ['h' => 125, 'up' => true],
                                ['h' => 120, 'up' => false],
                                ['h' => 140, 'up' => true],
                            ];
                        @endphp
                        @foreach($bars as $bar)
                            <div class="flex flex-col items-center flex-1 h-full justify-end group cursor-pointer">
                                <div class="w-0.5 {{ $bar['up'] ? 'bg-emerald-500' : 'bg-rose-500' }}" style="height: {{ $bar['h'] + 15 }}px;"></div>
                                <div class="w-3 sm:w-5 {{ $bar['up'] ? 'bg-emerald-500' : 'bg-rose-500' }} rounded-xs -mt-2" style="height: {{ $bar['h'] }}px;"></div>
                                <div class="w-0.5 {{ $bar['up'] ? 'bg-emerald-500' : 'bg-rose-500' }}" style="height: 10px;"></div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Bottom Chart Summary -->
                    <div class="mt-4 flex justify-between items-center text-xs text-gray-400 font-mono">
                        <span>Low: ${{ number_format($pair->low_price_cents / 100, 2) }}</span>
                        <span>Current: <strong class="text-white">{{ $pair->formatted_price }}</strong></span>
                        <span>High: ${{ number_format($pair->high_price_cents / 100, 2) }}</span>
                    </div>
                </div>

                <!-- Right Col: Order Placement Box -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ side: 'buy', type: 'market', currentPrice: {{ $pair->current_price_cents / 100 }}, price: {{ $pair->current_price_cents / 100 }}, quantity: 0.1, get total() { return (this.type === 'market' ? this.currentPrice : (this.price || this.currentPrice)) * (this.quantity || 0); } }">
                    <!-- Buy / Sell Toggle Tabs -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl mb-5">
                        <button type="button" @click="side = 'buy'" :class="side === 'buy' ? 'bg-emerald-600 text-white shadow' : 'text-gray-600'" class="py-2 text-xs font-bold rounded-lg transition">
                            Buy {{ $pair->base_asset }}
                        </button>
                        <button type="button" @click="side = 'sell'" :class="side === 'sell' ? 'bg-rose-600 text-white shadow' : 'text-gray-600'" class="py-2 text-xs font-bold rounded-lg transition">
                            Sell {{ $pair->base_asset }}
                        </button>
                    </div>

                    <form method="POST" action="{{ route('trade.order') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="trading_pair_id" value="{{ $pair->id }}">
                        <input type="hidden" name="side" :value="side">

                        <!-- Order Type: Market / Limit -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Order Type</label>
                            <select name="type" x-model="type" class="w-full text-xs border-gray-300 rounded-lg focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="market">Market Order (Instant Fill)</option>
                                <option value="limit">Limit Order</option>
                            </select>
                        </div>

                        <!-- Limit Price (Conditional) -->
                        <div x-show="type === 'limit'" style="display: none;">
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Limit Price (USD)</label>
                            <input type="number" step="0.01" name="price" x-model.number="price" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <!-- Quantity -->
                        <div>
                            <div class="flex justify-between items-center mb-1 text-xs">
                                <label class="font-bold uppercase text-gray-500">Amount ({{ $pair->base_asset }})</label>
                                <span class="text-gray-400" x-show="side === 'sell'">Holding: {{ $userHolding ? (float)$userHolding->quantity : '0.00' }}</span>
                                <span class="text-gray-400" x-show="side === 'buy'">Cash: ${{ number_format(Auth::user()->cash_balance_cents / 100, 2) }}</span>
                            </div>
                            <input type="number" step="0.0001" name="quantity" x-model.number="quantity" required min="0.000001" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <!-- Total Computed Cost -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center text-xs">
                            <span class="text-gray-500 font-semibold">Estimated Total:</span>
                            <span class="font-bold font-mono text-gray-900" x-text="'$' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" :class="side === 'buy' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'" class="w-full py-3 text-white text-xs font-bold rounded-xl shadow transition">
                            <span x-text="side === 'buy' ? 'Place Buy Order' : 'Place Sell Order'"></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Open Orders & Recent Trades Feed -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Open Orders -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 text-sm mb-3">My Open Orders for {{ $pair->symbol }}</h3>
                    @if($openOrders->isEmpty())
                        <div class="text-xs text-gray-400 py-6 text-center">No active open limit orders.</div>
                    @else
                        <div class="divide-y divide-gray-100 text-xs">
                            @foreach($openOrders as $o)
                                <div class="py-2.5 flex justify-between items-center">
                                    <div>
                                        <span class="font-bold uppercase {{ $o->side === 'buy' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $o->side }}</span>
                                        <span class="font-mono ml-2">{{ (float)$o->quantity }} @ ${{ number_format($o->price_cents / 100, 2) }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('orders.cancel', $o) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-gray-100 hover:bg-rose-50 hover:text-rose-600 rounded text-[11px] font-semibold text-gray-600 transition">
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Recent Filled Trades -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 text-sm mb-3">Recent Executed Market Trades</h3>
                    @if($recentTrades->isEmpty())
                        <div class="text-xs text-gray-400 py-6 text-center">No recent market executions.</div>
                    @else
                        <div class="divide-y divide-gray-100 text-xs font-mono">
                            @foreach($recentTrades as $trade)
                                <div class="py-2 flex justify-between items-center">
                                    <span class="font-bold uppercase {{ $trade->side === 'buy' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $trade->side }}</span>
                                    <span>{{ (float)$trade->quantity }} {{ $pair->base_asset }}</span>
                                    <span>${{ number_format($trade->price_cents / 100, 2) }}</span>
                                    <span class="text-gray-400 text-[10px]">{{ $trade->created_at->format('H:i:s') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
