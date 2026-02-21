# Aurex API

> The fastest production-ready PHP REST API — raw PHP, clean architecture, zero bloat.

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## Why Aurex?

Speed comes from what you **don't** load.

Aurex boots with only what a request actually needs — no service container resolving hundreds of bindings, no autoloaded config files, no event listeners warming up in the background.

Every millisecond saved at the framework level compounds across every request, every user, every server.

**Built for speed, without sacrificing structure:**

- Zero framework overhead on every request
- Minimal dependency chain — only 3 Composer packages
- PDO with persistent-ready singleton connection
- No reflection, no magic methods, no runtime class scanning
- Middleware pipeline resolves in microseconds

---

## What's included

- **Front Controller** — Single entry point `public/index.php`
- **Router** — Method + URI matching, route groups, URL params `{id}`
- **Middleware Pipeline** — Global + per-route, chainable
- **Auth** — JWT (HS256) via `firebase/php-jwt`
- **RBAC** — Role-based access per route group
- **Validation** — Built-in rule engine (required, email, min, max, in, date, numeric, nullable)
- **Standard Response** — Consistent JSON shape across all endpoints
- **Logging** — JSON structured logs via Monolog
- **Rate Limiting** — IP-based throttle, file-backed (no Redis needed)
- **CORS** — Configurable per origin
- **Soft Delete** — `deleted_at` on all tables
- **PDO only** — Prepared statements everywhere, no raw string queries

---

## Project Structure

```
├── app/
│   ├── Contracts/        Interface definitions
│   ├── Core/             Kernel · Router · Request · Response · Database · Logger · MiddlewareChain
│   ├── Controllers/      Receive request → call service → return JSON
│   ├── Services/         Business logic (no DB here)
│   ├── Repositories/     Only layer that talks to MySQL
│   ├── Middleware/       Cors · Logging · RateLimit · Auth · Role
│   ├── Exceptions/       HttpException · ValidationException
│   └── DTO/              Typed data transfer objects
├── config/               app · database · cors
├── routes/               api.php
├── database/migrations/  SQL files, run in order
├── public/               index.php (entry point) · .htaccess
├── logs/                 app.log (auto-created)
└── storage/              rate limit store (auto-created)
```

---

## Quick Start

**Requirements:** PHP 8.1+, MySQL 8.0+, Composer, Apache/Nginx

```bash
# 1. Clone
git clone https://github.com/MorphEdit/aurex-api.git
cd aurex-api

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.example .env
# Edit .env — set DB credentials and JWT_SECRET

# 4. Run migrations (in order)
mysql -u root -p aurex < database/migrations/001_create_users_table.sql
mysql -u root -p aurex < database/migrations/002_create_departments_table.sql
mysql -u root -p aurex < database/migrations/003_create_positions_table.sql
mysql -u root -p aurex < database/migrations/004_create_employees_table.sql

# 5. Point your web server document root to /public
```

**First login** (seeded by migration 001):
```
email:    superadmin@aurex.local
password: password
```
> Change this immediately after first login.

---

## API Reference

All responses follow this shape:

```json
// Success
{ "success": true, "data": {}, "meta": {} }

// Error
{ "success": false, "error": { "code": "ERROR_CODE", "message": "..." } }

// Paginated
{ "success": true, "data": [], "meta": { "total": 100, "per_page": 15, "current_page": 1, "last_page": 7 } }
```

### Auth

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/auth/login` | ❌ | Login, receive JWT |
| GET | `/api/v1/auth/me` | ✅ | Current user profile |
| POST | `/api/v1/auth/logout` | ✅ | Logout (client discards token) |

```bash
# Login
curl -X POST /api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"superadmin@aurex.local","password":"password"}'

# Authenticated request
curl /api/v1/auth/me \
  -H "Authorization: Bearer <token>"
```

### Users `[super_admin only]`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/users` | List users (paginated, searchable) |
| POST | `/api/v1/users` | Create user |
| GET | `/api/v1/users/{id}` | Get user |
| PUT | `/api/v1/users/{id}` | Update user |
| DELETE | `/api/v1/users/{id}` | Soft delete user |

### Departments `[admin+]`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/departments` | List departments |
| POST | `/api/v1/departments` | Create department |
| GET | `/api/v1/departments/{id}` | Get department |
| PUT | `/api/v1/departments/{id}` | Update department |
| DELETE | `/api/v1/departments/{id}` | Soft delete department |

### Positions `[admin+]`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/positions` | List positions |
| POST | `/api/v1/positions` | Create position |
| GET | `/api/v1/positions/{id}` | Get position |
| PUT | `/api/v1/positions/{id}` | Update position |
| DELETE | `/api/v1/positions/{id}` | Soft delete position |

### Employees `[admin+]`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/employees` | List employees (filter by dept, position, status) |
| POST | `/api/v1/employees` | Create employee |
| GET | `/api/v1/employees/{id}` | Get employee (with dept + position name) |
| PUT | `/api/v1/employees/{id}` | Update employee |
| DELETE | `/api/v1/employees/{id}` | Soft delete employee |

**Employee filters:**
```
GET /api/v1/employees?search=john&dept_id=1&position_id=2&status=active&page=1&per_page=15
```

---

## Roles

| Role | Can do |
|---|---|
| `super_admin` | Everything — including managing system users |
| `admin` | Manage employees, departments, positions |

---

## Environment Variables

```env
APP_NAME=AurexAPI
APP_ENV=local           # local | production
APP_DEBUG=true          # true shows stack traces in errors

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aurex
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=             # Min 32 chars, random string
JWT_EXPIRY=3600         # Seconds

CORS_ALLOWED_ORIGINS=http://localhost   # Comma-separated or *

LOG_PATH=logs/app.log
LOG_LEVEL=debug         # debug | info | warning | error

RATE_LIMIT=60           # Requests per window
RATE_LIMIT_WINDOW=60    # Window in seconds
```

---

## Adding a New Endpoint

1. **Repository** — add query method in `app/Repositories/`
2. **Service** — add business logic in `app/Services/`
3. **Controller** — add method in `app/Controllers/`
4. **Route** — register in `routes/api.php`

No config files to update, no service providers, no magic. New endpoint is live in under 2 minutes.

---

## Extending Middleware

```php
// app/Middleware/MyMiddleware.php
class MyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        // before
        $next($request);
        // after
    }
}

// routes/api.php — attach to a route group
$router->group('/api/v1/something', [new MyMiddleware()], function ($router) {
    // ...
});
```

---

## Security

- All DB queries use **PDO prepared statements** — no SQL injection possible
- Passwords hashed with **bcrypt cost 12**
- JWT signed with **HS256**
- **CORS** restricted to configured origins
- **Rate limiting** on every request
- `APP_DEBUG=false` in production hides stack traces
- Soft delete — no data is permanently lost by accident

---

## Benchmark

Measured inside Docker container — PHP 8.1.34, MySQL 8.0, Nginx + PHP-FPM.
Run with `docker exec aurex_app php benchmark/benchmark.php http://nginx`

| Endpoint | min | avg | p95 | req/s |
|---|---|---|---|---|
| `POST /auth/login` | 58ms | 77ms | 95ms | 13 |
| `GET /auth/me` | 19ms | 29ms | 45ms | 34 |
| `GET /employees` (paginated) | 21ms | 31ms | 45ms | 32 |
| `GET /employees?search=` (filtered) | 21ms | 29ms | 42ms | 34 |

**Peak memory per benchmark run: 2 MB**

> Login is slower by design — bcrypt verification takes ~60ms as a security feature.
> Authenticated endpoints average **25ms** including JWT decode, middleware pipeline, and DB query.

### Production Performance

For production Linux deployments, enable OPcache to reduce PHP compilation overhead per request.
A pre-configured `opcache.ini` is included at `.docker/opcache.ini`.

Enable it in your `Dockerfile`:

```dockerfile
RUN docker-php-ext-install opcache
COPY .docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
```

Set `opcache.validate_timestamps=0` in production (files don't change between deploys).

---

## License

MIT — see [LICENSE](LICENSE)
