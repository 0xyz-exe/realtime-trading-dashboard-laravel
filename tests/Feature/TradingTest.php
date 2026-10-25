<?php

namespace Tests\Feature;

use App\Models\Holding;
use App\Models\Order;
use App\Models\TradingPair;
use App\Models\User;
use Database\Seeders\TradingPairSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradingPairSeeder::class);
    }

    public function test_user_can_access_dashboard_with_portfolio_stats(): void
    {
        $user = User::factory()->create(['cash_balance_cents' => 1500000]); // $15,000

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Trading Portfolio & Markets');
        $response->assertSee('$15,000.00');
    }

    public function test_user_can_view_trade_terminal_with_active_pairs(): void
    {
        $user = User::factory()->create();
        $btc = TradingPair::where('symbol', 'BTC/USD')->first();

        $response = $this->actingAs($user)->get("/trade/{$btc->id}");
        $response->assertStatus(200);
        $response->assertSee('BTC/USD');
        $response->assertSee('Place Buy Order');
    }

    public function test_user_can_place_market_buy_order_with_sufficient_cash(): void
    {
        $user = User::factory()->create(['cash_balance_cents' => 2000000]); // $20,000.00
        $btc = TradingPair::where('symbol', 'BTC/USD')->first(); // $96,500.00 (9,650,000 cents)

        // Buy 0.1 BTC = $9,650.00 (965,000 cents)
        $response = $this->actingAs($user)->post('/trade/order', [
            'trading_pair_id' => $btc->id,
            'side' => 'buy',
            'type' => 'market',
            'quantity' => 0.1,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1035000, $user->fresh()->cash_balance_cents); // 2,000,000 - 965,000

        $this->assertDatabaseHas('holdings', [
            'user_id' => $user->id,
            'trading_pair_id' => $btc->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'trading_pair_id' => $btc->id,
            'side' => 'buy',
            'type' => 'market',
            'status' => 'filled',
            'total_cents' => 965000,
        ]);
    }

    public function test_user_cannot_place_market_buy_order_with_insufficient_cash(): void
    {
        $user = User::factory()->create(['cash_balance_cents' => 50000]); // $500.00
        $btc = TradingPair::where('symbol', 'BTC/USD')->first();

        // Attempt to buy 1 BTC ($96,500.00)
        $response = $this->actingAs($user)->post('/trade/order', [
            'trading_pair_id' => $btc->id,
            'side' => 'buy',
            'type' => 'market',
            'quantity' => 1.0,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(50000, $user->fresh()->cash_balance_cents);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_user_can_place_market_sell_order_when_holding_asset(): void
    {
        $user = User::factory()->create(['cash_balance_cents' => 100000]); // $1,000.00
        $eth = TradingPair::where('symbol', 'ETH/USD')->first(); // $3,250.00 (325,000 cents)

        // Seed initial holding of 2 ETH
        Holding::create([
            'user_id' => $user->id,
            'trading_pair_id' => $eth->id,
            'quantity' => 2.0,
            'avg_buy_price_cents' => 300000,
        ]);

        // Sell 1 ETH = $3,250.00 (325,000 cents)
        $response = $this->actingAs($user)->post('/trade/order', [
            'trading_pair_id' => $eth->id,
            'side' => 'sell',
            'type' => 'market',
            'quantity' => 1.0,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(425000, $user->fresh()->cash_balance_cents); // 100,000 + 325,000

        $holding = $user->getHolding($eth->id);
        $this->assertEquals(1.0, (float)$holding->quantity);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'trading_pair_id' => $eth->id,
            'side' => 'sell',
            'status' => 'filled',
        ]);
    }

    public function test_user_cannot_place_market_sell_order_without_holding(): void
    {
        $user = User::factory()->create();
        $sol = TradingPair::where('symbol', 'SOL/USD')->first();

        $response = $this->actingAs($user)->post('/trade/order', [
            'trading_pair_id' => $sol->id,
            'side' => 'sell',
            'type' => 'market',
            'quantity' => 5.0,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_user_can_place_limit_order(): void
    {
        $user = User::factory()->create(['cash_balance_cents' => 1000000]);
        $sol = TradingPair::where('symbol', 'SOL/USD')->first();

        $response = $this->actingAs($user)->post('/trade/order', [
            'trading_pair_id' => $sol->id,
            'side' => 'buy',
            'type' => 'limit',
            'quantity' => 2.0,
            'price' => 190.00, // $190 limit price
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'trading_pair_id' => $sol->id,
            'side' => 'buy',
            'type' => 'limit',
            'price_cents' => 19000,
            'status' => 'open',
        ]);
    }

    public function test_user_can_cancel_own_open_limit_order(): void
    {
        $user = User::factory()->create();
        $sol = TradingPair::where('symbol', 'SOL/USD')->first();

        $order = Order::create([
            'user_id' => $user->id,
            'trading_pair_id' => $sol->id,
            'side' => 'buy',
            'type' => 'limit',
            'price_cents' => 19000,
            'quantity' => 1.0,
            'total_cents' => 19000,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post("/orders/{$order->id}/cancel");
        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_user_cannot_cancel_other_users_order(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $sol = TradingPair::where('symbol', 'SOL/USD')->first();

        $orderA = Order::create([
            'user_id' => $userA->id,
            'trading_pair_id' => $sol->id,
            'side' => 'buy',
            'type' => 'limit',
            'price_cents' => 19000,
            'quantity' => 1.0,
            'total_cents' => 19000,
            'status' => 'open',
        ]);

        $response = $this->actingAs($userB)->post("/orders/{$orderA->id}/cancel");
        $response->assertStatus(403);
        $this->assertEquals('open', $orderA->fresh()->status);
    }

    public function test_user_can_deposit_simulated_cash(): void
    {
        $user = User::factory()->create(['cash_balance_cents' => 100000]); // $1,000

        $response = $this->actingAs($user)->post('/portfolio/deposit', [
            'amount_dollars' => 2500,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(350000, $user->fresh()->cash_balance_cents); // $1,000 + $2,500 = $3,500

        $this->assertDatabaseHas('trading_transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount_cents' => 250000,
        ]);
    }

    public function test_user_can_toggle_watchlist(): void
    {
        $user = User::factory()->create();
        $btc = TradingPair::where('symbol', 'BTC/USD')->first();

        $response = $this->actingAs($user)->post("/watchlist/{$btc->id}/toggle");
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('watchlists', [
            'user_id' => $user->id,
            'trading_pair_id' => $btc->id,
        ]);

        // Toggle off
        $this->actingAs($user)->post("/watchlist/{$btc->id}/toggle");
        $this->assertDatabaseMissing('watchlists', [
            'user_id' => $user->id,
            'trading_pair_id' => $btc->id,
        ]);
    }
}
