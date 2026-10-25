<?php

namespace App\Http\Controllers;

use App\Models\TradingTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $holdings = $user->holdings()->with('tradingPair')->where('quantity', '>', 0)->get();
        $transactions = $user->transactions()->latest()->paginate(15);

        $holdingsValueCents = 0;
        foreach ($holdings as $holding) {
            $holdingsValueCents += $holding->current_value_cents;
        }

        $totalPortfolioCents = $user->cash_balance_cents + $holdingsValueCents;

        return view('portfolio.index', compact('user', 'holdings', 'transactions', 'holdingsValueCents', 'totalPortfolioCents'));
    }

    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount_dollars' => 'required|numeric|min:10|max:50000',
        ]);

        $cents = (int) round($validated['amount_dollars'] * 100);
        $user = $request->user();

        DB::transaction(function () use ($user, $cents) {
            $user->increment('cash_balance_cents', $cents);

            TradingTransaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount_cents' => $cents,
                'description' => 'Simulated Cash Deposit: +$' . number_format($cents / 100, 2),
            ]);
        });

        return back()->with('success', 'Deposited $' . number_format($cents / 100, 2) . ' into your trading account!');
    }
}
