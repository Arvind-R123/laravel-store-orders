# Store Order & Inventory Mini-System

## Overview

Laravel 13.39 / PHP 8.4 application for an Indian retail counter: products, stock-safe orders, customer history, low-stock reporting, and queued confirmation logging.

### Tech stack

- PHP 8.4 with `pdo_mysql` and `pdo_sqlite`
- Laravel 13.39
- Composer 2.x
- MySQL 8.x for the application and concurrency demo
- SQLite in-memory database for tests
- Blade/Vite, Laravel queues, PHPUnit, and Pint

## Prerequisites

Install PHP 8.4 with `pdo_mysql` and `pdo_sqlite`, Composer 2.x, and MySQL 8.x. Verify:

```sh
php -v
composer -V
```

## Setup

Create an empty MySQL database, then configure its name, username, and password in `.env`:

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan queue:work
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`. Run the server and queue worker in separate terminals. MySQL is the primary application database; SQLite is configured by `phpunit.xml` for isolated tests.

## Configuration reference

Every key in `.env.example` is listed below; blank means Laravel's default or intentionally unset.

| Key | Default | Purpose |
|---|---|---|
| `APP_NAME` | `Laravel` | Application name |
| `APP_ENV` | `local` | Environment |
| `APP_KEY` | blank until generated | Encryption key |
| `APP_DEBUG` | `true` | Debug output |
| `APP_URL` | `http://localhost:8000` | Base URL |
| `APP_LOCALE` | `en` | Locale |
| `APP_FALLBACK_LOCALE` | `en` | Fallback locale |
| `APP_FAKER_LOCALE` | `en_US` | Faker locale |
| `APP_MAINTENANCE_DRIVER` | `file` | Maintenance driver |
| `LOG_CHANNEL` | `stack` | Default log channel |
| `LOG_STACK` | `single` | Stack channels |
| `LOG_DEPRECATIONS_CHANNEL` | `null` | Deprecation channel |
| `LOG_LEVEL` | `debug` | Minimum log level |
| `DB_CONNECTION` | `sqlite` | Driver; use `mysql` locally |
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `laravel` | Database name |
| `DB_USERNAME` | `root` | Database user |
| `DB_PASSWORD` | blank | Database password |
| `SESSION_DRIVER` | `database` | Session store |
| `SESSION_LIFETIME` | `120` | Lifetime in minutes |
| `SESSION_ENCRYPT` | `false` | Encrypt sessions |
| `SESSION_PATH` | `/` | Cookie path |
| `SESSION_DOMAIN` | unset | Cookie domain |
| `BROADCAST_CONNECTION` | `log` | Broadcast connection |
| `FILESYSTEM_DISK` | `local` | Filesystem disk |
| `QUEUE_CONNECTION` | `database` | Queue backend |
| `INVENTORY_LOW_STOCK_THRESHOLD` | `10` | Low-stock threshold |
| `ORDER_MAX_ITEMS` | `50` | Maximum order lines |
| `ORDER_MAX_LINE_QUANTITY` | `100` | Maximum line quantity |
| `ORDER_TRANSACTION_ATTEMPTS` | `3` | Transaction retries |
| `DEFAULT_PER_PAGE` | `15` | History page size |
| `STORE_CURRENCY` | `INR` | Currency code |
| `STORE_CURRENCY_SYMBOL` | `₹` | Display symbol |
| `STORE_TAX_ROUNDING` | `half_up` | Tax rounding |
| `CACHE_STORE` | `database` | Cache backend |
| `CACHE_PREFIX` | app-derived | Cache prefix |
| `MAIL_MAILER` | `log` | Mail transport |
| `MAIL_SCHEME` | unset | Mail scheme |
| `MAIL_HOST` | `127.0.0.1` | Mail host |
| `MAIL_PORT` | `2525` | Mail port |
| `MAIL_USERNAME` | unset | Mail username |
| `MAIL_PASSWORD` | unset | Mail password |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Sender address |
| `MAIL_FROM_NAME` | `${APP_NAME}` | Sender name |
| `VITE_APP_NAME` | `${APP_NAME}` | Frontend app name |

## API reference

All API routes are under `/api/v1`.

### Health

```sh
curl http://localhost:8000/api/v1/health
```

Success: `200 {"status":"ok"}`.

### Products and low stock

```sh
curl http://localhost:8000/api/v1/products
curl "http://localhost:8000/api/v1/products/low-stock?threshold=10"
```

Success: `200 {"data":[{"id":1,"name":"Rice","code":"RICE-1","price":"108.00","tax_percent":"5.00","stock":10}]}`. A negative threshold returns `422 {"message":"Invalid threshold"}`.

### Customer lookup

```sh
curl "http://localhost:8000/api/v1/customers?email=a%40example.com"
```

Success: `200 {"id":1,"name":"A","email":"a@example.com"}`; unknown email returns `200 null`. Invalid email returns `422` with `{ "message": "The given data was invalid.", "errors": { ... } }`.

### Create an order

```sh
curl -X POST http://localhost:8000/api/v1/orders -H "Content-Type: application/json" -d '{"customer":{"email":"a@example.com","name":"A"},"items":[{"product_id":1,"quantity":2}]}'
```

Success: `201 {"data":{"id":1,"order_number":"ORD-...","status":"confirmed","items":[...],"subtotal":"216.00","tax_total":"10.80","grand_total":"226.80"}}`. Insufficient stock returns `422 {"message":"Insufficient stock for product 1."}`. Invalid payloads return `422` with `{ "message": "The given data was invalid.", "errors": { ... } }`.

### Customer order history

```sh
curl "http://localhost:8000/api/v1/customers/orders?email=a%40example.com&page=1"
```

Success: `200 {"data":[...],"links":{...},"meta":{...}}`. Unknown email returns `404 {"message":"No query results for model [App\\Models\\Customer]."}`; invalid email returns `422` with `{ "message": "The given data was invalid.", "errors": { ... } }`.

## Tests

The suite uses `RefreshDatabase` with in-memory SQLite:

```sh
php artisan test
vendor/bin/pint
```

## Concurrency demo

Configure `.env` for MySQL, seed the database, and run:

```sh
php artisan orders:concurrency-demo --requests=5
```

Expected output:

```text
+--------------------+-------+
| status             | count |
+--------------------+-------+
| success            | 1     |
| insufficient_stock | 4     |
| error              | 0     |
+--------------------+-------+
final_stock=0
```

The command temporarily sets the selected product to one unit and restores its original stock afterwards, including on failure. SQLite is refused for this demo.

## Architecture and design decisions

```text
app/Actions/Orders       transactional order orchestration
app/Console/Commands     operational and concurrency commands
app/Http                 controllers, requests, and JSON resources
app/Jobs                 after-commit confirmation logging
app/Models               Eloquent models, relationships, casts, scopes
app/Services              pricing and order-number services
config/inventory.php     domain limits and store settings
database/                migrations, factories, and seeders
routes/                  web and API route definitions
resources/               Blade UI and frontend assets
tests/                   feature and unit tests
prompts/                 AI prompt notes
```

Orders use `lockForUpdate` in sorted product-id order to reduce deadlocks. A conditional `UPDATE` is the final guard against negative inventory. Items snapshot price and tax. Confirmation work is dispatched after commit. Money is stored as integer paise and formatted as fixed two-decimal strings at the API boundary.

## Assumptions

- INR is the only currency; tax is calculated per line with half-up rounding.
- Duplicate product lines merge; customers are reused by email without overwriting names.
- New customers require a name; orders start as `confirmed`; stock must never be negative.
- Low stock means strictly below the threshold.
- MySQL locking and conditional updates provide the production concurrency guarantee; SQLite is only for isolated tests.
- No authentication is included for this counter-system scope.
- The job writes a structured log entry; PDF emailing is out of scope.
- Products are seeded only; there is no CRUD.
- The wireframe is a layout reference only. Inconsistencies: line totals `108 + 54 = 162` but subtotal shown as `210`; balance `22.80` but denomination breakdown sums to `23`.

## Known limitations and next steps

There is no authentication, authorization, rate limiting, idempotency key, inventory ledger, returns/cancellations workflow, reservation expiry, external email delivery, or robust observability. The concurrency demo is diagnostic rather than a load test. Next steps are authentication, an inventory ledger, idempotent submission, cancellation/returns, real email delivery, and production metrics/tracing.

## AI usage and Prompt Log

The `/prompts` directory contains the prompt log. AI produced first drafts; I reviewed the implementation and ran every command. I caught the stub concurrency demo, the UI 100x price bug, and Boost configuration noise.
