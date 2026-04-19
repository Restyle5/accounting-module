# Accounting Module — Laravel + MySQL Docker Setup

## Stack

| Service     | Image           | Port (Host) |
|-------------|-----------------|-------------|
| PHP 8.3-FPM | php:8.3-fpm     | —           |
| Nginx       | nginx:alpine    | 8000        |
| MySQL       | mysql:8.0       | 3306        |
| phpMyAdmin  | phpmyadmin      | 8080        |

---

## Project Structure

```
accounting-module/
├── docker-compose.yml
├── Dockerfile
├── .env                        # Docker environment variables
├── src/                        # Laravel app lives here
└── docker/
    ├── nginx/conf.d/app.conf   # Nginx virtual host
    ├── php/local.ini           # PHP settings
    └── mysql/my.cnf            # MySQL settings
```

---

## Database Credentials

| Setting   | Value               |
|-----------|---------------------|
| Host      | `db` (inside Docker) / `localhost` (external) |
| Port      | `3306`              |
| Database  | `accounting_module` |
| Username  | `admin`             |
| Password  | `password`          |
| Root PW   | `password`          |

---

## Pulling the Project (Existing Codebase)

Use this when cloning the repo for the first time.

### 1. Build and start containers
```bash
docker compose up -d --build
```

### 2. Install Composer dependencies
```bash
docker compose exec app composer install
```

### 3. Set up Laravel .env
```bash
docker compose exec app cp .env.example .env
```

Edit `src/.env` and set:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=accounting_module
DB_USERNAME=admin
DB_PASSWORD=password
```

### 4. Generate app key
```bash
docker compose exec app php artisan key:generate
```

### 5. Run migrations
```bash
docker compose exec app php artisan migrate
```

### 6. Fix permissions (if you hit storage errors)
```bash
docker compose exec -u root app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

---

## Fresh Laravel Install (First Time Only)

Use this only if starting from scratch with no existing Laravel code in `src/`.

### 1. Build and start containers
```bash
docker compose up -d --build
```

### 2. Install Laravel into a temp folder
> Composer requires an empty directory. Since Docker volume mounts are never truly empty, install to `/tmp` first then move.
```bash
docker compose exec app composer create-project laravel/laravel /tmp/laravel --prefer-dist
```

### 3. Fix ownership before copying
```bash
docker compose exec -u root app chown -R laravel:laravel /var/www
```

### 4. Move Laravel files into /var/www
```bash
docker compose exec app cp -r /tmp/laravel/. /var/www/
docker compose exec app rm -rf /tmp/laravel
```

### 5. Fix storage permissions
```bash
docker compose exec -u root app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### 6. Set up Laravel .env
```bash
docker compose exec app cp .env.example .env
```

Edit `src/.env`:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=accounting_module
DB_USERNAME=admin
DB_PASSWORD=password
```

### 7. Generate app key
```bash
docker compose exec app php artisan key:generate
```

### 8. Run migrations
```bash
docker compose exec app php artisan migrate
```

---

## Access Points

- 🌐 Laravel App → http://localhost:8000
- 🗄️ phpMyAdmin → http://localhost:8080

---

## Common Commands

```bash
# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan make:model Invoice -mcr
docker compose exec app php artisan tinker

# Composer
docker compose exec app composer require vendor/package
docker compose exec app composer install

# Container management
docker compose up -d            # Start containers
docker compose down             # Stop containers (keeps DB data)
docker compose down -v          # Stop containers + wipe DB volume ⚠️
docker compose up -d --build    # Rebuild and start

# Logs
docker compose logs -f          # All containers
docker compose logs -f app      # App only
```

---

## Making Changes to docker-compose.yml

1. Bring containers down first:
```bash
docker compose down
```
2. Make your changes
3. Bring back up:
```bash
docker compose up -d --build
```

> ⚠️ Only use `down -v` if you intentionally want to wipe the database (e.g. resetting credentials). Otherwise always use plain `down`.

---

## Adding Composer Packages

Most packages just need:
```bash
docker compose exec app composer require vendor/package
```

No Docker config changes needed unless the package requires a PHP extension not already installed. Currently installed extensions: `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`. If a new extension is needed, add it to the `Dockerfile` and rebuild with `docker compose up -d --build`.

---

## Troubleshooting

**Migration fails with "name resolution" error**
The app container can't reach the db container. Make sure all containers are running (`docker compose ps`) and that `DB_HOST=db` in `src/.env`. If credentials changed, wipe the volume and recreate: `docker compose down -v && docker compose up -d`.

**Permission denied on storage or bootstrap/cache**
```bash
docker compose exec -u root app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

**MySQL has old credentials after changing password**
Docker volumes persist old data. Wipe and recreate:
```bash
docker compose down -v
docker compose up -d
```

**`sudo` not working inside container**
Use `-u root` instead:
```bash
docker compose exec -u root app <command>
```