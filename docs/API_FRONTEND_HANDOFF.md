# Oil Admin API Frontend Handoff

Base URL:

```text
{{base_url}}/api/v1
```

Local default:

```text
http://127.0.0.1:8000/api/v1
```

## Frontend Integration Rules

- Use `Accept: application/json` on every API request.
- For JSON payloads, also send `Content-Type: application/json`.
- For uploads, send `multipart/form-data`.
- Successful JSON responses now use this envelope:

```json
{
  "success": true,
  "message": "Human-readable status message.",
  "data": {}
}
```

- Error JSON responses include `success: false`, a `message`, and may include `errors`, `error`, `kyc_url`, `country`, or `meta` depending on the endpoint.
- Login and register return `data.access_token` and `data.token_type`. Store `data.access_token` securely and send:

```http
Authorization: Bearer <token>
```

- Run bootstrap calls after login: `GET /me`, `GET /profile`, `GET /kyc/status`, `GET /assets`, `GET /methods`, `GET /settings`.
- Financial endpoints require verified KYC. If the API returns `error: KYC_NOT_VERIFIED`, route the user to the KYC flow.
- Do not build file URLs from raw `proof` paths. Use `proof_url`.
- Proof endpoints return binary file responses. Open in a new tab or download as a blob.

## Latest Endpoint Info Update

Affected frontend contract changes:

| Endpoint | Change |
| --- | --- |
| All JSON API endpoints except proof file downloads | Successful responses now include top-level `success`, `message`, and `data`. Paginated/list extras such as `meta` remain top-level. |
| API error responses | API exception handlers now include `success: false`; validation still includes `errors`. |
| `POST /register` | Request body changed from `name` to required `first_name` and `last_name`. Response token changed from `data.token` to `data.access_token`; `data.token_type` is `Bearer`. |
| `POST /login` | Response token changed from `data.token` to `data.access_token`; `data.token_type` is `Bearer`. |
| `GET /me` | User payload now includes `first_name`, `last_name`, `email_verified`, `email_verified_at`, and `updated_at` in addition to `name`. |
| `GET /dashboard` | `data.user` now uses the full `UserResource`, so it includes `first_name`, `last_name`, `email_verified`, `email_verified_at`, `profile`, and `updated_at` where loaded. |
| `GET /profile` | Profile payload is expanded with IDs, nested `user`, KYC review/document fields, and timestamps. |
| `PUT /profile` | Now accepts user fields `first_name`, `last_name`, `email`, and `phone` alongside profile fields. Response returns the expanded profile resource. |
| `GET /countries/{countrySlug}/states` | Still returns `country` as a top-level extra alongside the new success envelope. |
| List endpoints with pagination metadata | `meta` remains top-level beside `success`, `message`, and `data`. Affected: `/balances/transactions`, `/transactions`, `/notifications`. |
| `GET /deposits/{deposit}/proof`, `GET /withdrawals/{withdrawal}/proof` | Unchanged binary file responses; do not expect JSON envelopes. |

## Common Errors

| Status | Meaning | Frontend action |
| --- | --- | --- |
| 401 | Missing or invalid token | Clear session and show login |
| 403 | Permission denied, wrong owner, suspended account, or KYC blocked | Show the API `message`; for `KYC_NOT_VERIFIED`, route to KYC |
| 404 | Resource/file not found | Show not-found state |
| 422 | Validation or business rule error | Render field errors or message |
| 500 | Server error | Show generic retry/support message |

KYC blocked response:

```json
{
  "success": false,
  "message": "Your identity has not been verified yet. Please submit your KYC documents.",
  "error": "KYC_NOT_VERIFIED",
  "kyc_url": "/api/v1/kyc/submit"
}
```

Insufficient balance response:

```json
{
  "success": false,
  "message": "Insufficient USD balance.",
  "error": "INSUFFICIENT_BALANCE"
}
```

Validation response follows Laravel's normal structure:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Auth

### Register

`POST /register`

Auth: public

Body:

```json
{
  "first_name": "Postman",
  "last_name": "Investor",
  "email": "postman.investor@example.com",
  "phone": "+2348012345678",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

Response `201`:

```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "user": {
      "id": "uuid",
      "first_name": "Postman",
      "last_name": "Investor",
      "name": "Postman Investor",
      "email": "postman.investor@example.com",
      "phone": "+2348012345678",
      "status": "active",
      "email_verified": false,
      "email_verified_at": null,
      "profile": {}
    },
    "access_token": "plain-text-token",
    "token_type": "Bearer"
  }
}
```

Frontend notes:

- New users normally need profile completion and KYC before financial operations.
- Token is a Sanctum bearer token.

### Login

`POST /login`

Auth: public

Body:

```json
{
  "email": "ada@example.com",
  "password": "password"
}
```

Response `200`:

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": "uuid",
      "first_name": "Ada",
      "last_name": "Okafor",
      "name": "Ada Okafor",
      "email": "ada@example.com",
      "status": "active",
      "email_verified": true,
      "email_verified_at": "2026-05-19T10:30:00.000000Z"
    },
    "access_token": "plain-text-token",
    "token_type": "Bearer"
  }
}
```

Frontend notes:

- Login revokes previous tokens, so treat it as a single active session.
- Suspended users receive `403`.

### Me

`GET /me`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Authenticated user retrieved.",
  "data": {
    "id": "uuid",
    "first_name": "Ada",
    "last_name": "Okafor",
    "name": "Ada Okafor",
    "email": "ada@example.com",
    "status": "active",
    "email_verified": true,
    "email_verified_at": "2026-05-19T10:30:00.000000Z",
    "profile": {},
    "roles": []
  }
}
```

Frontend notes:

- `email_verified` is the boolean clients should use for verified/unverified UI.
- Admins can manually verify a user's email from the admin Users page; the next `GET /me`, `GET /dashboard`, login, or register response reflects the updated `UserResource`.

### Logout

`POST /logout`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Logged out successfully.",
  "data": null
}
```

### Dashboard

`GET /dashboard`

Auth: bearer token

Purpose: single professional home-screen payload after login. This endpoint is not KYC-gated, so the frontend can show account status, next actions, balances, activity, and limits before financial actions are available.

Response:

```json
{
  "success": true,
  "message": "Dashboard retrieved.",
  "data": {
    "user": {
      "id": "uuid",
      "first_name": "Ada",
      "last_name": "Okafor",
      "name": "Ada Okafor",
      "email": "ada@example.com",
      "phone": "+2348012345678",
      "status": "active",
      "email_verified": true,
      "email_verified_at": "2026-05-19T10:30:00.000000Z"
    },
    "account": {
      "kyc_status": "verified",
      "profile_complete": true,
      "can_transact": true,
      "unread_notifications": 0
    },
    "portfolio": {
      "balances_count": 1,
      "balances": [
        {
          "asset": {
            "id": "uuid",
            "symbol": "USD",
            "name": "US Dollar",
            "type": "currency"
          },
          "quantity": "12000.00000000",
          "value": "12000.00000000"
        }
      ]
    },
    "activity": {
      "total_transactions": 12,
      "monthly_credits": "1500.00000000",
      "monthly_debits": "250.00000000",
      "pending_deposits": { "count": 1, "amount": "500.00000000" },
      "pending_withdrawals": { "count": 1, "amount": "250.00000000" },
      "recent_transactions": []
    },
    "limits": {
      "min_deposit_amount": 10,
      "max_deposit_amount": 50000,
      "min_withdrawal_amount": 20,
      "max_withdrawal_amount": 20000
    },
    "next_actions": []
  }
}
```

Frontend notes:

- `portfolio.balances` keeps each asset separate. Do not sum unlike assets into a single dashboard total.
- Use `next_actions[0]` as the primary dashboard CTA when present.

### Contact

`POST /contact`

Auth: public. If a bearer token is provided, the API stores the authenticated `user_id` with the message.

Purpose: lets both registered and guest users send a support/contact message.

Body:

```json
{
  "name": "Ada Okafor",
  "email": "ada@example.com",
  "message": "I need help reviewing my withdrawal request."
}
```

Response `201`:

```json
{
  "success": true,
  "message": "Your message has been received. Our support team will respond as soon as possible.",
  "data": {
    "id": "uuid",
    "name": "Ada Okafor",
    "email": "ada@example.com",
    "created_at": "2026-05-20T10:30:00+00:00"
  }
}
```

Rules:

- `name`: required string, max 255.
- `email`: required valid email, max 255.
- `message`: required string, min 10, max 5000.

### Forgot Password

`POST /forgot-password`

Auth: public

Body:

```json
{
  "email": "ada@example.com"
}
```

Response:

```json
{
  "success": true,
  "message": "Password reset link sent to your email.",
  "data": null
}
```

### Reset Password

`POST /reset-password`

Auth: public

Body:

```json
{
  "email": "ada@example.com",
  "token": "reset-token-from-email",
  "password": "password",
  "password_confirmation": "password"
}
```

Response:

```json
{
  "success": true,
  "message": "Password reset successfully. Please log in with your new password.",
  "data": null
}
```

## Public Data

### Settings

`GET /settings`

Auth: public

Response shape:

```json
{
  "success": true,
  "message": "Settings retrieved.",
  "data": {
    "platform": {
      "platform_name": "Oil Admin",
      "platform_tagline": "Invest in the energy that powers the world.",
      "support_email": "support@example.com",
      "support_phone": null
    },
    "limits": {
      "min_deposit_amount": 10,
      "max_deposit_amount": 50000,
      "min_withdrawal_amount": 20,
      "max_withdrawal_amount": 20000
    }
  }
}
```

Frontend notes:

- Use this to display platform/support details and deposit/withdrawal limits.
- Payment instructions now come from `GET /sub-methods/{subMethod}`.

### Countries

`GET /countries`

Auth: public

Response:

```json
{
  "success": true,
  "message": "Countries retrieved.",
  "data": [
    { "id": "uuid", "name": "Nigeria", "slug": "nigeria" }
  ]
}
```

### States By Country Slug

`GET /countries/{countrySlug}/states`

Auth: public

Response:

```json
{
  "success": true,
  "message": "States retrieved.",
  "data": [
    { "id": "uuid", "name": "Lagos", "slug": "lagos", "country_id": "uuid" }
  ],
  "country": { "id": "uuid", "name": "Nigeria" }
}
```

### States By Country ID

`GET /states?country_id={country_id}`

Auth: public

Response:

```json
{
  "success": true,
  "message": "States retrieved.",
  "data": [
    { "id": "uuid", "name": "Lagos", "slug": "lagos", "country_id": "uuid" }
  ]
}
```

## Profile And KYC

### Profile

`GET /profile`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Profile retrieved.",
  "data": {
    "id": "uuid",
    "user_id": "uuid",
    "user": {
      "id": "uuid",
      "first_name": "Ada",
      "last_name": "Okafor",
      "name": "Ada Okafor",
      "email": "ada@example.com"
    },
    "country_id": "uuid",
    "state_id": "uuid",
    "country": { "id": "uuid", "name": "Nigeria", "slug": "nigeria" },
    "state": { "id": "uuid", "name": "Lagos", "slug": "lagos", "country_id": "uuid" },
    "address": "14 Marina Road, Lagos",
    "gender": "female",
    "date_of_birth": "1992-08-19",
    "kyc_status": "verified",
    "kyc_submitted_at": "2026-05-19T10:30:00+00:00",
    "kyc_reviewed_at": "2026-05-19T12:30:00+00:00",
    "kyc_reviewed_by": "uuid",
    "kyc_rejection_reason": null,
    "id_document_type": "national_id",
    "id_document_front": "kyc/file.jpg",
    "id_document_back": null,
    "selfie_with_id": "kyc/selfie.jpg",
    "proof_of_address": "kyc/address.pdf",
    "created_at": "2026-05-19T10:30:00+00:00",
    "updated_at": "2026-05-19T12:30:00+00:00"
  }
}
```

### Update Profile

`PUT /profile`

Auth: bearer token

Body:

```json
{
  "first_name": "Ada",
  "last_name": "Okafor",
  "email": "ada@example.com",
  "phone": "+2348012345678",
  "country_id": "uuid",
  "state_id": "uuid",
  "address": "14 Marina Road, Lagos",
  "gender": "female",
  "date_of_birth": "1992-08-19"
}
```

Rules:

- `country_id`: nullable UUID, must exist.
- `state_id`: nullable UUID, must exist.
- `first_name`: nullable string, max 255.
- `last_name`: nullable string, max 255.
- `email`: nullable valid email, unique except current user.
- `phone`: nullable string, unique except current user.
- `gender`: nullable, `male`, `female`, or `other`.
- `date_of_birth`: nullable date before today.

Response:

```json
{
  "success": true,
  "message": "Profile updated.",
  "data": {}
}
```

### KYC Status

`GET /kyc/status`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "KYC status retrieved.",
  "data": {
    "kyc_status": "pending",
    "kyc_submitted_at": null,
    "kyc_reviewed_at": null,
    "kyc_rejection_reason": null,
    "documents": {
      "id_document_type": null,
      "id_document_front": false,
      "id_document_back": false,
      "selfie_with_id": false,
      "proof_of_address": false
    }
  }
}
```

Statuses:

- `pending`: user can submit documents.
- `submitted`: documents submitted and waiting for admin review.
- `under_review`: admin is reviewing.
- `verified`: financial operations are allowed.
- `rejected`: user should resubmit corrected documents.

### Submit KYC

`POST /kyc/submit`

Auth: bearer token

Content type: `multipart/form-data`

Fields:

| Field | Required | Type |
| --- | --- | --- |
| `id_document_type` | yes | `passport`, `national_id`, `drivers_license` |
| `id_document_front` | yes | jpg, jpeg, png, pdf, max 5MB |
| `id_document_back` | no | jpg, jpeg, png, pdf, max 5MB |
| `selfie_with_id` | yes | jpg, jpeg, png, max 5MB |
| `proof_of_address` | yes | jpg, jpeg, png, pdf, max 5MB |

Response:

```json
{
  "success": true,
  "message": "Documents submitted successfully. We will review and notify you within 24 hours.",
  "data": {
    "kyc_status": "submitted",
    "kyc_submitted_at": "2026-05-19T10:30:00+00:00"
  }
}
```

Frontend notes:

- KYC documents are private. The API does not expose user-facing document URLs.
- Already verified users receive `422`.
- Users under active review receive `422`.

## Catalog

### Assets

`GET /assets`

Auth: bearer token

Optional query:

```text
type=currency|crypto|share|commodity
```

Response:

```json
{
  "success": true,
  "message": "Assets retrieved.",
  "data": [
    {
      "id": "uuid",
      "symbol": "USD",
      "name": "US Dollar",
      "type": "currency",
      "current_price": "1.00000000",
      "price_source": "manual",
      "status": "active"
    }
  ]
}
```

### Asset Detail

`GET /assets/{asset}`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Asset retrieved.",
  "data": {
    "id": "uuid",
    "symbol": "USDT",
    "name": "Tether USD",
    "type": "crypto",
    "current_price": "1.00000000",
    "status": "active"
  }
}
```

### Methods

`GET /methods`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Payment methods retrieved.",
  "data": [
    {
      "id": "uuid",
      "name": "Bank Transfer",
      "sub_methods_count": 3
    }
  ]
}
```

Frontend notes:

- Methods are top-level categories. Use them to group sub-methods.

### Sub-Methods By Method

`GET /methods/{method}/sub-methods`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Payment sub-methods retrieved.",
  "data": [
    {
      "id": "uuid",
      "method_id": "uuid",
      "name": "GTBank",
      "bank_name": "GTBank",
      "account_name": "Oil Admin",
      "account_number": "0123456789",
      "wallet_address": null,
      "network": null,
      "instructions": "Use your transaction reference.",
      "is_active": true
    }
  ]
}
```

### Sub-Method Detail

`GET /sub-methods/{subMethod}`

Auth: bearer token

Use this endpoint before showing deposit instructions. The payload includes nullable bank and wallet fields, network, and `instructions`.

## Balances, Holdings, And Trades

### Balances

`GET /balances`

Auth: bearer token, verified KYC

Response:

```json
{
  "success": true,
  "message": "Balances retrieved.",
  "data": {
    "balances": [
      {
        "asset": {
          "id": "uuid",
          "symbol": "USD",
          "name": "US Dollar",
          "type": "currency"
        },
        "quantity": "12000.00000000",
        "value": "12000.00000000"
      }
    ]
  }
}
```

Frontend notes:

- Zero balances are filtered out.
- Balance is ledger-calculated from completed/processing credits, pending/processing/completed withdrawal debits, and processed earnings. Pending deposits do not increase balance; pending withdrawals reserve funds immediately.

### Balance Transactions

`GET /balances/transactions`

Auth: bearer token, verified KYC

Response:

```json
{
  "success": true,
  "message": "Balance transactions retrieved.",
  "data": [
    {
      "id": "uuid",
      "reference": "Deposit via Bank Transfer",
      "type": "deposit",
      "direction": "credit",
      "amount": "12,000.00",
      "quantity": "12000.00000000",
      "rate": "1.00000000",
      "asset": {},
      "method": {},
      "status": "completed",
      "created_at": "2026-05-19T10:30:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

### Holdings

`GET /holdings`

Auth: bearer token, verified KYC, `view_holdings` permission

Response:

```json
{
  "success": true,
  "message": "Holdings retrieved.",
  "data": [
    {
      "asset": {},
      "quantity": "100.00000000",
      "current_value": "6825.00000000"
    }
  ]
}
```

### Holding Detail

`GET /holdings/{asset}`

Auth: bearer token, verified KYC, `view_holdings` permission

Response:

```json
{
  "success": true,
  "message": "Holding retrieved.",
  "data": {
    "asset": {},
    "quantity": "100.00000000",
    "current_value": "6825.00000000"
  }
}
```

### Holding Trades

`GET /holdings/trades`

Auth: bearer token, verified KYC, `view_holdings` permission

Response:

```json
{
  "success": true,
  "message": "Holding trades retrieved.",
  "data": [
    {
      "id": "uuid",
      "type": "buy",
      "direction": "credit",
      "amount": "6,825.00",
      "asset": {}
    }
  ]
}
```

### Buy Asset

`POST /trades/buy`

Auth: bearer token, verified KYC, `buy_assets` permission

Body:

```json
{
  "from_asset_id": "uuid",
  "to_asset_id": "uuid",
  "amount": 25
}
```

Rules:

- `from_asset_id` and `to_asset_id` must exist and be different.
- `amount` is the quantity of the source asset to debit.

Response `201`:

```json
{
  "success": true,
  "message": "Trade recorded successfully.",
  "data": {
    "debit": {},
    "credit": {}
  }
}
```

### Sell Asset

`POST /trades/sell`

Auth: bearer token, verified KYC, `sell_assets` permission

Body:

```json
{
  "from_asset_id": "uuid",
  "to_asset_id": "uuid",
  "amount": 0.1
}
```

Response `201`:

```json
{
  "success": true,
  "message": "Trade recorded successfully.",
  "data": {
    "debit": {},
    "credit": {}
  }
}
```

## Deposits

### List Deposits

`GET /deposits`

Auth: bearer token, verified KYC

Response:

```json
{
  "success": true,
  "message": "Deposits retrieved.",
  "data": [
    {
      "id": "uuid",
      "reference": "Deposit via Bank Transfer",
      "amount": "1,500.00",
      "quantity": "1500.00000000",
      "asset": {},
      "method": {},
      "proof": "proofs/file.jpg",
      "proof_url": "http://127.0.0.1:8000/api/v1/deposits/uuid/proof",
      "status": "pending",
      "created_at": "2026-05-19T10:30:00+00:00"
    }
  ]
}
```

### Create Deposit

`POST /deposits`

Auth: bearer token, verified KYC, `deposit_funds` permission

Content type: `multipart/form-data`

Fields:

| Field | Required | Type |
| --- | --- | --- |
| `asset_id` | yes | UUID |
| `sub_method_id` | yes | UUID |
| `amount` | yes | numeric, within settings limits |
| `proof` | no | jpg, jpeg, png, pdf, max 5MB |

Response `201`:

```json
{
  "success": true,
  "message": "Deposit submitted. Awaiting completion.",
  "data": {
    "id": "uuid",
    "proof_url": "http://127.0.0.1:8000/api/v1/deposits/uuid/proof",
    "status": "pending"
  }
}
```

Frontend notes:

- Deposit does not immediately credit spendable balance until admin completion.
- If `proof_url` is null, no proof was uploaded.
- `method_id` is still accepted for old clients when the method has an active sub-method, but new clients should send `sub_method_id`.

### Deposit Detail

`GET /deposits/{deposit}`

Auth: bearer token, verified KYC, owner only

Response:

```json
{
  "success": true,
  "message": "Deposit retrieved.",
  "data": {
    "id": "uuid",
    "reference": "Deposit via Bank Transfer",
    "asset": {},
    "method": {},
    "proof_url": "http://127.0.0.1:8000/api/v1/deposits/uuid/proof",
    "status": "completed"
  }
}
```

### View Deposit Proof

`GET /deposits/{deposit}/proof`

Auth: bearer token, owner only

Response:

```text
Binary file response
```

Frontend notes:

- This route is authenticated but not KYC-gated, so users can still view historical proofs if their KYC status changes.
- Use `window.open(proof_url)` for simple viewing or `fetch` as blob for custom viewers.

## Withdrawals

### List Withdrawals

`GET /withdrawals`

Auth: bearer token, verified KYC

Response:

```json
{
  "success": true,
  "message": "Withdrawals retrieved.",
  "data": [
    {
      "id": "uuid",
      "reference": "Withdrawal to USDT TRC20",
      "amount": "250.00",
      "quantity": "250.00000000",
      "asset": {},
      "method": {},
      "destination": {
        "type": "crypto",
        "account_name": null,
        "account_number": null,
        "bank_name": null,
        "wallet_address": "TUserSeedWallet123",
        "network": "TRC20",
        "proof": "proofs/testing/ppa_Letter.pdf",
        "proof_url": "http://127.0.0.1:8000/api/v1/withdrawals/uuid/proof"
      },
      "status": "processing"
    }
  ]
}
```

### Create Bank Withdrawal

`POST /withdrawals`

Auth: bearer token, verified KYC, `withdraw_funds` permission

Body:

```json
{
  "asset_id": "uuid",
  "sub_method_id": "uuid",
  "amount": 50,
  "destination_type": "bank",
  "account_name": "Ada Okafor",
  "account_number": "0123456789",
  "bank_name": "Oil Admin Test Bank"
}
```

### Create Crypto Withdrawal

`POST /withdrawals`

Auth: bearer token, verified KYC, `withdraw_funds` permission

Body:

```json
{
  "asset_id": "uuid",
  "sub_method_id": "uuid",
  "amount": 25,
  "destination_type": "crypto",
  "wallet_address": "TPostmanWalletAddress1234567890",
  "network": "TRC20"
}
```

Rules:

- `amount` must fit withdrawal settings limits.
- User can only have one pending withdrawal per asset.
- Balance must be sufficient.
- Bank fields are required when `destination_type=bank`.
- Wallet fields are required when `destination_type=crypto`.
- `method_id` is still accepted for old clients when the method has an active sub-method, but new clients should send `sub_method_id`.

## Payment Migration Notes

- Legacy settings in the `payment` group are not returned from `GET /settings` and are removed by the settings seeder.
- When legacy payment settings exist during seeding, non-empty bank and crypto values are copied into `sub_methods` before those settings are deleted.
- Admins should maintain all payment destinations in Finance / Payment sub-methods.

Response `201`:

```json
{
  "success": true,
  "message": "Withdrawal request submitted. Pending approval.",
  "data": {
    "id": "uuid",
    "status": "pending",
    "destination": {}
  }
}
```

### Withdrawal Detail

`GET /withdrawals/{withdrawal}`

Auth: bearer token, verified KYC, owner only

Response:

```json
{
  "success": true,
  "message": "Withdrawal retrieved.",
  "data": {
    "id": "uuid",
    "reference": "Withdrawal to USDT TRC20",
    "destination": {
      "type": "crypto",
      "wallet_address": "TUserSeedWallet123",
      "network": "TRC20",
      "proof_url": "http://127.0.0.1:8000/api/v1/withdrawals/uuid/proof"
    },
    "status": "processing"
  }
}
```

### View Withdrawal Proof

`GET /withdrawals/{withdrawal}/proof`

Auth: bearer token, owner only

Response:

```text
Binary file response
```

Frontend notes:

- `proof_url` is usually null until admin processes the withdrawal and uploads payout evidence.
- This is the user-facing proof that payout processing evidence exists.

## Transactions

### List Transactions

`GET /transactions`

Auth: bearer token, verified KYC, `view_transactions` permission

Optional filters:

```text
type=deposit|withdrawal|buy|sell
direction=credit|debit
status=pending|processing|completed|cancelled
```

Response:

```json
{
  "success": true,
  "message": "Transactions retrieved.",
  "data": [
    {
      "id": "uuid",
      "reference": "Buy SHELL from USD",
      "type": "buy",
      "direction": "debit",
      "amount": "6,825.00",
      "quantity": "100.00000000",
      "rate": "68.25000000",
      "asset": {},
      "method": null,
      "status": "completed",
      "created_at": "2026-05-19T10:30:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

### Transaction Detail

`GET /transactions/{transaction}`

Auth: bearer token, verified KYC, owner only, `view_transactions` permission

Response:

```json
{
  "success": true,
  "message": "Transaction retrieved.",
  "data": {
    "id": "uuid",
    "reference": "Buy SHELL from USD",
    "type": "buy",
    "direction": "credit",
    "asset": {},
    "method": null,
    "status": "completed"
  }
}
```

## Notifications

### List Notifications

`GET /notifications`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "Notifications retrieved.",
  "data": [
    {
      "id": "uuid",
      "type": "deposit_completed",
      "title": "Deposit completed",
      "body": "Your deposit has been completed.",
      "category": "general",
      "priority": "normal",
      "severity": "info",
      "action": null,
      "data": {},
      "read": false,
      "read_at": null,
      "created_at": "2026-05-19T10:30:00+00:00"
    }
  ],
  "meta": {
    "unread_count": 1,
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

### Mark Notification Read

`POST /notifications/{id}/read`

Auth: bearer token, owner only

Response:

```json
{
  "success": true,
  "message": "Notification marked as read.",
  "data": null
}
```

### Mark All Notifications Read

`POST /notifications/read-all`

Auth: bearer token

Response:

```json
{
  "success": true,
  "message": "All notifications marked as read.",
  "data": null
}
```

## Recommended Frontend Screens

- Auth: login, register, forgot password, reset password.
- Public contact: submit support messages from guest or signed-in users.
- App bootstrap: dashboard loads user, profile, KYC status, balances, assets, methods, notifications.
- KYC: status screen, upload form, rejected-resubmission state, verified state.
- Wallet: balances, deposit form, withdrawal form, transaction history.
- Deposits: list, detail, proof viewer.
- Withdrawals: list, detail, payout proof viewer.
- Trading: buy/sell form using assets and balances.
- Notifications: list, unread badge, mark read.

## Postman Run Order

1. Public / Send Contact Message
2. Public / Countries
3. Public / States By Country Slug
4. Auth / Login - Test User
5. Auth / Dashboard
6. Catalog / Assets
7. Catalog / Payment Methods
8. Catalog / Bank Sub Methods
9. Catalog / Crypto Sub Methods
10. Catalog / Sub Method Detail
11. Balances Holdings Trades / Balances
12. Deposits / List Deposits
13. Deposits / Deposit Detail
14. Deposits / View Deposit Proof
15. Withdrawals / List Withdrawals
16. Withdrawals / Withdrawal Detail
17. Withdrawals / View Withdrawal Proof
