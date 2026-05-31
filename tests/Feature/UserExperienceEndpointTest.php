<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Deposit;
use App\Models\ContactMessage;
use App\Models\Method;
use App\Models\Role;
use App\Models\SubMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserExperienceEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_endpoint_returns_professional_user_summary(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Okafor',
            'email' => 'ada@example.com',
        ]);
        Sanctum::actingAs($user);

        $asset = $this->createAsset();
        $method = $this->createMethod();
        $subMethod = $this->createSubMethod($method);

        Deposit::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'method_id' => $method->id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'direction' => Transaction::DIRECTION_CREDIT,
            'amount' => '100.00000000',
            'reference' => 'Initial deposit',
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        Withdrawal::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'method_id' => $method->id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'direction' => Transaction::DIRECTION_DEBIT,
            'amount' => '30.00000000',
            'reference' => 'Pending withdrawal',
            'status' => Transaction::STATUS_PENDING,
        ]);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'ada@example.com')
            ->assertJsonPath('data.portfolio.balances.0.quantity', '70.00000000')
            ->assertJsonMissingPath('data.portfolio.total_value')
            ->assertJsonPath('data.activity.pending_withdrawals.count', 1)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'account',
                    'portfolio',
                    'activity' => ['recent_transactions'],
                    'limits',
                    'next_actions',
                ],
            ]);
    }

    public function test_guest_can_submit_contact_message(): void
    {
        $this->postJson('/api/v1/contact', [
            'name' => 'Guest Sender',
            'email' => 'guest@example.com',
            'message' => 'I need help understanding my account setup.',
        ])->assertCreated()
            ->assertJsonPath('data.email', 'guest@example.com');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Guest Sender',
            'email' => 'guest@example.com',
            'user_id' => null,
        ]);
    }

    public function test_admin_can_view_contact_messages_read_only_pages(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $admin = User::factory()->create(['status' => 'active']);
        $admin->roles()->attach($role);

        ContactMessage::create([
            'name' => 'Guest Sender',
            'email' => 'guest@example.com',
            'message' => 'I need help understanding my account setup.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('Guest Sender')
            ->assertSee('guest@example.com');

        $message = ContactMessage::firstOrFail();

        $this->actingAs($admin)
            ->get("/admin/contact-messages/{$message->id}")
            ->assertOk()
            ->assertSee('I need help understanding my account setup.');
    }

    public function test_registered_user_contact_message_keeps_user_reference(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/contact', [
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'Please review my withdrawal request status.',
        ])->assertCreated();

        $this->assertDatabaseHas('contact_messages', [
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    public function test_contact_message_requires_name_email_and_message(): void
    {
        $this->postJson('/api/v1/contact', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'message',
            ]);
    }

    private function createAsset(): Asset
    {
        return Asset::create([
            'symbol' => 'USD',
            'name' => 'US Dollar',
            'type' => Asset::TYPE_CURRENCY,
            'current_price' => '1.00000000',
            'status' => Asset::STATUS_ACTIVE,
        ]);
    }

    private function createMethod(): Method
    {
        return Method::create([
            'name' => 'Bank Transfer',
        ]);
    }

    private function createSubMethod(Method $method): SubMethod
    {
        return SubMethod::create([
            'method_id' => $method->id,
            'name' => 'Test Bank',
            'account_name' => 'Oil Admin',
            'account_number' => '0123456789',
            'bank_name' => 'Test Bank',
            'is_active' => true,
        ]);
    }
}
