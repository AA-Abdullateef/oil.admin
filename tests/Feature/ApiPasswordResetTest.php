<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApiPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_frontend_reset_link(): void
    {
        config(['app.frontend_url' => 'https://frontend.example']);
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'ada@example.com',
        ]);

        $this->postJson('/api/v1/forgot-password', [
            'email' => $user->email,
        ])->assertOk()
            ->assertJson([
                'message' => 'Password reset link sent to your email.',
            ]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);
            $url = $mail->actionUrl;

            return str_starts_with($url, 'https://frontend.example/reset-password?')
                && str_contains($url, 'token=')
                && str_contains($url, 'email=ada%40example.com');
        });
    }
}
