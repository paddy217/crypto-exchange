# Crypto Exchange Mini App

A full-stack cryptocurrency exchange application built with Laravel API and Vue.js frontend, featuring real-time order matching and Pusher-based notifications.

## Tech Stack

- **Backend:** Laravel 12 (PHP)
- **Frontend:** Vue.js 3 (Composition API) + Tailwind CSS
- **Database:** MySQL / PostgreSQL
- **Real-time:** Pusher via Laravel Broadcasting
- **Authentication:** Laravel Sanctum

## Features

- User registration and authentication
- USD balance and crypto asset management
- Limit order placement (Buy/Sell)
- Order matching engine with 1.5% commission
- Real-time order match notifications
- Orderbook display (BTC/ETH)
- Order history with cancel functionality

## Project Structure

```
crypto-exchange/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Events/             # OrderMatched broadcast event
│   │   ├── Http/Controllers/   # API controllers
│   │   ├── Models/             # Eloquent models
│   │   └── Services/           # Order matching business logic
│   ├── database/migrations/    # Database schema
│   ├── routes/api.php          # API endpoints
│   └── routes/channels.php     # Broadcasting channels
│
└── frontend/                   # Vue.js SPA
    ├── src/
    │   ├── components/         # UI components
    │   ├── views/              # Page views
    │   ├── stores/             # Pinia state management
    │   ├── services/           # API & Echo services
    │   └── router/             # Vue Router config
    └── tailwind.config.js
```

## Prerequisites

- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `openssl`
- Composer
- Node.js 18+ & npm
- MySQL 8.0+ or PostgreSQL
- Pusher account (free tier available)

## Installation

### 1. Clone the Repository

```bash
cd crypto-exchange
```

### 2. Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment Variables

Edit `backend/.env`:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exchange_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Pusher (get credentials from pusher.com)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

# Frontend URL for CORS
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

### 4. Create Database

```sql
CREATE DATABASE exchange_db;
```

### 5. Run Migrations & Seed Data

```bash
php artisan migrate:fresh --seed
```

This creates two test users:
| Email | Password | USD Balance | BTC | ETH |
|-------|----------|-------------|-----|-----|
| paddy@example.com | Password@123 | $50,000 | 1.5 | 10 |
| sonik@example.com | Password@123 | $25,000 | 0.5 | 5 |

### 6. Frontend Setup

```bash
cd ../frontend

# Install dependencies
npm install
```

Edit `frontend/.env`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_APP_KEY=your_app_key
VITE_PUSHER_APP_CLUSTER=mt1
```

## Running the Application

### Start Backend Server

```bash
cd backend
php artisan serve
```
Backend runs at: `http://localhost:8000`

### Start Frontend Server

```bash
cd frontend
npm run dev
```
Frontend runs at: `http://localhost:5173`

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/register` | Register new user | No |
| POST | `/api/login` | User login | No |
| POST | `/api/logout` | User logout | Yes |
| GET | `/api/profile` | Get user profile & assets | Yes |
| GET | `/api/orders?symbol=BTC` | Get orderbook for symbol | Yes |
| POST | `/api/orders` | Create limit order | Yes |
| POST | `/api/orders/{id}/cancel` | Cancel open order | Yes |
| GET | `/api/user/orders` | Get user's order history | Yes |

### Create Order Request

```json
POST /api/orders
{
  "symbol": "BTC",
  "side": "buy",
  "price": 95000.00,
  "amount": 0.01
}
```

## Business Logic

### Order Matching Rules

- **Buy Order:** Matches with lowest-priced sell order where `sell.price <= buy.price`
- **Sell Order:** Matches with highest-priced buy order where `buy.price >= sell.price`
- Full match only (no partial fills)
- Orders from the same user cannot match

### Commission

- **Rate:** 1.5% of matched USD value
- **Calculation:** `commission = price × amount × 0.015`
- **Deducted from:** Buyer's total (seller receives `total - commission`)

### Balance Management

- **Buy Order:** USD deducted from balance when order placed
- **Sell Order:** Asset moved to `locked_amount` when order placed
- **Cancel:** Locked funds returned to available balance

## Real-time Events

When orders match, `OrderMatched` event broadcasts to both parties via private channels:

```javascript
// Channel: private-user.{userId}
// Event: order.matched
{
  "trade": {
    "id": 1,
    "symbol": "BTC",
    "price": "95000.00000000",
    "amount": "0.01000000",
    "total": "950.00000000",
    "commission": "14.25000000",
    "side": "buy",
    "created_at": "2025-12-19T12:00:00.000Z"
  },
  "user": {
    "id": 1,
    "balance": "49035.75000000"
  }
}
```

## Database Schema

### users
- `id`, `name`, `email`, `password`, `balance` (USD), `timestamps`

### assets
- `id`, `user_id`, `symbol`, `amount`, `locked_amount`, `timestamps`

### orders
- `id`, `user_id`, `symbol`, `side`, `price`, `amount`, `status` (1=open, 2=filled, 3=cancelled), `timestamps`

### trades
- `id`, `buy_order_id`, `sell_order_id`, `buyer_id`, `seller_id`, `symbol`, `price`, `amount`, `total`, `commission`, `timestamps`

## Troubleshooting

### Database Driver Error
```
could not find driver
```
Enable `pdo_mysql` in your `php.ini`:
```ini
extension=pdo_mysql
```

### CORS Issues
Ensure `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` are correctly set in `backend/.env`

### Pusher Not Working
1. Verify credentials in both `.env` files
2. Check browser console for WebSocket errors
3. Ensure `BROADCAST_CONNECTION=pusher` in backend

## License

This project is for assessment purposes.
