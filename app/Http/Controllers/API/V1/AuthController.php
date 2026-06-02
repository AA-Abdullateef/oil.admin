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

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $role = Role::where('slug', RoleManager::USER)->first();
        if ($role) {
            $user->roles()->attach($role);
        }

        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load('profile')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->error('Your account has been suspended. Contact support.', 403);
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
        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->error('Unable to send reset link. Please try again.', 422);
        }

        return $this->success(null, 'Password reset link sent to your email.');
    }

    /**
     * Step 2 — Validate token and set new password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();

                // Revoke all tokens so re-login is required after reset
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->error(
                match ($status) {
                    Password::INVALID_TOKEN => 'Invalid or expired reset token.',
                    Password::INVALID_USER => 'No account found with that email.',
                    default => 'Password reset failed. Please try again.',
                },
                422
            );
        }

        return $this->success(null, 'Password reset successfully. Please log in with your new password.');
    }
}
