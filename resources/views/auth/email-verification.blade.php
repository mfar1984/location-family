<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verification - LocaFam</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .logo p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .verification-info {
            margin-bottom: 30px;
        }
        
        .verification-info h2 {
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 20px;
        }
        
        .verification-info p {
            color: #7f8c8d;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        
        .email-display {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #2c3e50;
            margin: 16px 0;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-group {
            display: flex;
            gap: 8px;
        }
        
        .input-group input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            letter-spacing: 2px;
            font-weight: 600;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #5a6fd8;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: #7f8c8d;
        }
        
        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 16px;
        }
        
        .btn-verify:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }
        
        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success-message {
            background: #efe;
            color: #2a7;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .info-message {
            background: #e3f2fd;
            color: #1565c0;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .countdown {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 8px;
        }
        
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .logout-link {
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .logout-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .logout-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="logo">
            <h1>📍 Family Tracker</h1>
            <p>Email Verification</p>
        </div>
        
        <div class="verification-info">
            <h2>📧 Verify Your Email</h2>
            <p>We've sent a verification code to:</p>
            <div class="email-display">{{ Auth::user()->email }}</div>
            <p>Please enter the 6-digit code to verify your email address.</p>
        </div>
        
        <div id="alert-container"></div>
        
        <div class="form-group">
            <label for="email_otp">Verification Code</label>
            <div class="input-group">
                <input 
                    type="text" 
                    id="email_otp" 
                    placeholder="000000"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    autofocus
                >
                <button type="button" id="resend-email-otp" class="btn btn-secondary">
                    Resend
                </button>
            </div>
            <div id="email-countdown" class="countdown"></div>
        </div>
        
        <button type="button" class="btn-verify" id="verify-email-btn">
            Verify Email
        </button>
        
        <div class="status-pending">
            <strong>📋 Next Steps:</strong><br>
            After email verification, your account will be automatically approved and you can access the system immediately.
        </div>
        
        <div class="logout-link">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout and try different account
            </a>
        </div>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>

    <script>
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // DOM elements
        const emailOtpInput = document.getElementById('email_otp');
        const verifyEmailBtn = document.getElementById('verify-email-btn');
        const resendEmailOtpBtn = document.getElementById('resend-email-otp');
        const emailCountdown = document.getElementById('email-countdown');
        const alertContainer = document.getElementById('alert-container');
        
        // State
        let otpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes from now
        let resendCooldown = null;
        
        // Show alert message
        function showAlert(message, type = 'error') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `${type}-message`;
            alertDiv.textContent = message;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Verify email OTP
        verifyEmailBtn.addEventListener('click', async () => {
            const otpCode = emailOtpInput.value.trim();
            
            if (!otpCode || otpCode.length !== 6) {
                showAlert('Please enter the 6-digit verification code');
                return;
            }
            
            // Show loading state
            const originalText = verifyEmailBtn.innerHTML;
            verifyEmailBtn.disabled = true;
            verifyEmailBtn.innerHTML = '<span class="spinner"></span> Verifying...';
            
            try {
                const response = await fetch('/api/otp/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        identifier: '{{ Auth::user()->email }}',
                        code: otpCode,
                        type: 'email'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Email verified successfully! Redirecting...', 'success');
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                    
                } else {
                    showAlert(data.message || 'Invalid verification code');
                    
                    if (data.attempts_remaining !== undefined) {
                        showAlert(`${data.attempts_remaining} attempts remaining`, 'info');
                    }
                }
            } catch (error) {
                console.error('Verify email OTP error:', error);
                showAlert('Failed to verify email. Please try again.');
            } finally {
                verifyEmailBtn.disabled = false;
                verifyEmailBtn.innerHTML = originalText;
            }
        });
        
        // Resend email OTP
        resendEmailOtpBtn.addEventListener('click', async () => {
            // Show loading state
            const originalText = resendEmailOtpBtn.innerHTML;
            resendEmailOtpBtn.disabled = true;
            resendEmailOtpBtn.innerHTML = '<span class="spinner"></span> Sending...';
            
            try {
                const response = await fetch('/api/otp/resend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        identifier: '{{ Auth::user()->email }}',
                        type: 'email'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('New verification code sent to your email.', 'success');
                    
                    // Reset OTP expiry countdown
                    otpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes
                    
                    // Clear OTP input
                    emailOtpInput.value = '';
                    emailOtpInput.focus();
                    
                    // Start resend cooldown
                    startResendCooldown(60); // 60 seconds
                    
                } else {
                    showAlert(data.message || 'Failed to resend verification code');
                    
                    if (data.time_remaining) {
                        startResendCooldown(data.time_remaining);
                    }
                }
            } catch (error) {
                console.error('Resend email OTP error:', error);
                showAlert('Failed to resend verification code. Please try again.');
            } finally {
                resendEmailOtpBtn.disabled = false;
                resendEmailOtpBtn.innerHTML = originalText;
            }
        });
        
        // Start OTP expiry countdown
        function startOtpCountdown() {
            const countdown = setInterval(() => {
                const remaining = Math.max(0, otpExpiry - Date.now());
                
                if (remaining <= 0) {
                    clearInterval(countdown);
                    emailCountdown.textContent = 'Code expired. Please request a new one.';
                    emailCountdown.style.color = '#e74c3c';
                    return;
                }
                
                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                emailCountdown.textContent = `Code expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
                emailCountdown.style.color = '#7f8c8d';
            }, 1000);
        }
        
        // Start resend cooldown
        function startResendCooldown(seconds) {
            resendCooldown = Date.now() + (seconds * 1000);
            resendEmailOtpBtn.disabled = true;
            
            const countdown = setInterval(() => {
                const remaining = Math.max(0, resendCooldown - Date.now());
                
                if (remaining <= 0) {
                    clearInterval(countdown);
                    resendEmailOtpBtn.disabled = false;
                    resendEmailOtpBtn.textContent = 'Resend';
                    return;
                }
                
                const secs = Math.ceil(remaining / 1000);
                resendEmailOtpBtn.textContent = `Resend (${secs}s)`;
            }, 1000);
        }
        
        // Auto-format OTP input (numbers only)
        emailOtpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        // Enter key to verify
        emailOtpInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                verifyEmailBtn.click();
            }
        });
        
        // Initialize countdown
        startOtpCountdown();
        
        // Start with 60 second resend cooldown
        startResendCooldown(60);
    </script>
</body>
</html>