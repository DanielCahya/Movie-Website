# MovieWebsite

A Laravel-based project for managing and displaying movie-related information.

## Prerequisites

Before setting up the project, ensure the following tools are installed on your system:

- **PHP** (version 8.1 or higher)
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

---

### Step 5: Generate the Application Key

Run the following command to generate a unique application key:

```bash
php artisan key:generate
```

---

### Step 6: Run Database Migrations

Ensure your database is running and properly configured in `.env`, then run:

```bash
php artisan migrate
```

---

### Step 7: Build Frontend Assets

If the project uses Laravel Mix or similar tools, compile the assets:

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

---

## Contributors

- **Daniel Cahya Kurniawan**
