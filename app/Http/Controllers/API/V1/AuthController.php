<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgotPasswordRequest;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Support\Roles\RoleManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\RegistrationOtp;
use App\Models\PasswordResetOtp;
use App\Http\Requests\API\Auth\VerifyResetOtpRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            ...$request->validated(),
            'status' => null,
            'email_verified_at' => null,
        ]);

        // attach default role (unchanged logic)
        $role = Role::where('slug', RoleManager::USER)->first();
        if ($role) {
            $user->roles()->attach($role);
        }

        // generate OTP for verification
        $otp = random_int(100000, 999999);

        \App\Models\RegistrationOtp::create([
            'user_id' => $user->id,
            'otp' => \Illuminate\Support\Facades\Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        // send OTP (keep simple, can be swapped to Notification later)
        \Illuminate\Support\Facades\Mail::raw(
            "Your verification OTP is: {$otp}. It expires in 10 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Account');
            }
        );

        return $this->success([
            'user' => new UserResource($user->load('profile')),
            'requires_verification' => true,
        ], 'Registration successful. Please verify the OTP sent to your email.', 201);
    }

    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found.', 404);
        }

        $otpRecord = RegistrationOtp::where('user_id', $user->id)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return $this->error('OTP not found.', 422);
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            return $this->error('OTP expired.', 422);
        }

        if (!Hash::check($request->otp, $otpRecord->otp)) {
            return $this->error('Invalid OTP.', 422);
        }

        // activate user
        $user->update([
            'status' => 'active',
        ]);

        $otpRecord->update([
            'used' => true,
        ]);

        // LOGIN USER IMMEDIATELY
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->fresh()->load('profile')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Account verified successfully.');
    }

    public function resendRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->status === 'active') {
            return $this->error('Invalid request.', 422);
        }

        RegistrationOtp::where('user_id', $user->id)->delete();

        $otp = random_int(100000, 999999);

        RegistrationOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw(
            "Your new OTP is: {$otp}",
            fn ($message) =>
                $message->to($user->email)->subject('OTP Resend')
        );

        return $this->success(null, 'OTP resent successfully.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($user->status)) {
            return $this->error('Please verify your account first.', 403);
        }

        if ($user->status === 'suspended') {
            return $this->error('Your account is suspended. Contact support.', 403);
        }

        if ($user->status === 'banned') {
            return $this->error('Your account is deactivated. Contact support.', 403);
        }

        // Revoke all previous tokens — enforce single active session
        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load('profile')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource(
                $request->user()->load('profile', 'roles')
            ),
            'Authenticated user retrieved.'
        );
    }

    /**
     * Step 1 — Send password reset link to email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error(
                'No account found with that email address.',
                404
            );
        }

        PasswordResetOtp::where('user_id', $user->id)->delete();

        $otp = random_int(100000, 999999);

        PasswordResetOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw(
            "Your password reset OTP is: {$otp}. It expires in 10 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP');
            }
        );

        return $this->success(
            new UserResource($user),
            'Password reset OTP sent to your email.'
        );
    }

    /**
     * Step 2 — Verify OTP and return reset token.
     */
    public function verifyResetOtp(
    VerifyResetOtpRequest $request
    ): JsonResponse {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error(
                'Invalid request.',
                404
            );
        }

        $otpRecord = PasswordResetOtp::where('user_id', $user->id)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return $this->error(
                'OTP not found.',
                422
            );
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            return $this->error(
                'OTP has expired.',
                422
            );
        }

        if (!Hash::check($request->otp, $otpRecord->otp)) {
            return $this->error(
                'Invalid OTP.',
                422
            );
        }

        $resetToken = Str::random(64);

        $otpRecord->update([
            'used' => true,
            'reset_token' => hash('sha256', $resetToken),
        ]);

        return $this->success([
            'reset_token' => $resetToken,
        ], 'OTP verified successfully.');
    }

    /**
     * Step 2 — Validate token and set new password.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error(
                'User not found.',
                404
            );
        }

        $record = PasswordResetOtp::where('user_id', $user->id)
            ->where('used', true)
            ->latest()
            ->first();

        if (!$record) {
            return $this->error(
                'Reset session not found.',
                422
            );
        }

        if (
            !hash_equals(
                $record->reset_token,
                hash('sha256', $request->reset_token)
            )
        ) {
            return $this->error(
                'Invalid reset token.',
                422
            );
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $user->tokens()->delete();

        PasswordResetOtp::where(
            'user_id',
            $user->id
        )->delete();

        return $this->success(
            null,
            'Password reset successfully.'
        );
    }
}
