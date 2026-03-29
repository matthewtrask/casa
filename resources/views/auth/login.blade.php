<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa – Sign in</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:       #faf8f5;
            --surface:  #ffffff;
            --surface-2:#f4f1eb;
            --text:     #2c2825;
            --text-muted:#7d7268;
            --text-light:#b5ada4;
            --border:   #e5dfd5;
            --ok:       #16a34a;
            --critical: #dc2626;
            --critical-soft:#fee2e2;
            --radius:   14px;
            --radius-sm:8px;
            --shadow-lg:0 8px 32px rgba(44,40,37,.14), 0 2px 8px rgba(44,40,37,.08);
            --font-display:'Lora', Georgia, serif;
            --font-body:'DM Sans', system-ui, sans-serif;
            --ease: 150ms ease;
        }
        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        /* Decorative background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(22,163,74,.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(139,92,246,.05) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 80%, rgba(245,158,11,.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-wrap {
            width: 100%;
            max-width: 380px;
            position: relative;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-brand-icon { font-size: 48px; margin-bottom: 8px; }
        .login-brand-name {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 600;
            letter-spacing: -.5px;
            color: var(--text);
        }
        .login-brand-sub { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px 24px;
            box-shadow: var(--shadow-lg);
        }

        .error-banner {
            background: var(--critical-soft);
            border: 1px solid #fecaca;
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 13px;
            color: #7f1d1d;
            font-weight: 500;
        }

        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-family: var(--font-body);
            color: var(--text);
            background: var(--surface);
            transition: border-color var(--ease), box-shadow var(--ease);
            -webkit-appearance: none;
            min-height: 46px;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--text);
            box-shadow: 0 0 0 3px rgba(44,40,37,.07);
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--text);
            cursor: pointer;
        }
        .remember-row label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .btn-signin {
            width: 100%;
            padding: 13px 20px;
            background: var(--text);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--ease), transform var(--ease);
            min-height: 48px;
        }
        .btn-signin:hover { background: #1a1714; }
        .btn-signin:active { transform: scale(0.98); }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-light);
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <div class="login-brand-icon">🏠</div>
        <div class="login-brand-name">Casa</div>
        <div class="login-brand-sub">Your home, managed.</div>
    </div>

    <div class="login-card">
        @if ($errors->any())
        <div class="error-banner">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="{{ old('email') }}" required autofocus autocomplete="email"
                       placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input"
                       required autocomplete="current-password" placeholder="••••••••">
            </div>
            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Keep me signed in</label>
            </div>
            <button type="submit" class="btn-signin">Sign in</button>
        </form>
    </div>

    <div class="login-footer">Casa — your private household manager</div>
</div>
</body>
</html>
