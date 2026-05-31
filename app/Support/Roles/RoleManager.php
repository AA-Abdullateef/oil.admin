<?php

namespace App\Support\Roles;

final class RoleManager
{
    // ── Role slugs ───────────────────────────────────────────────
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN       = 'admin';
    public const USER        = 'user';

    /**
     * Roles that can NEVER be deleted, and whose assignment to
     * the seeded super-admin user can never be removed.
     */
    public const IMMUTABLE_ROLES = [
        self::SUPER_ADMIN,
    ];

    /**
     * Roles that require admin-panel access (used by AdminMiddleware).
     */
    public const ADMIN_ROLES = [
        self::SUPER_ADMIN,
        self::ADMIN,
    ];

    public static function all(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN, self::USER];
    }

    /**
     * Permission slugs per role.
     *
     * super_admin  — owns every permission; also bypasses hasPermission()
     *                entirely in User::hasPermission().
     * admin        — operational permissions: complete/process/manage.
     * user         — end-user financial actions.
     *
     * Every slug here must correspond to an enforcing point:
     *   Form Request authorize()  ·  HasPermission middleware
     *   Gate definition           ·  Policy method
     * See AuthServiceProvider and each FormRequest for the mapping.
     */
    public static function permissions(): array
    {
        $adminPermissions = [
            // User management
            'manage_users',
            // Financial operations
            'manage_transactions',
            'complete_deposits',
            'process_withdrawals',
            // Asset management
            'manage_balances',
            'manage_assets',
            // Access management
            'manage_roles',
            'manage_permissions',
            // KYC
            'manage_kyc',
            // Settings
            'manage_settings',
            // Read-only monitoring
            'view_holdings',
            'view_transaction_logs',
        ];

        return [
            self::SUPER_ADMIN => $adminPermissions, // super_admin bypasses in code anyway
            self::ADMIN       => $adminPermissions,
            self::USER        => [
                'deposit_funds',
                'withdraw_funds',
                'buy_assets',
                'sell_assets',
                'view_holdings',
                'view_transactions',
            ],
        ];
    }

    public static function permissionsFor(string $role): array
    {
        return static::permissions()[$role] ?? [];
    }

    public static function isImmutable(string $roleSlug): bool
    {
        return in_array($roleSlug, self::IMMUTABLE_ROLES, true);
    }

    public static function isAdminRole(string $roleSlug): bool
    {
        return in_array($roleSlug, self::ADMIN_ROLES, true);
    }
}
