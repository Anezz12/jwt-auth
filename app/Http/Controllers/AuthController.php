<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = Auth::login(user: $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Register',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (! $token) {
            return response()->json(data: [
                'status' => 'error',
                'message' => 'Email atau Password salah',
            ], status: 401);
        }

        $user = Auth::user();

        return response()->json(data: [
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'expires_in' => Auth::factory()->getTTL() * 60,
                'type' => 'bearer',
            ],
        ]);

    }

    public function logout(Request $request)
    {
        Auth::logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ],
        ]);
    }

    public function user()
    {
        if (! Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        } else {
            return response()->json([
                'status' => 'success',
                'user' => Auth::user(),
            ]);
        }
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirectToProvider($provider)
    {
        $validProviders = ['google', 'github', 'twitter'];

        if (! in_array($provider, $validProviders)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid provider',
            ], 400);
        }

        // Generate state untuk security
        $state = base64_encode(json_encode([
            'provider' => $provider,
            'timestamp' => now()->timestamp,
        ]));
        $redirectUrl = Socialite::driver($provider)
            ->stateless()
            ->with(['state' => $state])
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => true]))
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl,
            'state' => $state,
        ]);
    }

    /**
     * Handle OAuth callback Google
     */
    public function exchangeGoogleCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            // Exchange code for user info
            $socialUser = Socialite::driver('google')
                ->stateless()
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->user();

            $existingUser = User::where('email', $socialUser->getEmail())->first();

            if ($existingUser) {
                $existingUser->update([
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'role' => 'user',
                    'password' => Hash::make(\Illuminate\Support\Str::random(24)),
                    'email_verified_at' => now(),
                ]);
            }

            $token = Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'OAuth login successful',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'expires_in' => Auth::factory()->getTTL() * 60,
                    'type' => 'bearer',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OAuth exchange failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle OAuth callback Github
     */
    public function exchangeGithubCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            // Exchange code for user info
            $socialUser = Socialite::driver('github')
                ->stateless()
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->user();

            $existingUser = User::where('email', $socialUser->getEmail())->first();

            if ($existingUser) {
                $existingUser->update([
                    'provider' => 'github',
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $socialUser->getName() ?: 'User',
                    'email' => $socialUser->getEmail(),
                    'provider' => 'github',
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'role' => 'user',
                    'password' => Hash::make(\Illuminate\Support\Str::random(24)), // ✅ Fix password
                    'email_verified_at' => now(),
                ]);
            }

            $token = Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'OAuth login successful',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'expires_in' => Auth::factory()->getTTL() * 60,
                    'type' => 'bearer',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OAuth exchange failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
