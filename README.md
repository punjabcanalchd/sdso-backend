# SDSO Backend

Laravel backend service for the SDSO project.

## Features

- Laravel application structure
- Environment-based configuration
- REST API endpoints
- Easy local development setup

## Prerequisites

- PHP 8.4+ or the runtime required by this project
- Composer
- Node.js and npm for frontend asset tooling, if used
- Access to the required database and external services

## Setup

1. Clone the repository.
2. Install PHP dependencies:

```bash
composer install
```

3. Copy the environment file and configure the required variables:

```bash
cp .env.example .env
```

4. Generate the application key:

```bash
php artisan key:generate
```

5. Run database migrations and seed role categories:

```bash
php artisan migrate
php artisan db:seed --class=RoleCategorySeeder
```

6. Start the application:

```bash
php artisan serve
```

## Project Structure

This project follows the standard Laravel structure:

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
```

## Common Scripts

```bash
php artisan serve     # start development server
php artisan migrate   # run database migrations
php artisan test      # run tests
```

## Environment Variables

Set the required values in `.env` before running the app. Common variables may include:

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Troubleshooting

- Verify the `.env` file exists and contains valid values.
- Ensure Composer dependencies are installed.
- Confirm the database or external service is reachable.

## License

Add your project license here.

## PHP Redis
Install PHP redis from below link
https://pecl.php.net/package/redis/6.3.0/windows
Extract Zip and copy php_redis.dll in your php ext folder
then open php.ini file add extension their as below
extension=redis
Update you .env file with below parameters and retart terminal


CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

## Set Up Redis server on Windows
1\\. Run PowerShell as Administrator 
 
Then execute:
wsl --install -d Ubuntu 
check the ubuntu in your system by ubuntu icon then start ubuntu form there.
 
sudo apt update
sudo apt install redis-server -y
 
sudo service redis-server start
redis-server --version

## Role Based Access Control (Spatie)

This project uses [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) for handling roles and permissions.

### Setup and Installation
1. Install the package via composer (if not already installed run "composer update" command):
   ```bash
   composer require spatie/laravel-permission
   ```
2. Publish the configuration and migration files:
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   ```
3. Run the following specific migrations to create the required tables and map your existing user roles:
   ```bash
   php artisan migrate --path=database/migrations/2026_06_11_175045_create_permission_tables.php
   php artisan migrate --path=database/migrations/2026_06_11_175550_map_existing_user_roles_to_spatie_roles.php
   php artisan migrate --path=database/migrations/2026_06_30_120000_link_users_role_id_to_spatie_roles_table.php
   ```

   The third migration (`2026_06_30_120000`) does the following:
   - Drops the old foreign key on `users.role_id` that pointed to the legacy `user_roles` table (if present).
   - Updates every user's `role_id` to point to the corresponding Spatie `roles` record (matched by name).
   - Adds a new foreign key on `users.role_id` → `roles.id`.
php artisan migrate --path=database\migrations\2026_07_16_052808_create_role_categories_table.php

### Usage Workflow

We have a centralized command to sync permissions based on our configured modules.

1. **Add a new module:** 
   Open `config/modules.php` and add your module name to the array.
   ```php
   return [
       'users',
       'products', // Example new module
   ];
   ```

2. **Sync permissions:**
   Run the following artisan command to automatically create `create`, `read`, `update`, and `destroy` permissions for your newly added module:
   ```bash
   php artisan permissions:sync
   ```
   *This command is safe to run multiple times without creating duplicates.*

