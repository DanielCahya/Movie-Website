# Galiwe 🎬

A modern, responsive Laravel-based web application for discovering movies, TV shows, and anime. Powered by the TMDB API and optimized with Redis caching.

## Features

- **TMDB API Integration**: Real-time data for popular, top-rated, and currently airing movies and TV shows.
- **Dedicated Anime Section**: Specialized filtering for animated content.
- **Fast Caching**: Utilizes Redis for high-performance API response caching.
- **Dynamic Search**: Real-time search powered by Laravel Livewire.
- **Admin Dashboard**: Manage users and application settings.

## Tech Stack

- **Backend**: Laravel, PHP 8.1+
- **Frontend**: Tailwind CSS, Laravel Livewire
- **Database**: MySQL
- **Caching**: Redis
- **APIs**: The Movie Database (TMDB) API

## Prerequisites

Before setting up the project, ensure the following tools are installed on your system:

- **PHP** (version 8.1 or higher) with `fileinfo` and `curl` extensions enabled
- **Composer**
- **Node.js** and **npm**
- **MySQL** or any supported database
- **Git**
- TMDB API token
---

## Installation

Follow these steps to set up the project on your local machine:

### Step 1: Clone the Repository

```bash
# Clone the repository
git clone <repository-url>

# Navigate to the project directory
cd MovieWebsite
```

---

### Step 2: Install PHP Dependencies

Ensure Composer is installed, then run:

```bash
composer install
```

---

### Step 3: Install JavaScript Dependencies

Ensure npm (or Yarn) is installed, then run:

```bash
npm install
```

---

### Step 4: Configure the `.env` File

- Copy the example environment file:
  ```bash
  cp .env.example .env
  ```
- Open the `.env` file and update the following settings:
  - `DB_CONNECTION` (e.g., `mysql`)
  - `DB_HOST` (e.g., `127.0.0.1` or your database server)
  - `DB_PORT` (default: `3306`)
  - `DB_DATABASE` (your database name)
  - `DB_USERNAME` (your database username)
  - `DB_PASSWORD` (your database password)
  - `TMDB_TOKEN` (your-tmdb-token)
  - `CACHE_DRIVER=redis`
  - `REDIS_CLIENT=predis`

---

### Step 5: Generate the Application Key

Run the following command to generate a unique application key:

```bash
php artisan key:generate
```

---

### Step 6: Set up Database

Ensure your database is running and properly configured in `.env`. You can either run the migrations:

```bash
php artisan migrate
```

Alternatively, you can import the provided `galiwe.sql` dump file directly into your database using your preferred database manager (e.g. phpMyAdmin, Laragon) if you want the sample users and data pre-populated.

---

### Step 7: Build Frontend Assets

This project uses Laravel Mix to compile Tailwind CSS and Javascript assets. You must compile the assets to generate the final CSS:

```bash
# For development mode
npm run dev

# For production mode
npm run prod
```

---

### Step 8: Run the Application

For local development, use the built-in Laravel server:

```bash
php artisan serve
```

By default, the application will be available at `http://127.0.0.1:8000`.

---

## Deployment

To deploy the application, ensure the following:

1. Configure a web server (e.g., Apache or Nginx).
2. Use environment-specific settings in the `.env` file.
3. Set proper file permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
4. Optimize the application for production:
   ```bash
   php artisan optimize
   ```

---

## Common Issues

### Missing Dependencies

If the `vendor` or `node_modules` folders are missing, ensure you run:

- `composer install` for PHP dependencies.
- `npm install` for JavaScript dependencies.

### Permissions Issues

Ensure the `storage` and `bootstrap/cache` directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
```

### Database Connection Errors

Double-check the database configuration in your `.env` file and ensure the database service is running.

### cURL error 60: SSL certificate problem

If you are developing on Windows and see an SSL certificate error when the app tries to connect to the TMDB API:
1. Download `cacert.pem` from [curl.se](https://curl.se/ca/cacert.pem).
2. Save it to your PHP installation directory (e.g., `C:\php\cacert.pem`).
3. Open your `php.ini` file, find `;curl.cainfo =` and change it to `curl.cainfo = "C:\php\cacert.pem"`.
4. Restart your web server (Laragon/XAMPP).

### Redis class not found

If you see a "Class 'Redis' not found" error, make sure your `.env` is configured to use the `predis` client (since native PHP Redis extensions can be difficult to install on Windows):
```env
CACHE_DRIVER=redis
REDIS_CLIENT=predis
```
And ensure you have run `composer install` to download the `predis/predis` package.

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Contributors

- **Daniel Cahya Kurniawan**
