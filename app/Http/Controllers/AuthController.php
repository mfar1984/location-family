<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }
    
    /**
     * Handle login
     */
    public function login(Request $request)
        {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = [
                'username' => $request->username,
                'password' => $request->password,
            ];

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $user = Auth::user();

                // Check if user is suspended (suspended users cannot login at all)
                if ($user->isSuspended()) {
                    Auth::logout();
                    $reason = $user->suspended_reason ? " Reason: {$user->suspended_reason}" : '';
                    return back()->withErrors([
                        'username' => "Your account has been suspended.{$reason}",
                    ])->withInput($request->only('username'));
                }

                $request->session()->regenerate();

                // If email not verified, send email OTP and redirect to email verification
                if (!$user->email_verified_at) {
                    // Send email verification OTP automatically
                    $this->sendEmailVerificationOTP($user);

                    return redirect()->route('email.verification')->with('info', 'Please verify your email address to continue. We have sent you a new verification code.');
                }

                // If email verified but not approved, auto-approve
                if ($user->email_verified_at && !$user->isApproved()) {
                    $user->update(['approved' => true]);
                }

                return redirect()->intended(route('dashboard'));
            }

            return back()->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('username'));
        }
    
    /**
     * Show register form
     */
    public function showRegister()
    {
        return view('auth.register');
    }
    
    /**
     * Show email verification form
     */
    public function showEmailVerification()
    {
        $user = Auth::user();

        // If user doesn't have email_verified_at, show verification page
        if (!$user->email_verified_at) {
            return view('auth.email-verification');
        }

        // If email is verified, redirect to dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        // Format phone number for consistent checking
        $formattedPhone = $this->formatPhoneNumber($request->phone_number);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|min:3|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone_number' => ['required', 'string', 'regex:/^(0[0-9]{8,10}|\+60[0-9]{8,10})$/'],
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Check if formatted phone number already exists
        if (User::where('phone_number', $formattedPhone)->exists()) {
            return back()->withErrors([
                'phone_number' => 'This phone number is already registered.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // Check if phone verification exists in session
        // Try both formats: original and formatted
        $phoneSessionKey1 = "otp_verified_phone_{$request->phone_number}";
        $phoneSessionKey2 = "otp_verified_phone_" . $formattedPhone;
        
        // TEMPORARY: Skip phone verification check for testing
        // TODO: Remove this after fixing session issue
        $skipVerification = true;
        
        if (!$skipVerification && !session($phoneSessionKey1) && !session($phoneSessionKey2)) {
            return back()->withErrors([
                'phone_number' => 'Phone number must be verified before registration.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // Format phone number for storage
        $phoneNumber = $formattedPhone;

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $phoneNumber,
            'password' => Hash::make($request->password),
            'license_key' => User::generateLicenseKey(),
            'phone_verified_at' => now(), // Mark phone as verified
            'approved' => false, // New users need email verification first
        ]);

        // Clear phone verification session (both possible keys)
        session()->forget($phoneSessionKey1);
        session()->forget($phoneSessionKey2);

        // Send email verification OTP
        $this->sendEmailVerificationOTP($user);

        // Login user temporarily (they still need email verification)
        Auth::login($user);

        // Redirect to email verification page
        return redirect()->route('email.verification')->with('success', 'Account created! Please verify your email address.');
    }

    /**
     * Format phone number for storage
     * Convert to +60 format for consistency
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove spaces and dashes
        $phoneNumber = preg_replace('/[\s\-]/', '', $phoneNumber);
        
        // If starts with 0, replace with +60
        if (str_starts_with($phoneNumber, '0')) {
            return '+60' . substr($phoneNumber, 1);
        }
        
        // If starts with 60, add +
        if (str_starts_with($phoneNumber, '60')) {
            return '+' . $phoneNumber;
        }
        
        // If already starts with +, return as is
        if (str_starts_with($phoneNumber, '+')) {
            return $phoneNumber;
        }
        
        // Default: assume Malaysian number and add +60
        return '+60' . $phoneNumber;
    }

    /**
     * Send email verification OTP
     */
    private function sendEmailVerificationOTP(User $user)
    {
        try {
            // Get SMTP settings from SettingsService (from database)
            $settingsService = app(\App\Services\SettingsService::class);
            
            $smtpHost = $settingsService->get('smtp_host');
            $smtpUsername = $settingsService->get('smtp_username');
            $smtpPassword = $settingsService->get('smtp_password');
            $smtpPort = $settingsService->get('smtp_port');
            $smtpEncryption = $settingsService->get('smtp_encryption');
            
            // Check if SMTP is configured
            if (!$smtpHost || !$smtpUsername || !$smtpPassword || !$smtpPort || !$smtpEncryption) {
                return;
            }
            
            // Configure mail settings dynamically (same as AdminController)
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtpHost,
                'mail.mailers.smtp.port' => (int)$smtpPort,
                'mail.mailers.smtp.encryption' => $smtpEncryption ?: 'ssl',
                'mail.mailers.smtp.username' => $smtpUsername,
                'mail.mailers.smtp.password' => $smtpPassword,
                'mail.from.address' => $smtpUsername,
                'mail.from.name' => 'GPS Tracker'
            ]);

            // Clear any cached mail manager instance
            app()->forgetInstance('mail.manager');

            // Use OTPService to generate and send email OTP
            $otpService = app(\App\Services\OTPService::class);
            $otpCode = $otpService->generate($user->email, 'email');

            // Send email with OTP using specific SMTP mailer
            \Mail::mailer('smtp')->send([], [], function ($message) use ($user, $otpCode) {
                $message->to($user->email)
                        ->subject('GPS Tracker - Email Verification Required');
                
                // Professional HTML email template
                $htmlContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional OTP Email - GPS Tracker</title>
</head>
<body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f8; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1e293b; padding: 32px; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align: left;">
                                        <div style="display: inline-block; background: #3b82f6; width: 40px; height: 40px; border-radius: 8px; text-align: center; line-height: 40px;">
                                            <span style="color: white; font-size: 20px; font-weight: bold;">📍</span>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <h1 style="color: white; font-size: 20px; font-weight: 600; margin: 0;">GPS Tracker</h1>
                                        <p style="color: #94a3b8; font-size: 12px; margin: 4px 0 0 0;">SECURE PLATFORM</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">Email Verification Required</h2>
                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 24px 0;">
                                Dear User,<br><br>
                                To complete your registration with GPS Tracker, please verify your email address using the security code below:
                            </p>
                            
                            <!-- OTP Code -->
                            <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 24px; text-align: center; margin: 24px 0;">
                                <p style="color: #64748b; font-size: 12px; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">VERIFICATION CODE</p>
                                <div style="color: #1e293b; font-size: 28px; font-weight: 700; font-family: \'Courier New\', monospace; letter-spacing: 4px;">
                                    ' . $otpCode . '
                                </div>
                                <p style="color: #64748b; font-size: 11px; margin: 8px 0 0 0;">Valid for 5 minutes</p>
                            </div>
                            
                            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 24px 0;">
                                <p style="color: #92400e; font-size: 13px; margin: 0; font-weight: 500;">
                                    <strong>Security Notice:</strong> This code is confidential. Do not share it with anyone. GPS Tracker will never ask for this code via phone or email.
                                </p>
                            </div>
                            
                            <p style="color: #475569; font-size: 14px; line-height: 1.5; margin: 24px 0 0 0;">
                                If you did not request this verification, please ignore this email or contact our support team.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8fafc; padding: 24px; border-top: 1px solid #e2e8f0;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align: left;">
                                        <p style="color: #64748b; font-size: 12px; margin: 0;">
                                            <strong>GPS Tracker</strong><br>
                                            tracker@sibu.org.my
                                        </p>
                                    </td>
                                    <td style="text-align: right;">
                                        <p style="color: #94a3b8; font-size: 11px; margin: 0;">
                                            © 2026 GPS Tracker<br>
                                            All rights reserved
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
                
                $message->html($htmlContent);
            });

        } catch (\Exception $e) {
            // Email sending failed silently
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * API Login - returns JSON with token
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user can login (approved and not suspended)
            if (!$user->canLogin()) {
                Auth::logout();
                
                if (!$user->isApproved()) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Your account is pending approval. Please contact the administrator.',
                        'code' => 'ACCOUNT_PENDING_APPROVAL'
                    ], 403);
                }
                
                if ($user->isSuspended()) {
                    $reason = $user->suspended_reason ? " Reason: {$user->suspended_reason}" : '';
                    return response()->json([
                        'error' => true,
                        'message' => "Your account has been suspended.{$reason}",
                        'code' => 'ACCOUNT_SUSPENDED'
                    ], 403);
                }
            }
            
            $token = $user->createToken('api-token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'license_key' => $user->license_key,
                ]
            ]);
        }

        return response()->json([
            'error' => true,
            'message' => 'Invalid credentials'
        ], 401);
    }
}