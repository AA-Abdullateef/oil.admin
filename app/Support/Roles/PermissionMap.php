<?php

// This file is documentation, not runtime code.
// It exists so developers can look up any slug and find its enforcement point.

/*
|--------------------------------------------------------------------------
| Permission Enforcement Map
|--------------------------------------------------------------------------
|
| End-user permissions:
| - deposit_funds: StoreDepositRequest::authorize()
| - withdraw_funds: StoreWithdrawalRequest::authorize()
| - buy_assets: BuyTradeRequest::authorize()
| - sell_assets: SellTradeRequest::authorize()
| - view_holdings: has_permission middleware on API holdings routes
| - view_transactions: has_permission middleware on API transaction routes
|
| Admin permissions:
| - manage_users: has_permission middleware on admin user routes
| - manage_transactions: has_permission middleware on admin deposit/withdrawal read and cancel routes
| - complete_deposits: has_permission middleware on admin deposits.complete route
| - process_withdrawals: has_permission middleware on admin withdrawals.process route
| - manage_balances: has_permission middleware on admin balance routes
| - manage_assets: has_permission middleware on admin asset routes
| - manage_settings: has_permission middleware on admin settings and sub-method routes
| - manage_roles: has_permission middleware on admin role routes
| - manage_permissions: has_permission middleware on admin permission routes
| - manage_kyc: has_permission middleware on admin KYC routes
| - view_holdings: has_permission middleware on admin holding routes
| - view_transaction_logs: has_permission middleware on admin transaction routes
|
| super_admin bypasses hasPermission() entirely in User::hasPermission().
| No permission rows need to exist for super_admin to work.
|
| Permission check resolution order:
| 1. isSuperAdmin() returns true, allow immediately.
| 2. roles() checks for a related permission whose slug matches the required slug.
| 3. Missing permission returns false and becomes a 403 or validation failure.
|
*/
