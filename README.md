# Store Order & Inventory Mini-System

Laravel 13.17 / PHP 8.3+ application for an Indian retail counter: products, stock-safe orders, customer history, low-stock reporting, and queued confirmation logging.

## Setup

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan queue:work
```

SQLite is the default; MySQL/PostgreSQL work by changing the standard DB variables. `QUEUE_CONNECTION=database` is recommended in `.env`.

## Configuration

`INVENTORY_LOW_STOCK_THRESHOLD` (10), `ORDER_MAX_ITEMS` (50), `ORDER_MAX_LINE_QUANTITY` (100), `ORDER_TRANSACTION_ATTEMPTS` (3), `DEFAULT_PER_PAGE` (15), `STORE_CURRENCY` (INR), `STORE_CURRENCY_SYMBOL` (₹), `STORE_TAX_ROUNDING` (half_up), and Laravel's standard DB, queue, log, and mail keys are configurable through `.env`.

## API

`POST /api/v1/orders` with `{"customer":{"email":"a@example.com","name":"A"},"items":[{"product_id":1,"quantity":2}]}` returns `201` and the order with fixed two-decimal strings. `GET /api/v1/products`, `GET /api/v1/products/low-stock?threshold=10`, and `GET /api/v1/customers/orders?email=a@example.com` provide catalog, low-stock, and paginated history. Validation and stock failures return `422` with `{message, errors}`; unknown customer history returns Laravel's JSON `404`; unexpected errors hide details when `APP_DEBUG=false`.

## Design and assumptions

Money is integer minor units (paise), tax is calculated per line with half-up rounding, and order items snapshot price/tax. Duplicate lines merge by product. Existing customers are reused without overwriting names. Low-stock means strictly below threshold. Orders start `confirmed`; no authentication is included for this counter-system scope. INR is the single currency. Conditional stock updates plus row locks prevent overselling; SQLite serializes writers, while MySQL is the true row-lock demonstration. The wireframe's inconsistent numbers are ignored in favor of real calculations. PDF/email delivery is out of scope; the job writes a structured log entry.

## Architecture

`app/Actions/Orders` owns transactional orchestration; `app/Services` owns pure pricing; `app/Models` contains relationships/casts/scopes; requests/resources/controllers define the API boundary; `app/Jobs` handles post-commit confirmation. `database/` contains schema, factories, and realistic seed data. Prompt notes are in `/prompts`.

## Tests and concurrency

Run `php artisan test` and `vendor/bin/pint`. The intended suite uses RefreshDatabase and in-memory SQLite. A production concurrency stress command should target MySQL with one remaining unit; the conditional update is the deterministic guard. This environment currently lacks `pdo_sqlite`, so migrations/tests could not execute here.

## Limitations / next steps

Add authentication, idempotency keys, returns/cancellations, an inventory ledger, rate limiting, and richer observability. Laravel Boost installation was attempted but Packagist was unreachable, so no Boost-specific generated guidance was available.
