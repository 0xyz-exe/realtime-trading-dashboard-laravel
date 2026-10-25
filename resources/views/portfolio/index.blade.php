<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Portfolio & Capital Assets') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Net Worth Hero Banner & Deposit Box -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Net worth breakdown -->
                <div class="md:col-span-2 bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 text-white shadow-md flex flex-col justify-between">
                    <div>
                        <span class="text-xs uppercase font-bold text-gray-400 tracking-wider">Total Portfolio Net Worth</span>
                        <div class="text-4xl font-extrabold font-mono mt-1">${{ number_format($totalPortfolioCents / 100, 2) }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-gray-700/60 text-xs">
                        <div>
                            <span class="text-gray-400 block">Available Cash:</span>
                            <span class="text-emerald-400 font-bold font-mono text-base">${{ number_format($user->cash_balance_cents / 100, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Invested in Assets:</span>
                            <span class="text-indigo-400 font-bold font-mono text-base">${{ number_format($holdingsValueCents / 100, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Simulated Cash Deposit Box -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Fund Account</h3>
                        <p class="text-xs text-gray-500 mt-1">Deposit simulated test capital to trade additional assets.</p>
                    </div>
                    <form method="POST" action="{{ route('portfolio.deposit') }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="amount_dollars" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Amount ($ USD)</label>
                            <input type="number" step="100" min="10" max="50000" id="amount_dollars" name="amount_dollars" value="5000" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow transition">
                            Deposit Simulated Funds
                        </button>
                    </form>
                </div>
            </div>

            <!-- Holdings Asset Allocation Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-base">Asset Holdings & Performance</h3>
                </div>
                @if($holdings->isEmpty())
                    <div class="p-10 text-center text-gray-400 text-xs">
                        No asset positions owned yet. Visit the <a href="{{ route('trade.terminal') }}" class="text-emerald-600 underline font-semibold">Trade Terminal</a> to buy crypto or equities.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="p-4">Asset</th>
                                    <th class="p-4">Quantity</th>
                                    <th class="p-4">Avg Buy Price</th>
                                    <th class="p-4">Market Price</th>
                                    <th class="p-4">Position Value</th>
                                    <th class="p-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-mono">
                                @foreach($holdings as $h)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4 font-bold text-gray-900 font-sans">
                                            {{ $h->tradingPair->name }} ({{ $h->tradingPair->base_asset }})
                                        </td>
                                        <td class="p-4">{{ number_format((float)$h->quantity, 4) }}</td>
                                        <td class="p-4">${{ number_format($h->avg_buy_price_cents / 100, 2) }}</td>
                                        <td class="p-4">${{ number_format($h->tradingPair->current_price_cents / 100, 2) }}</td>
                                        <td class="p-4 font-bold text-gray-900">${{ number_format($h->current_value_cents / 100, 2) }}</td>
                                        <td class="p-4 text-right font-sans">
                                            <a href="{{ route('trade.terminal', $h->tradingPair) }}" class="px-3 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-bold transition">
                                                Trade
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Ledger Transactions Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-base">Account Activity Ledger</h3>
                </div>
                @if($transactions->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-xs">
                        No transactions recorded yet.
                    </div>
                @else
                    <div class="divide-y divide-gray-100 text-xs font-mono">
                        @foreach($transactions as $tx)
                            <div class="p-4 flex items-center justify-between">
                                <div class="font-sans">
                                    <div class="font-semibold text-gray-900">{{ $tx->description }}</div>
                                    <div class="text-gray-400 text-[11px]">{{ $tx->created_at->format('M d, Y H:i:s') }}</div>
                                </div>
                                <div class="font-bold {{ $tx->amount_cents >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $tx->amount_cents >= 0 ? '+' : '' }}${{ number_format(abs($tx->amount_cents) / 100, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-4 border-t border-gray-100 font-sans">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
