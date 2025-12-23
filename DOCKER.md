# Docker Setup Guide

Run the Crypto Exchange application using Docker containers.

## Prerequisites

- Docker Desktop installed
- Docker Compose installed
- Pusher account (free at [pusher.com](https://pusher.com))

---

## Quick Start

### 1. Create Environment File

```bash
cp .env.example .env
```

### 2. Configure Environment

Edit `.env` file with your Pusher credentials:

```env
# Database
DB_DATABASE=exchange_db
DB_USERNAME=laravel
DB_PASSWORD=secret

# Pusher Configuration
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_SECRET=your-pusher-app-secret
PUSHER_APP_CLUSTER=mt1

# Frontend
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_APP_KEY=your-pusher-app-key
VITE_PUSHER_APP_CLUSTER=mt1
```

### 3. Build and Start Containers

```bash
docker-compose up -d --build
```

### 4. Run Migrations & Seed Database

```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### 5. Generate App Key (if needed)

```bash
docker-compose exec app php artisan key:generate
```

---

## Access the Application

| Service | URL |
|---------|-----|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000/api |
| MySQL | localhost:3306 |

---

## Test Accounts

| Email | Password | USD Balance | BTC | ETH |
|-------|----------|-------------|-----|-----|
| paddy@example.com | Password@123 | $50,000 | 1.5 | 10 |
| sonik@example.com | Password@123 | $25,000 | 0.5 | 5 |

---

## Docker Commands

### Start containers
```bash
docker-compose up -d
```

### Stop containers
```bash
docker-compose down
```

### View logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f frontend
```

### Rebuild containers
```bash
docker-compose up -d --build
```

### Access container shell
```bash
# Backend
docker-compose exec app bash

# Frontend
docker-compose exec frontend sh
```

### Run Artisan commands
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
```

### Reset database
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Container Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Docker Network                        │
│                                                         │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐ │
│  │  Frontend   │    │   Nginx     │    │    MySQL    │ │
│  │  (Vue.js)   │    │  (Backend)  │    │   Database  │ │
│  │  Port 5173  │    │  Port 8000  │    │  Port 3306  │ │
│  └─────────────┘    └──────┬──────┘    └──────┬──────┘ │
│                            │                   │        │
│                     ┌──────▼──────┐           │        │
│                     │  Laravel    │◄──────────┘        │
│                     │  (PHP-FPM)  │                    │
│                     │  Port 9000  │                    │
│                     └─────────────┘                    │
└─────────────────────────────────────────────────────────┘
```

---

## Troubleshooting

### Container won't start
```bash
# Check logs
docker-compose logs app

# Rebuild
docker-compose down
docker-compose up -d --build
```

### Database connection error
```bash
# Ensure database is healthy
docker-compose ps

# Wait for MySQL to be ready, then run migrations
docker-compose exec app php artisan migrate:fresh --seed
```

### Permission issues
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Clear all caches
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

---

## Production Deployment

For production, update the following:

1. Use proper secrets management
2. Enable HTTPS with SSL certificates
3. Set `APP_ENV=production` and `APP_DEBUG=false`
4. Use a production-grade MySQL configuration
5. Configure proper logging and monitoring
