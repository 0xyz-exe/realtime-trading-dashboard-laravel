# Real-Time Trading Dashboard (Laravel 12 / Breeze / Tailwind / SQLite)

A modern, high-precision financial trading and portfolio management application built with Laravel, Tailwind CSS, and Blade. Features real-time tickers, order placement console (Market & Limit orders), holdings management, candlestick charts, and atomic ledger accounting in integer minor units.

## Features

- **Integer Monetary Precision**: All balances, order valuations, trade executions, and transaction records are maintained in integer cents (`cash_balance_cents`, `price_cents`, `total_cents`) to eliminate floating-point rounding errors.
- **Interactive Trading Terminal**: Multi-asset trading pair selector (BTC, ETH, SOL, NVDA, AAPL) with 24h highs, lows, volumes, and candlestick price charts.
- **Order Placement Engine**: Instant Market Buy/Sell orders and Open Limit orders with automatic balance checks, holding updates, and transaction logging in `DB::transaction()`.
- **Portfolio & Asset Valuation**: Real-time mark-to-market position calculations, average buy price tracking, and simulated cash deposits.
- **Watchlist & Order Book**: Quick watchlist pinning and execution feed.

## Screenshots

| Tampilan | Preview |
|---|---|
| **Dashboard Portofolio & Pasar**<br>Ringkasan total portofolio, saldo kas tersedia, nilai aset mark-to-market, dan ticker pasar 24 jam | ![Dashboard Portofolio & Pasar](docs/screenshots/01-dashboard-overview.png) |
| **Terminal Trading Interaktif**<br>Pemilihan instrumen aset, grafik candlestick interaktif, formulir order instan Market & Limit, serta streaming riwayat transaksi | ![Terminal Trading Interaktif](docs/screenshots/02-trading-terminal.png) |
| **Manajemen & Riwayat Order**<br>Daftar eksekusi order komprehensif dengan filter status dan tipe eksekusi | ![Manajemen & Riwayat Order](docs/screenshots/03-orders-history.png) |
| **Portofolio & Alokasi Modal**<br>Valuasi aset real-time, pantauan harga rata-rata beli, formulir deposit simulasi, dan buku besar transaksi | ![Portofolio & Alokasi Modal](docs/screenshots/04-portfolio-holdings.png) |

---

## Tech Stack

- **Backend**: Laravel 12.x (PHP 8.3+)
- **Authentication**: Laravel Breeze
- **Database**: SQLite (local & testing), PostgreSQL ready
- **Frontend**: Blade, Tailwind CSS, Vite

## Setup & Testing

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan test
```
