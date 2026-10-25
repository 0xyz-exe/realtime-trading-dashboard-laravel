<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->orders()->with('tradingPair')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('side')) {
            $query->where('side', $request->input('side'));
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'open') {
            return back()->with('error', 'Only open limit orders can be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Order cancelled successfully.');
    }
}
