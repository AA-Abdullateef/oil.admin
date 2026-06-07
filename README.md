# Oil Admin

Oil Admin is a Laravel 12 application for managing an oil/energy investment platform. It includes an admin web panel and a versioned JSON API for user onboarding, KYC, assets, deposits, withdrawals, trades, holdings, notifications, and transaction history.

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum
- MySQL or SQLite
- Vite frontend build pipeline
- Database queue driver

## Main Features

- Admin authentication and role/permission management
- User registration, login, logout, password reset, and token authentication
- KYC submission, review, approval, rejection, and request-more-info flow
- Asset and payment sub-method management
- Deposit proof upload and admin completion/cancellation
- Withdrawal destination capture and admin processing/cancellation
- Ledger-based balances, holdings, trades, and transaction history
- Public platform settings, limits, countries, and states
- Notifications and audit logs
- Seeded admin, roles, permissions, assets, top-level payment methods, settings, and test users

## Setup

1. Install dependencies:

```bash
composer install
npm install
```

2. Create environment file and app key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure `.env`:

```env
APP_NAME="Oil Admin"
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oil_admin
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

SQLite also works for local testing:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

4. Run migrations and seeders:

```bash
php artisan migrate --seed
php artisan storage:link
```

5. Build frontend assets:

```bash
npm run build
```

6. Start the app:

```bash
php artisan serve
php artisan queue:work
```

## Seeded Access

Admin panel:

- URL: `/admin/login`
- Email: `superadmin@gmail.com`
- Password: `password`

API test users:

- `ada@example.com` / `password`
- `tunde@example.com` / `password`
- `maryam@example.com` / `password`

The seed data includes active assets, top-level payment methods, sample payment sub-methods, verified user profiles, a completed deposit, a pending withdrawal, and test proof references from `database/seeders/files`. Admins manage payment sub-method destinations from the admin panel.

## API

All API routes are under:

```text
/api/v1
```

Authentication uses Sanctum bearer tokens. Login and register return `data.access_token`; pass it as:

```http
Authorization: Bearer <token>
```

Important route groups:

- Public: register, login, forgot/reset password, settings, countries, states
- Authenticated: logout, me, profile, KYC status/submission, assets, methods, notifications
- KYC-gated: balances, deposits, withdrawals, trades, holdings, transactions

Postman artifacts are included in `postman/`:

- `postman/Oil_Admin_API.postman_collection.json`
- `postman/Oil_Admin_Local.postman_environment.json`

Import both files, select the environment, run `Auth / Login - Test User`, then continue with authenticated requests.

Frontend handoff documentation is available at `docs/API_FRONTEND_HANDOFF.md`. It documents every API endpoint, auth/KYC requirements, payloads, response shapes, file handling, errors, and the recommended Postman run order.

## Admin Workflows

- Admins with `manage_users` can verify a user's email from the Users list or user detail page. This sets `email_verified_at`; API user payloads expose both `email_verified` and `email_verified_at`.
- Deposits start as `pending`; admins can complete or cancel them.
- Withdrawals start as `pending`; admins can process them with payment evidence or cancel them with a reason.
- Payment destinations are no longer platform settings. Admins manage destination details under Finance / Payment sub-methods. Top-level methods are seeded categories such as Bank Transfer and Cryptocurrency; sub-methods carry bank account, wallet, network, and instruction fields.
- Balances are calculated from ledger transactions whose statuses affect balances.
- Super admin bypasses permission checks. Other roles must have explicit permission slugs assigned.

## File Handling

- Deposit proofs and withdrawal payout proofs are viewed through authenticated application routes, not by guessing `/storage/...` paths.
- API users can view only their own transaction proofs through the `proof_url` returned on deposit and withdrawal resources.
- Admin users with transaction permissions can view deposit and withdrawal proofs from the admin panel.
- KYC documents are stored on the private disk and are shown only inside the admin KYC review flow.
- Seeded test files are copied from `database/seeders/files` into `storage/app/public/proofs/testing` during seeding.

## Testing And Verification

Run the automated tests:

```bash
php artisan test
```

Run a clean local seed check without touching MySQL:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate:fresh --seed --force
```

On Windows PowerShell:

```powershell
$env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'; php artisan migrate:fresh --seed --force
```

## Production Notes

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Use a real queue worker/supervisor for queued notifications and jobs.
- Configure mail, database, cache, and storage drivers for the target environment.
- Review active payment sub-method destination details before launch.
- Rotate seeded passwords and remove or disable test accounts before production use.
