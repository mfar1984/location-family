<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LocaFam</title>
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
        
        .login-container {
            display: flex;
            background: white;
            border-radius: 4px;
            box-shadow: 
                0 25px 80px rgba(0, 0, 0, 0.25),
                0 15px 40px rgba(0, 0, 0, 0.15),
                0 8px 20px rgba(0, 0, 0, 0.1),
                0 4px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            min-height: 600px;
        }
        
        .login-sidebar {
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
        
        .login-sidebar::before {
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
        
        .login-form {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
        
        .form-field {
            margin-bottom: 24px;
        }
        
        .form-field label {
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
        
        .form-field input {
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
        }
        
        .form-field input::placeholder {
            color: #bdc3c7;
        }
        
        .form-field input:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
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
        
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: 18px;
            height: 18px;
            accent-color: #3498db;
        }
        
        .checkbox-group label {
            font-size: 14px;
            color: #7f8c8d;
            margin: 0;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 400;
        }
        
        .forgot-link {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .forgot-link:hover {
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
        
        .login-btn {
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
        
        .login-btn:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-2px);
            box-shadow: 
                0 12px 35px rgba(52, 152, 219, 0.4),
                0 6px 18px rgba(52, 152, 219, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
            color: #bdc3c7;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ecf0f1;
        }
        
        .divider span {
            background: white;
            padding: 0 20px;
            position: relative;
        }
        
        .register-link {
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            position: relative;
            z-index: 10;
        }
        
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            z-index: 10;
            display: inline-block;
            padding: 4px 8px;
        }
        
        .register-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .login-sidebar {
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
            
            .login-form {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-sidebar">
            <div class="sidebar-content">
                <div class="sidebar-icon">
                    <img src="{{ asset('locafam.svg') }}" alt="LocaFam" style="width: 80px; height: 80px;">
                </div>
                <h2>LocaFam</h2>
                <p>Keep your family safe with real-time location tracking and instant updates</p>
            </div>
        </div>
        
        <div class="login-form">
            <div class="form-header">
                <h1>Welcome Back</h1>
                <p>Please sign in to your account</p>
            </div>
            
            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-field">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username') }}"
                            placeholder="Enter your username"
                            required
                            autofocus
                        >
                        <span class="input-icon material-icons">person</span>
                    </div>
                </div>
                
                <div class="form-field">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                        <span class="input-icon material-icons">lock</span>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <div class="register-link">
                Don't have an account? <a href="{{ route('register') }}">Create account</a>
            </div>
        </div>
    </div>
</body>
</html>
