<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa — Sign In</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08), 0 20px 60px rgba(0,0,0,0.1);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo .icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .login-logo h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #22863a;
        }

        .login-logo p {
            color: #9ca3af;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
            color: #111827;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #22863a;
            box-shadow: 0 0 0 3px rgba(34,134,58,0.1);
        }

        .input-error {
            border-color: #ef4444 !important;
        }

        .error-message {
            color: #dc2626;
            font-size: 0.82rem;
            margin-top: 0.35rem;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-row input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: #22863a;
            cursor: pointer;
        }

        .remember-row label {
            margin: 0;
            font-weight: 400;
            color: #6b7280;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-login {
            display: block;
            width: 100%;
            background: #22863a;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            font-family: inherit;
        }

        .btn-login:hover {
            background: #1a6b2c;
        }

        .btn-login:active {
            transform: scale(0.99);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.75rem;
            color: #9ca3af;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <span class="icon">🏠</span>
            <h1>Casa</h1>
            <p>Your household, taken care of.</p>
        </div>

        @if ($errors->any())
            <div style="background:#fee2e2; color:#991b1b; border-radius:8px; padding:0.75rem 1rem; font-size:0.875rem; margin-bottom:1.25rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    class="{{ $errors->has('email') ? 'input-error' : '' }}"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="{{ $errors->has('email') ? 'input-error' : '' }}"
                >
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-login">Sign in</button>
        </form>

        <div class="login-footer">
            Casa · Home management for the Trask household
        </div>
    </div>
</body>
</html>
