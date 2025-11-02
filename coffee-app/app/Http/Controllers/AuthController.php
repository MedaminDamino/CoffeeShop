<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "birthday", "password", "password_confirmation"},
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="birthday", type="string", format="date", example="2000-01-01"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!"),
     *             @OA\Property(property="password_confirmation", type="string", example="Password123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration successful"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'birthday' => 'required|date|before:today',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/|confirmed',
        ]);

        // Check age >= 18
        $birthday = \Carbon\Carbon::parse($request->birthday);
        if ($birthday->age < 18) {
            throw ValidationException::withMessages([
                'birthday' => ['You must be at least 18 years old.'],
            ]);
        }

        $user = User::create([
            'name' => $request->username, // Use username as name for display
            'username' => $request->username,
            'email' => $request->email,
            'birthday' => $request->birthday,
            'password' => Hash::make($request->password),
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="johndoe or john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->username)
                    ->orWhere('username', $request->username)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Regenerate session for security
        Session::regenerate();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }
    // google auth
    public function redirectToGoogle()
    {
        Log::info('Backend: Redirecting to Google OAuth');
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            Log::info('Backend: Handling Google OAuth callback');
            $googleUser = Socialite::driver('google')->stateless()->user();
            Log::info('Backend: Google user retrieved', ['email' => $googleUser->getEmail(), 'name' => $googleUser->getName()]);

            // Check if the user exists before creating/updating
            $existingUser = User::where('email', $googleUser->getEmail())->first();

             // If existing, update info
            if ($existingUser) {
                Log::info('Backend: Existing user found, updating Google info');
                $existingUser->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
                $user = $existingUser;
                // Ensure existing users are marked as completed (they already have profiles)
                if (!$user->is_completed) {
                    $user->update(['is_completed' => true]);
                }
            }
            else{
                Log::info('Backend: Creating new user from Google OAuth');
                 // Find or create the user
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'username' => explode('@', $googleUser->getEmail())[0], // Use part before @ as username
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(bin2hex(random_bytes(16))),
                    'role' => 'user',
                    'email_verified_at' => now(),
                    'is_completed' => false // Mark as incomplete for Google users
                ]
            );

            }


            // Generate a token (if using Sanctum or JWT)
            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Backend: Token generated, redirecting to frontend', ['user_id' => $user->id]);

            // Redirect to your frontend with the token
            Log::info('Backend: Redirecting user', ['user_id' => $user->id, 'is_completed' => $user->is_completed, 'email' => $user->email]);
            return redirect("http://localhost:5173/google/callback?token={$token}&user=" . urlencode(json_encode(
                $user->toArray()
            )));

        } catch (\Exception $e) {
            Log::error('Backend: Google OAuth callback failed', ['error' => $e->getMessage()]);
            return redirect("http://localhost:5173/?error=" . urlencode($e->getMessage()));
        }
    }

    public function completeProfile(Request $request)
    {
        Log::info('Backend: Complete profile request received', ['user_id' => $request->user()->id ?? 'unknown']);

        $request->validate([
            'birthday' => 'required|date|before:today',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/',
                'confirmed',
            ],
        ]);

        $user = $request->user();

        if ($user->is_completed) {
            Log::info('Backend: Profile already completed', ['user_id' => $user->id]);
            return response()->json(['message' => 'Profile already completed.'], 400);
        }

        $user->update([
            'birthday' => $request->birthday,
            'password' => Hash::make($request->password),
            'is_completed' => true,
        ]);

        Log::info('Backend: Profile completed successfully', ['user_id' => $user->id]);
        return response()->json(['message' => 'Profile completed successfully.']);
    }
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user information",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Send password reset email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset email sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset email sent',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/email/verify/{id}/{hash}",
     *     summary="Verify user email",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email verified successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification link"
     *     )
     * )
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/email/resend",
     *     summary="Resend email verification",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification email sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent',
        ]);
    }
}