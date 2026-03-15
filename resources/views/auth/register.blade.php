<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - LocaFam</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(45deg, #667eea, #764ba2, #4ecdc4, #45b7d1);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="70" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="10" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="90" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .register-container {
            display: flex;
            background: white;
            border-radius: 4px;
            box-shadow: 
                0 25px 80px rgba(0, 0, 0, 0.25),
                0 15px 40px rgba(0, 0, 0, 0.15),
                0 8px 20px rgba(0, 0, 0, 0.1),
                0 4px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            min-height: 700px;
            position: relative;
            z-index: 1;
        }
        
        .register-sidebar {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .register-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="20" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="20" cy="90" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }
        
        .sidebar-content {
            position: relative;
            z-index: 1;
        }
        
        .sidebar-icon {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            margin: 0 auto 30px auto;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.2),
                0 8px 20px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        
        .sidebar-content h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }
        
        .sidebar-content p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 300px;
        }
        
        .register-form {
            flex: 1.2;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }
        
        .form-header {
            margin-bottom: 40px;
        }
        
        .form-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        
        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 24px;
            flex: 1;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #ecf0f1;
            border-radius: 4px;
            font-size: 16px;
            color: #2c3e50;
            transition: all 0.3s ease;
            background: #fafbfc;
            box-shadow: 
                0 2px 8px rgba(0, 0, 0, 0.05),
                inset 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 5;
        }
        
        .form-group input::placeholder {
            color: #bdc3c7;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        
        .form-group input:disabled {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
        }
        
        .form-group input:read-only {
            background: #f5f5f5;
            color: #666;
        }
        
        .input-group {
            display: flex;
            gap: 8px;
        }
        
        .input-group input {
            flex: 1;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #bdc3c7;
            font-size: 20px;
            pointer-events: none;
        }
        
        
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            position: relative;
            z-index: 10;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #2980b9;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover:not(:disabled) {
            background: #229954;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: #7f8c8d;
        }
        
        .btn-register {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 
                0 8px 25px rgba(52, 152, 219, 0.3),
                0 4px 12px rgba(52, 152, 219, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 10;
        }
        
        .btn-register:hover:not(:disabled) {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-2px);
            box-shadow: 
                0 12px 35px rgba(52, 152, 219, 0.4),
                0 6px 18px rgba(52, 152, 219, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
            color: #bdc3c7;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 10;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ecf0f1;
            z-index: 1;
        }
        
        .divider span {
            background: white;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .login-link {
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            position: relative;
            z-index: 10;
        }
        
        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            z-index: 999;
            display: inline-block;
            padding: 4px 8px;
        }
        
        .login-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success-message {
            background: #efe;
            color: #2a7;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .info-message {
            background: #e3f2fd;
            color: #1565c0;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .verification-status {
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .verification-status.verified {
            color: #27ae60;
        }
        
        .verification-status.pending {
            color: #f39c12;
        }
        
        .verification-status.error {
            color: #e74c3c;
        }
        
        .countdown {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 4px;
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
        
        .hidden {
            display: none;
        }
        
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .register-sidebar {
                padding: 40px 30px;
                min-height: 300px;
            }
            
            .sidebar-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }
            
            .sidebar-content h2 {
                font-size: 24px;
            }
            
            .sidebar-content p {
                font-size: 16px;
            }
            
            .register-form {
                padding: 40px 30px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-sidebar">
            <div class="sidebar-content">
                <div class="sidebar-icon">
                    <img src="{{ asset('locafam.svg') }}" alt="LocaFam" style="width: 80px; height: 80px;">
                </div>
                <h2>LocaFam</h2>
                <p>Join families worldwide keeping their loved ones safe with real-time location tracking</p>
            </div>
        </div>
        
        <div class="register-form">
            <div class="form-header">
                <h1>Create Account</h1>
                <p>Start your tracking journey today</p>
            </div>
            
            @if ($errors->any())
                <div class="error-message">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div id="alert-container"></div>
            
            <form method="POST" action="{{ route('register.post') }}" id="registration-form">
                @csrf
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}"
                            required 
                            autofocus
                            placeholder="Enter your full name"
                        >
                        <span class="input-icon material-icons">person</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username') }}"
                            required
                            placeholder="Choose username"
                        >
                        <span class="input-icon material-icons">alternate_email</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required
                            placeholder="your@company.com"
                        >
                        <span class="input-icon material-icons">email</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <div class="input-group">
                        <div class="input-wrapper" style="flex: 1;">
                            <input 
                                type="tel" 
                                id="phone_number" 
                                name="phone_number" 
                                value="{{ old('phone_number') }}"
                                required
                                placeholder="+60 12-345 6789"
                                pattern="^(0[0-9]{8,10}|\+60[0-9]{8,10})$"
                            >
                            <span class="input-icon material-icons">phone</span>
                        </div>
                        <button type="button" id="send-phone-otp" class="btn btn-primary">
                            Send OTP
                        </button>
                    </div>
                    <div id="phone-verification-status" class="verification-status hidden">
                        <span id="phone-status-text"></span>
                    </div>
                    <div id="phone-countdown" class="countdown hidden"></div>
                </div>
                
                <div class="form-group hidden" id="phone-otp-group">
                    <label for="phone_otp">Phone Verification Code</label>
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="phone_otp" 
                            name="phone_otp"
                            placeholder="Enter 6-digit code"
                            maxlength="6"
                            pattern="[0-9]{6}"
                        >
                        <button type="button" id="verify-phone-otp" class="btn btn-success">
                            Verify
                        </button>
                        <button type="button" id="resend-phone-otp" class="btn btn-secondary hidden">
                            Resend
                        </button>
                    </div>
                    <div id="phone-otp-countdown" class="countdown hidden"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Create password"
                                minlength="6"
                            >
                            <span class="input-icon material-icons">lock</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                required
                                placeholder="Confirm password"
                            >
                            <span class="input-icon material-icons">lock</span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-register" id="create-account-btn" disabled>
                    Create Account
                </button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>
        </div>
    </div>

    <script>
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // State variables
        let phoneVerified = false;
        let phoneOtpExpiry = null;
        let resendCooldown = null;
        
        // DOM elements
        const phoneInput = document.getElementById('phone_number');
        const sendPhoneOtpBtn = document.getElementById('send-phone-otp');
        const phoneOtpGroup = document.getElementById('phone-otp-group');
        const phoneOtpInput = document.getElementById('phone_otp');
        const verifyPhoneOtpBtn = document.getElementById('verify-phone-otp');
        const resendPhoneOtpBtn = document.getElementById('resend-phone-otp');
        const createAccountBtn = document.getElementById('create-account-btn');
        const phoneVerificationStatus = document.getElementById('phone-verification-status');
        const phoneStatusText = document.getElementById('phone-status-text');
        const phoneCountdown = document.getElementById('phone-countdown');
        const phoneOtpCountdown = document.getElementById('phone-otp-countdown');
        const alertContainer = document.getElementById('alert-container');
        
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
        
        // Update create account button state
        function updateCreateAccountButton() {
            createAccountBtn.disabled = !phoneVerified;
        }
        
        // Format phone number
        function formatPhoneNumber(phone) {
            // Remove spaces and dashes
            phone = phone.replace(/[\s\-]/g, '');
            
            // If starts with 0, keep as is for Malaysian format
            if (phone.startsWith('0')) {
                return phone;
            }
            
            // If starts with +60, convert to 0 format
            if (phone.startsWith('+60')) {
                return '0' + phone.substring(3);
            }
            
            // If starts with 60, convert to 0 format
            if (phone.startsWith('60')) {
                return '0' + phone.substring(2);
            }
            
            return phone;
        }
        
        // Send phone OTP
        sendPhoneOtpBtn.addEventListener('click', async () => {
            const phoneNumber = phoneInput.value.trim();
            
            if (!phoneNumber) {
                showAlert('Please enter your phone number');
                return;
            }
            
            // Validate phone format
            const phoneRegex = /^(0[0-9]{8,10}|\+60[0-9]{8,10})$/;
            if (!phoneRegex.test(phoneNumber)) {
                showAlert('Please enter a valid Malaysian phone number (e.g., 0123456789)');
                return;
            }
            
            // Show loading state
            const originalText = sendPhoneOtpBtn.innerHTML;
            sendPhoneOtpBtn.disabled = true;
            sendPhoneOtpBtn.innerHTML = '<span class="spinner"></span> Sending...';
            
            try {
                const response = await fetch('/api/otp/send-phone', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        phone_number: phoneNumber
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('OTP sent to your phone number. Please check your SMS.', 'success');
                    
                    // Show OTP input group
                    phoneOtpGroup.classList.remove('hidden');
                    phoneOtpInput.focus();
                    
                    // Show verification status
                    phoneVerificationStatus.classList.remove('hidden');
                    phoneVerificationStatus.className = 'verification-status pending';
                    phoneStatusText.textContent = '📱 OTP sent to your phone';
                    
                    // Start OTP expiry countdown
                    phoneOtpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes
                    startOtpCountdown();
                    
                    // Hide send button (but keep phone input enabled until verification)
                    sendPhoneOtpBtn.style.display = 'none';
                    
                } else {
                    showAlert(data.message || 'Failed to send OTP');
                }
            } catch (error) {
                console.error('Send OTP error:', error);
                showAlert('Failed to send OTP. Please try again.');
            } finally {
                sendPhoneOtpBtn.disabled = false;
                sendPhoneOtpBtn.innerHTML = originalText;
            }
        });
        
        // Verify phone OTP
        verifyPhoneOtpBtn.addEventListener('click', async () => {
            const phoneNumber = phoneInput.value.trim();
            const otpCode = phoneOtpInput.value.trim();
            
            if (!otpCode || otpCode.length !== 6) {
                showAlert('Please enter the 6-digit verification code');
                return;
            }
            
            // Show loading state
            const originalText = verifyPhoneOtpBtn.innerHTML;
            verifyPhoneOtpBtn.disabled = true;
            verifyPhoneOtpBtn.innerHTML = '<span class="spinner"></span> Verifying...';
            
            try {
                const response = await fetch('/api/otp/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        identifier: phoneNumber,
                        code: otpCode,
                        type: 'phone'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Phone number verified successfully!', 'success');
                    
                    // Update verification status
                    phoneVerificationStatus.className = 'verification-status verified';
                    phoneStatusText.textContent = '✅ Phone verified';
                    
                    // Hide OTP input and countdown
                    phoneOtpInput.disabled = true;
                    verifyPhoneOtpBtn.style.display = 'none';
                    resendPhoneOtpBtn.style.display = 'none';
                    phoneOtpCountdown.classList.add('hidden');
                    
                    // IMPORTANT: Don't disable phone input, just make it readonly
                    // This ensures the value is still submitted with the form
                    phoneInput.readOnly = true;
                    phoneInput.style.backgroundColor = '#f5f5f5';
                    phoneInput.style.color = '#666';
                    
                    // Mark as verified
                    phoneVerified = true;
                    updateCreateAccountButton();
                    
                } else {
                    showAlert(data.message || 'Invalid verification code');
                    
                    if (data.attempts_remaining !== undefined) {
                        showAlert(`${data.attempts_remaining} attempts remaining`, 'info');
                    }
                    
                    if (data.code === 'MAX_ATTEMPTS_EXCEEDED') {
                        // Reset form to allow new OTP request
                        resetPhoneVerification();
                    }
                }
            } catch (error) {
                console.error('Verify OTP error:', error);
                showAlert('Failed to verify OTP. Please try again.');
            } finally {
                verifyPhoneOtpBtn.disabled = false;
                verifyPhoneOtpBtn.innerHTML = originalText;
            }
        });
        
        // Resend phone OTP
        resendPhoneOtpBtn.addEventListener('click', async () => {
            const phoneNumber = phoneInput.value.trim();
            
            // Show loading state
            const originalText = resendPhoneOtpBtn.innerHTML;
            resendPhoneOtpBtn.disabled = true;
            resendPhoneOtpBtn.innerHTML = '<span class="spinner"></span> Sending...';
            
            try {
                const response = await fetch('/api/otp/resend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        identifier: phoneNumber,
                        type: 'phone'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('New OTP sent to your phone number.', 'success');
                    
                    // Reset OTP expiry countdown
                    phoneOtpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes
                    startOtpCountdown();
                    
                    // Clear OTP input
                    phoneOtpInput.value = '';
                    phoneOtpInput.focus();
                    
                    // Hide resend button temporarily
                    resendPhoneOtpBtn.classList.add('hidden');
                    
                } else {
                    showAlert(data.message || 'Failed to resend OTP');
                    
                    if (data.time_remaining) {
                        startResendCountdown(data.time_remaining);
                    }
                }
            } catch (error) {
                console.error('Resend OTP error:', error);
                showAlert('Failed to resend OTP. Please try again.');
            } finally {
                resendPhoneOtpBtn.disabled = false;
                resendPhoneOtpBtn.innerHTML = originalText;
            }
        });
        
        // Start OTP expiry countdown
        function startOtpCountdown() {
            phoneOtpCountdown.classList.remove('hidden');
            
            const countdown = setInterval(() => {
                const remaining = Math.max(0, phoneOtpExpiry - Date.now());
                
                if (remaining <= 0) {
                    clearInterval(countdown);
                    phoneOtpCountdown.textContent = 'OTP expired. Please request a new one.';
                    phoneOtpCountdown.style.color = '#e74c3c';
                    
                    // Show resend button
                    resendPhoneOtpBtn.classList.remove('hidden');
                    return;
                }
                
                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                phoneOtpCountdown.textContent = `OTP expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
                phoneOtpCountdown.style.color = '#7f8c8d';
                
                // Show resend button when 1 minute remaining
                if (remaining <= 60000) {
                    resendPhoneOtpBtn.classList.remove('hidden');
                }
            }, 1000);
        }
        
        // Start resend cooldown
        function startResendCountdown(seconds) {
            resendCooldown = Date.now() + (seconds * 1000);
            
            const countdown = setInterval(() => {
                const remaining = Math.max(0, resendCooldown - Date.now());
                
                if (remaining <= 0) {
                    clearInterval(countdown);
                    resendPhoneOtpBtn.classList.remove('hidden');
                    return;
                }
                
                const secs = Math.ceil(remaining / 1000);
                phoneCountdown.textContent = `Resend available in ${secs} seconds`;
                phoneCountdown.classList.remove('hidden');
            }, 1000);
        }
        
        // Reset phone verification
        function resetPhoneVerification() {
            phoneInput.disabled = false;
            phoneInput.readOnly = false;
            phoneInput.style.backgroundColor = '';
            phoneInput.style.color = '';
            sendPhoneOtpBtn.style.display = 'inline-flex';
            phoneOtpGroup.classList.add('hidden');
            phoneVerificationStatus.classList.add('hidden');
            phoneCountdown.classList.add('hidden');
            phoneOtpCountdown.classList.add('hidden');
            phoneVerified = false;
            updateCreateAccountButton();
        }
        
        // Auto-format phone input
        phoneInput.addEventListener('input', (e) => {
            e.target.value = formatPhoneNumber(e.target.value);
        });
        
        // Auto-format OTP input (numbers only)
        phoneOtpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        // Initialize button state
        updateCreateAccountButton();
    </script>
</body>
</html>
