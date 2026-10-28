# Store Order & Inventory Mini-System

Laravel 13.39 / PHP 8.4 application for an Indian retail counter: products, stock-safe orders, customer history, low-stock reporting, and queued confirmation logging.

## Setup

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan queue:work
```

MySQL is the primary application database. SQLite is configured through `phpunit.xml` for the fast, isolated in-memory test suite; PostgreSQL is also compatible when the standard DB variables are changed. `QUEUE_CONNECTION=database` is recommended in `.env`.

## Configuration

`INVENTORY_LOW_STOCK_THRESHOLD` (10), `ORDER_MAX_ITEMS` (50), `ORDER_MAX_LINE_QUANTITY` (100), `ORDER_TRANSACTION_ATTEMPTS` (3), `DEFAULT_PER_PAGE` (15), `STORE_CURRENCY` (INR), `STORE_CURRENCY_SYMBOL` (₹), `STORE_TAX_ROUNDING` (half_up), and Laravel's standard DB, queue, log, and mail keys are configurable through `.env`.

## API

`POST /api/v1/orders` with `{"customer":{"email":"a@example.com","name":"A"},"items":[{"product_id":1,"quantity":2}]}` returns `201` and the order with fixed two-decimal strings. `GET /api/v1/products`, `GET /api/v1/products/low-stock?threshold=10`, and `GET /api/v1/customers/orders?email=a@example.com` provide catalog, low-stock, and paginated history. Validation and stock failures return `422` with `{message, errors}`; unknown customer history returns Laravel's JSON `404`; unexpected errors hide details when `APP_DEBUG=false`.

## Design and assumptions

The full assumptions are: money is integer minor units (paise); INR is the only currency; tax is calculated per line with half-up rounding; order items snapshot price/tax; duplicate lines merge by product; existing customers are reused by email without overwriting names; new customers require a name; low-stock means strictly below threshold; orders start `confirmed`; stock must never become negative; inventory and order creation share one transaction; MySQL row-level locking and conditional updates provide the production concurrency guarantee; SQLite serializes writers and is only for isolated tests; no authentication is included for this counter-system scope; confirmation dispatch occurs after commit; and the job writes a structured log entry rather than sending email.

## Architecture

`app/Actions/Orders` owns transactional orchestration; `app/Services` owns pure pricing; `app/Models` contains relationships/casts/scopes; requests/resources/controllers define the API boundary; `app/Jobs` handles post-commit confirmation. `database/` contains schema, factories, and realistic seed data. Prompt notes are in `/prompts`.

## Tests and concurrency

Run `php artisan test` and `vendor/bin/pint --dirty --format agent`. The suite uses `RefreshDatabase` and in-memory SQLite. For the concurrency demo: configure `.env` for MySQL or PostgreSQL, run `php artisan migrate --seed`, ensure the selected product exists, then run `php artisan orders:concurrency-demo --product=<id> --requests=5`. The command resets the selected product to one unit and expects one success, four insufficient-stock responses, no errors, and final stock of zero. SQLite is intentionally refused for this demo.

## Limitations / next steps

The API has no authentication, authorization, rate limiting, idempotency keys, or duplicate-retry protection. There is no inventory ledger, returns/cancellations workflow, reservation expiry, or external email delivery. Observability is limited to application logs. The concurrency demo is diagnostic rather than a load test and requires row-level locking.

## Wireframe inconsistencies

The wireframe's product prices, totals, stock values, and customer/order examples are inconsistent with one another. The persisted catalog, configured tax rules, and calculated totals are authoritative; wireframe-only values and PDF/email delivery are outside this implementation's scope.
