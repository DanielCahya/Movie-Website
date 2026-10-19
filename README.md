# MovieWebsite (Galiwe) 🎬

A modern, responsive Laravel-based web application for discovering movies, TV shows, and anime. Powered by the TMDB API and optimized with Redis caching.

## Features

- **TMDB API Integration**: Real-time data for popular, top-rated, and currently airing movies and TV shows.
- **Dedicated Anime Section**: Specialized filtering for animated content.
- **Fast Caching**: Utilizes Redis for high-performance API response caching.
- **Dynamic Search**: Real-time instant search powered by Laravel Livewire (`Search` component).
- **Admin Dashboard**: Manage users and roles with granular policies and authorization gates.
- **Community Forum & Comments**: Discussions, nested comments, and like/dislike reactions.

## Tech Stack

- **Backend**: Laravel 9, PHP 8.2+
- **Frontend**: Tailwind CSS, Alpine.js, Laravel Livewire
- **Database**: MySQL
- **Caching**: Redis (predis)
- **APIs**: The Movie Database (TMDB) API

## Prerequisites

Before setting up the project, ensure the following tools are installed on your system:

- **PHP** (version 8.2 or higher) with `fileinfo`, `pdo_mysql`, `curl`, and `mbstring` extensions enabled
- **Composer**
- **Node.js** and **npm**
- **MySQL** or any supported database
- **Git**
- **TMDB API Read Access Token** (Bearer token)

---

## Installation

Follow these steps to set up the project on your machine:

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd MovieWebsite
```

---

### Step 2: Install PHP & JS Dependencies

```bash
composer install
npm install
```

---

### Step 3: Configure the `.env` File

- Copy the example environment file:
  ```bash
  cp .env.example .env
  ```
- Generate an application key:
  ```bash
  php artisan key:generate
  ```
- Open `.env` and set:
  - `DB_DATABASE=galiwe`
  - `DB_USERNAME=your_db_username`
  - `DB_PASSWORD=your_db_password`
  - `TMDB_TOKEN=your_tmdb_bearer_token`
  - `CACHE_DRIVER=redis`
  - `REDIS_CLIENT=predis`

---

### Step 4: Run Migrations & Storage Link

Run the standard Laravel migrations:
```bash
php artisan migrate
php artisan storage:link
```

> [!CAUTION]
> **Production Notice:** Do **NOT** import `galiwe.sql` into a production database. `galiwe.sql` contains sample development users with pre-seeded test accounts. Always run `php artisan migrate` on production.

---

### Step 5: Build Frontend Assets

```bash
# For development
npm run dev

# For production
npm run prod
```

---

### Step 6: Run the Application

```bash
php artisan serve
```

Default URL: `http://127.0.0.1:8000`.

---

## Production Deployment Checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
2. Ensure `APP_URL=https://yourdomain.com` and `SESSION_SECURE_COOKIE=true`.
3. Run storage symlink: `php artisan storage:link`.
4. Cache configuration, routes, and views:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Run migrations:
   ```bash
   php artisan migrate --force
   ```
6. Set log rotation and level: `LOG_LEVEL=warning`.

---

## Legal & TMDB Attribution

This product uses the TMDB API but is not endorsed or certified by TMDB. All movie and TV show data, posters, and backdrops are provided courtesy of [The Movie Database](https://www.themoviedb.org/).
