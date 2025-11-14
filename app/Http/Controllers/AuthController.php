<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Regular Authentication

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Only allow user registration via public API
        // Admin and author roles should be assigned manually or through admin panel
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user exists and has a password (not OAuth-only account)
        if (! $user || ! $user->password) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if account is associated with social login only
        if ($user->provider && ! $user->password) {
            throw ValidationException::withMessages([
                'email' => ['This account is registered with '.ucfirst($user->provider).'. Please use '.ucfirst($user->provider).' login.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    // Password Reset

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'success' => true,
            'message' => 'If your email is registered, you will receive a password reset link',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 500);
    }

    // OAuth - Google

    public function redirectToProvider()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function exchangeGoogleCode(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // Validate that email exists from OAuth provider
            if (! $googleUser->email) {
                throw new \Exception('Email not provided by OAuth provider');
            }

            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'name' => $googleUser->name,
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'role' => 'user',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            }

            // Delete old tokens and create new one
            $user->tokens()->delete();
            $token = $user->createToken('google-auth')->plainTextToken;

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            return redirect($frontendUrl.'/oauth/callback?success=true&token='.urlencode($token).'&provider=google');

        } catch (\Exception) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            // Don't leak sensitive error details to the frontend
            return redirect($frontendUrl.'/oauth/callback?success=false&error=authentication_failed');
        }
    }

    // OAuth - GitHub

    public function redirectToGithub()
    {
        return response()->json([
            'url' => Socialite::driver('github')
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ]);
    }

    public function exchangeGithubCode(Request $request)
    {
        try {
            $githubUser = Socialite::driver('github')
                ->stateless()
                ->user();

            // Validate that email exists from OAuth provider
            if (! $githubUser->email) {
                throw new \Exception('Email not provided by OAuth provider');
            }

            $user = User::where('email', $githubUser->email)->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'name' => $githubUser->name ?? $githubUser->nickname,
                    'provider' => 'github',
                    'provider_id' => $githubUser->id,
                    'avatar' => $githubUser->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $githubUser->name ?? $githubUser->nickname,
                    'email' => $githubUser->email,
                    'provider' => 'github',
                    'provider_id' => $githubUser->id,
                    'avatar' => $githubUser->avatar,
                    'role' => 'user',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            }

            // Delete old tokens and create new one
            $user->tokens()->delete();
            $token = $user->createToken('github-auth')->plainTextToken;

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            return redirect($frontendUrl.'/oauth/callback?success=true&token='.urlencode($token).'&provider=github');

        } catch (\Exception) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            // Don't leak sensitive error details to the frontend
            return redirect($frontendUrl.'/oauth/callback?success=false&error=authentication_failed');
        }
    }

    // Unlink OAuth Provider

    public function unlinkProvider(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:google,github',
        ]);

        $user = $request->user();

        // Check if user has password (can still login without OAuth)
        if (! $user->password && $user->provider) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot unlink OAuth provider. This is your only login method. Please set a password first.',
            ], 400);
        }

        // Check if provider is linked
        if (! $user->provider_id || $user->provider !== $request->provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not linked to this account',
            ], 400);
        }

        // Unlink the provider
        $user->update([
            'provider' => null,
            'provider_id' => null,
            'avatar' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->provider).' account unlinked successfully',
        ]);
    }
}
