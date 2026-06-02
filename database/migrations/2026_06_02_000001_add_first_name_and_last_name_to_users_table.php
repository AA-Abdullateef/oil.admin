<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user) {
                $parts = preg_split('/\s+/', trim((string) $user->name), 2);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'first_name' => $parts[0] ?? null,
                        'last_name' => $parts[1] ?? null,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
