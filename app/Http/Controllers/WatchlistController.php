<?php

namespace App\Http\Controllers;

use App\Models\TradingPair;
use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function toggle(Request $request, TradingPair $pair): RedirectResponse
    {
        $user = $request->user();
        $existing = Watchlist::where('user_id', $user->id)->where('trading_pair_id', $pair->id)->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', "Removed {$pair->symbol} from watchlist.");
        }

        Watchlist::create([
            'user_id' => $user->id,
            'trading_pair_id' => $pair->id,
        ]);

        return back()->with('success', "Added {$pair->symbol} to watchlist.");
    }
}
