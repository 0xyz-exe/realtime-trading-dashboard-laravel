<?php

namespace Database\Seeders;

use App\Models\TradingPair;
use Illuminate\Database\Seeder;

class TradingPairSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            [
                'symbol' => 'BTC/USD',
                'name' => 'Bitcoin',
                'base_asset' => 'BTC',
                'quote_asset' => 'USD',
                'current_price_cents' => 9650000, // $96,500.00
                'change_24h' => 3.45,
                'high_price_cents' => 9780000,
                'low_price_cents' => 9420000,
                'volume_cents' => 45000000000,
            ],
            [
                'symbol' => 'ETH/USD',
                'name' => 'Ethereum',
                'base_asset' => 'ETH',
                'quote_asset' => 'USD',
                'current_price_cents' => 325000, // $3,250.00
                'change_24h' => -1.20,
                'high_price_cents' => 335000,
                'low_price_cents' => 318000,
                'volume_cents' => 22000000000,
            ],
            [
                'symbol' => 'SOL/USD',
                'name' => 'Solana',
                'base_asset' => 'SOL',
                'quote_asset' => 'USD',
                'current_price_cents' => 21000, // $210.00
                'change_24h' => 7.82,
                'high_price_cents' => 21800,
                'low_price_cents' => 19500,
                'volume_cents' => 14000000000,
            ],
            [
                'symbol' => 'NVDA/USD',
                'name' => 'NVIDIA Corp',
                'base_asset' => 'NVDA',
                'quote_asset' => 'USD',
                'current_price_cents' => 14250, // $142.50
                'change_24h' => 2.15,
                'high_price_cents' => 14500,
                'low_price_cents' => 14000,
                'volume_cents' => 38000000000,
            ],
            [
                'symbol' => 'AAPL/USD',
                'name' => 'Apple Inc',
                'base_asset' => 'AAPL',
                'quote_asset' => 'USD',
                'current_price_cents' => 23500, // $235.00
                'change_24h' => 0.65,
                'high_price_cents' => 23700,
                'low_price_cents' => 23350,
                'volume_cents' => 18000000000,
            ],
        ];

        foreach ($pairs as $data) {
            TradingPair::updateOrCreate(['symbol' => $data['symbol']], $data);
        }
    }
}
