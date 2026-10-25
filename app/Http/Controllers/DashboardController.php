<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TradingPair;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->load(['holdings.tradingPair', 'watchlists.tradingPair']);

        $holdingsValueCents = 0;
        foreach ($user->holdings as $holding) {
            $holdingsValueCents += $holding->current_value_cents;
        }

        $totalPortfolioCents = $user->cash_balance_cents + $holdingsValueCents;

        $stats = [
            'cash_balance' => '$' . number_format($user->cash_balance_cents / 100, 2),
            'holdings_value' => '$' . number_format($holdingsValueCents / 100, 2),
            'total_portfolio' => '$' . number_format($totalPortfolioCents / 100, 2),
            'open_orders_count' => $user->orders()->where('status', 'open')->count(),
        ];

        $marketTickers = TradingPair::where('is_active', true)->get();
        $recentOrders = $user->orders()->with('tradingPair')->latest()->take(5)->get();
        $activeHoldings = $user->holdings()->with('tradingPair')->where('quantity', '>', 0)->get();

        return view('dashboard', compact('stats', 'marketTickers', 'recentOrders', 'activeHoldings'));
    }
}
