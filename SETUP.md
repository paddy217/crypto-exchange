# How to Start the Project

## Prerequisites

- PHP 8.2+ (with `pdo_mysql` extension enabled)
- Composer
- Node.js 18+
- MySQL Server
- Pusher Account (free at [pusher.com](https://pusher.com))

---

## Step 1: Database Setup

Create a MySQL database:

```sql
CREATE DATABASE exchange_db;
```

---

## Step 2: Backend Setup

```bash
# Navigate to backend folder
cd backend

# Install dependencies
composer install

# Configure environment
cp .env.example .env
```

Edit `backend/.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exchange_db
DB_USERNAME=root
DB_PASSWORD=your_password

BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=mt1

FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

Run migrations and seed:

```bash
php artisan migrate:fresh --seed
```

Start backend server:

```bash
php artisan serve
```

Backend will run at: **http://localhost:8000**

---

## Step 3: Frontend Setup

Open a new terminal:

```bash
# Navigate to frontend folder
cd frontend

# Install dependencies
npm install
```

Edit `frontend/.env` file:

```env
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_APP_KEY=your_pusher_app_key
VITE_PUSHER_APP_CLUSTER=mt1
```

Start frontend server:

```bash
npm run dev
```

Frontend will run at: **http://localhost:5173**

---

## Step 4: Access the Application

Open your browser and go to:

| Page | URL |
|------|-----|
| Login | http://localhost:5173/login |
| Register | http://localhost:5173/register |
| Dashboard | http://localhost:5173/dashboard |

---

## Test Accounts

After running `php artisan migrate:fresh --seed`, these accounts are available:

| Email | Password | USD Balance | BTC | ETH |
|-------|----------|-------------|-----|-----|
| paddy@example.com | Password@123 | $50,000 | 1.5 | 10 |
| sonik@example.com | Password@123 | $25,000 | 0.5 | 5 |

---

## Quick Start Commands

**Terminal 1 (Backend):**
```bash
cd backend
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
cd frontend
npm run dev
```

---

## Troubleshooting

### "could not find driver" error
Enable `pdo_mysql` in your `php.ini` file:
```ini
extension=pdo_mysql
```

### CORS errors
Make sure `FRONTEND_URL=http://localhost:5173` is set in `backend/.env`

### Pusher not working
1. Verify Pusher credentials in both `.env` files
2. Make sure `BROADCAST_CONNECTION=pusher` in backend
