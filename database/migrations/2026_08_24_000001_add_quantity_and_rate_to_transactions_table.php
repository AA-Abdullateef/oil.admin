<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // quantity: the number of asset units this row moves.
            // This is the permanent historical fact — it never changes after insert.
            // Examples:
            //   deposit 10 USD        → quantity = 10
            //   buy 5 SHELL           → quantity = 5   (SHELL credit row)
            //   sell 5 SHELL          → quantity = 5   (SHELL debit row)
            //   receive 250 USD       → quantity = 250 (USD credit row from selling SHELL)
            //
            // amount = quantity × rate  (USD cost — kept for reporting/display only)
            // Balance is always derived from SUM(quantity), never from amount.
            $table->decimal('quantity', 28, 8)->default(0)->after('amount');

            // rate: asset price in USD at the exact moment this transaction was created.
            // Stored so the original quantity can always be re-derived if needed:
            //   quantity = amount / rate
            // Also used in reporting to show the price at which a trade executed.
            $table->decimal('rate', 28, 8)->default(0)->after('quantity');
        });

        // ── Backfill existing rows ────────────────────────────────────────────
        // For rows already in the database, we have `amount` but not `quantity`
        // or `rate`. We reconstruct them using the asset's current price as a proxy.
        //
        // This is an approximation for historical data — the real rate is gone
        // because it was never stored. All new rows will be exact.
        //
        // currency-type assets (USD, USDT) have current_price = 1, so their
        // quantity backfill is exact: quantity = amount / 1 = amount.
        //
        // For shares/crypto, quantity will be based on the current price, not
        // the price at the time of the original trade. Acceptable because:
        // 1. The balance will be correct going forward from this migration.
        // 2. True historical accuracy requires the rate column going forward.
        DB::statement('
            UPDATE transactions t
            INNER JOIN assets a ON a.id = t.asset_id
            SET
                t.rate     = CASE WHEN a.current_price > 0 THEN a.current_price ELSE 1 END,
                t.quantity = CASE
                    WHEN a.current_price > 0 THEN t.amount / a.current_price
                    ELSE t.amount
                END
            WHERE t.quantity = 0
        ');
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'rate']);
        });
    }
};