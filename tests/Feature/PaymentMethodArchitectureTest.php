<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Deposit;
use App\Models\Method;
use App\Models\Role;
use App\Models\SubMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentMethodArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_methods_and_sub_methods(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $method = Method::create(['name' => 'Bank Transfer']);
        $subMethod = SubMethod::create([
            'method_id' => $method->id,
            'name' => 'GTBank',
            'account_name' => 'Oil Admin',
            'account_number' => '0123456789',
            'bank_name' => 'GTBank',
            'instructions' => 'Use your deposit reference.',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/methods')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Bank Transfer');

        $this->getJson("/api/v1/methods/{$method->id}/sub-methods")
            ->assertOk()
            ->assertJsonPath('data.0.id', $subMethod->id);

        $this->getJson("/api/v1/sub-methods/{$subMethod->id}")
            ->assertOk()
            ->assertJsonPath('data.instructions', 'Use your deposit reference.');
    }

    public function test_deposit_accepts_sub_method_id_and_records_method_relationship(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'User', 'slug' => 'user']);
        $role->permissions()->create(['name' => 'Deposit Funds', 'slug' => 'deposit_funds']);
        $user->roles()->attach($role);
        $user->profile()->updateOrCreate([], ['kyc_status' => 'verified']);
        Sanctum::actingAs($user);

        $asset = Asset::create([
            'symbol' => 'USD',
            'name' => 'US Dollar',
            'type' => Asset::TYPE_CURRENCY,
            'current_price' => '1.00000000',
            'status' => Asset::STATUS_ACTIVE,
        ]);
        $method = Method::create(['name' => 'Bank Transfer']);
        $subMethod = SubMethod::create([
            'method_id' => $method->id,
            'name' => 'Access Bank',
            'account_name' => 'Oil Admin',
            'account_number' => '0123456789',
            'bank_name' => 'Access Bank',
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/deposits', [
            'asset_id' => $asset->id,
            'sub_method_id' => $subMethod->id,
            'amount' => 50,
        ])->assertCreated()
            ->assertJsonPath('data.sub_method.id', $subMethod->id);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'method_id' => $method->id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_DEPOSIT,
        ]);
    }

    public function test_admin_cannot_delete_referenced_sub_method(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $role->permissions()->create(['name' => 'Manage Settings', 'slug' => 'manage_settings']);
        $admin = User::factory()->create(['status' => 'active']);
        $admin->roles()->attach($role);

        $asset = Asset::create([
            'symbol' => 'USD',
            'name' => 'US Dollar',
            'type' => Asset::TYPE_CURRENCY,
            'current_price' => '1.00000000',
            'status' => Asset::STATUS_ACTIVE,
        ]);
        $method = Method::create(['name' => 'Bank Transfer']);
        $subMethod = SubMethod::create([
            'method_id' => $method->id,
            'name' => 'First Bank',
            'is_active' => true,
        ]);
        Deposit::create([
            'user_id' => $admin->id,
            'asset_id' => $asset->id,
            'method_id' => $method->id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'direction' => Transaction::DIRECTION_CREDIT,
            'amount' => '10.00000000',
            'status' => Transaction::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->delete("/admin/sub-methods/{$subMethod->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sub_methods', ['id' => $subMethod->id]);
    }
}
