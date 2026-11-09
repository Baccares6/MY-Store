<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #870000;
            --primary-hover: #600000;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --bg-card: #ffffff;
            --error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #31101e 100%);
            padding: 24px;
            color: var(--text-main);
        }

        .register-container {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            padding: 40px 36px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .header .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
            background: rgba(135, 0, 0, 0.08);
            padding: 6px 14px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }

        .header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .header p {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            font-family: inherit;
            border-radius: 10px;
            border: 1.5px solid var(--border-color);
            background-color: #f8fafc;
            color: var(--text-main);
            transition: all 0.2s ease;
            outline: none;
        }

        .input-wrapper input:focus {
            background-color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(135, 0, 0, 0.1);
        }

        .input-wrapper input.is-invalid {
            border-color: var(--error);
            background-color: #fffafb;
        }

        .error-message {
            color: var(--error);
            font-size: 12px;
            font-weight: 500;
            margin-top: 6px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            color: #ffffff;
            background: linear-gradient(135deg, #870000 0%, #a81c1c 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(135, 0, 0, 0.3);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #700000 0%, #8f1717 100%);
            transform: translateY(-1px);
        }

        .footer-note {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 24px;
        }

        .footer-note a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <div class="header">
            <span class="badge">E-Commerce Platform</span>
            <h2>Create an Account</h2>
            <p>Enter your details below to sign up</p>
        </div>

        <form action="{{ route('register.perform') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Username</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        placeholder="e.g. johndoe"
                        class="@error('name') is-invalid @enderror"
                    >
                </div>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="name@example.com"
                        class="@error('email') is-invalid @enderror"
                    >
                </div>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimum 6 characters"
                        class="@error('password') is-invalid @enderror"
                    >
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        placeholder="Re-type your password"
                    >
                </div>
            </div>

            <button type="submit" class="btn-submit">Register Account</button>
        </form>

        <div class="footer-note">
            Already have an account? <a href="{{ route('login') }}">Log In</a>
            <div style="margin-top: 8px;"><a href="{{ route('home') }}">← Back to Store</a></div>
        </div>
    </div>

</body>
</html>