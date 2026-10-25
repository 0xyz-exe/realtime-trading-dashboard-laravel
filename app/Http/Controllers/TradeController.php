<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use App\Models\Order;
use App\Models\TradingPair;
use App\Models\TradingTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TradeController extends Controller
{
    public function terminal(?TradingPair $pair = null): View
    {
        if (!$pair || !$pair->exists) {
            $pair = TradingPair::where('symbol', 'BTC/USD')->first() ?? TradingPair::first();
        }

        $allPairs = TradingPair::where('is_active', true)->get();
        $user = auth()->user();

        $userHolding = $user ? $user->getHolding($pair->id) : null;
        $openOrders = $user ? $user->orders()->where('trading_pair_id', $pair->id)->where('status', 'open')->latest()->get() : collect();
        $recentTrades = Order::where('trading_pair_id', $pair->id)->where('status', 'filled')->latest()->take(10)->get();

        return view('trade.terminal', compact('pair', 'allPairs', 'userHolding', 'openOrders', 'recentTrades'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trading_pair_id' => 'required|exists:trading_pairs,id',
            'side' => 'required|in:buy,sell',
            'type' => 'required|in:market,limit',
            'quantity' => 'required|numeric|min:0.000001|max:100000',
            'price' => 'nullable|numeric|min:0.01',
        ]);

        $user = $request->user();
        $pair = TradingPair::findOrFail($validated['trading_pair_id']);
        $quantity = (float) $validated['quantity'];
        $side = $validated['side'];
        $type = $validated['type'];

        $priceCents = ($type === 'limit' && !empty($validated['price']))
            ? (int) round($validated['price'] * 100)
            : $pair->current_price_cents;

        $totalCents = (int) round($quantity * $priceCents);

        if ($side === 'buy') {
            if (!$user->hasSufficientCash($totalCents)) {
                return back()->with('error', "Insufficient cash balance. Required: $" . number_format($totalCents / 100, 2) . ", Available: $" . number_format($user->cash_balance_cents / 100, 2));
            }

            DB::transaction(function () use ($user, $pair, $side, $type, $priceCents, $quantity, $totalCents) {
                // Deduct cash
                $user->decrement('cash_balance_cents', $totalCents);

                if ($type === 'market') {
                    // Update or create holding
                    $holding = Holding::firstOrCreate(
                        ['user_id' => $user->id, 'trading_pair_id' => $pair->id],
                        ['quantity' => 0, 'avg_buy_price_cents' => 0]
                    );

                    $currentQty = (float) $holding->quantity;
                    $newQty = $currentQty + $quantity;
                    $newAvgPrice = (int) round((($currentQty * $holding->avg_buy_price_cents) + $totalCents) / ($newQty ?: 1));

                    $holding->update([
                        'quantity' => $newQty,
                        'avg_buy_price_cents' => $newAvgPrice,
                    ]);

                    Order::create([
                        'user_id' => $user->id,
                        'trading_pair_id' => $pair->id,
                        'side' => 'buy',
                        'type' => 'market',
                        'price_cents' => $priceCents,
                        'quantity' => $quantity,
                        'total_cents' => $totalCents,
                        'status' => 'filled',
                        'filled_at' => now(),
                    ]);

                    TradingTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'trade_buy',
                        'amount_cents' => -$totalCents,
                        'description' => "Market Buy: {$quantity} {$pair->base_asset} @ $" . number_format($priceCents / 100, 2),
                    ]);
                } else {
                    // Limit order
                    Order::create([
                        'user_id' => $user->id,
                        'trading_pair_id' => $pair->id,
                        'side' => 'buy',
                        'type' => 'limit',
                        'price_cents' => $priceCents,
                        'quantity' => $quantity,
                        'total_cents' => $totalCents,
                        'status' => 'open',
                    ]);
                }
            });

            return back()->with('success', 'Buy order placed successfully!');
        } else {
            // Sell side
            $holding = $user->getHolding($pair->id);
            if (!$holding || ((float) $holding->quantity) < $quantity) {
                $avail = $holding ? (float) $holding->quantity : 0;
                return back()->with('error', "Insufficient {$pair->base_asset} balance. Required: {$quantity}, Available: {$avail}");
            }

            DB::transaction(function () use ($user, $pair, $holding, $side, $type, $priceCents, $quantity, $totalCents) {
                if ($type === 'market') {
                    $newQty = ((float) $holding->quantity) - $quantity;
                    $holding->update(['quantity' => max(0, $newQty)]);

                    $user->increment('cash_balance_cents', $totalCents);

                    Order::create([
                        'user_id' => $user->id,
                        'trading_pair_id' => $pair->id,
                        'side' => 'sell',
                        'type' => 'market',
                        'price_cents' => $priceCents,
                        'quantity' => $quantity,
                        'total_cents' => $totalCents,
                        'status' => 'filled',
                        'filled_at' => now(),
                    ]);

                    TradingTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'trade_sell',
                        'amount_cents' => $totalCents,
                        'description' => "Market Sell: {$quantity} {$pair->base_asset} @ $" . number_format($priceCents / 100, 2),
                    ]);
                } else {
                    Order::create([
                        'user_id' => $user->id,
                        'trading_pair_id' => $pair->id,
                        'side' => 'sell',
                        'type' => 'limit',
                        'price_cents' => $priceCents,
                        'quantity' => $quantity,
                        'total_cents' => $totalCents,
                        'status' => 'open',
                    ]);
                }
            });

            return back()->with('success', 'Sell order placed successfully!');
        }
    }
}
