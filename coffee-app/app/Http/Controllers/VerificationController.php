<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    /**
     * Send verification code to email
     */
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        $cacheKey = 'verification_code_' . $email;
        Cache::put($cacheKey, $code, now()->addMinutes(10));

        // Send email with code
        // In your controller
    try {
        Mail::to($email)->send(new VerificationCode($code));
        
        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('Email sending failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send verification code'
        ], 500);
    }
    }

    /**
     * Verify code
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $code = $request->code;
        $cacheKey = 'verification_code_' . $email;

        // Get stored code from cache
        $storedCode = Cache::get($cacheKey);

        if (!$storedCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Verification code expired or not found'
            ], 404);
        }

        if ($storedCode !== $code) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Code is valid - remove it from cache
        Cache::forget($cacheKey);

        // Mark email as verified in cache (valid for 1 hour)
        Cache::put('email_verified_' . $email, true, now()->addHour());

        return response()->json([
            'valid' => true,
            'message' => 'Email verified successfully'
        ], 200);
    }
}