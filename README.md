# URL Shortener Service

A multi-tenant URL shortening service built with Laravel 12, featuring role-based access control.

## Quick Setup

1. **Install dependencies:**
```bash
composer install
```

2. **Configure environment:**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Setup database in `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

4. **Run migrations and seeders:**
```bash
php artisan migrate --seed
```

5. **Start server:**
```bash
php artisan serve
```

Visit `http://localhost:8000/login`

## Login

**Default SuperAdmin Credentials:**
- Email: `superadmin@supderadmin.com`
- Password: `admin123`

After login, you can:
- Invite companies (SuperAdmin)
- Invite team members (Admin)
- Generate short URLs (Admin/Member)

## Testing

Run tests:
```bash
php artisan test
```

**Test Coverage:**
- SuperAdmin cannot create short URLs
- Admin and Member can create short URLs
- Admin sees only URLs from their company
- Member sees only their own URLs
- Short URLs require authentication to access



