<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Auction Platform</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
        }

        .card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.10);
            padding: 40px 36px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .card-header .brand {
            font-size: 22px;
            font-weight: 700;
            color: #b45309;
            letter-spacing: 0.5px;
        }

        .card-header h1 {
            font-size: 20px;
            font-weight: 600;
            margin-top: 8px;
            color: #1a1a1a;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fafafa;
            transition: border-color 0.15s;
            outline: none;
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: #b45309;
            background: #fff;
        }

        input.is-invalid {
            border-color: #dc2626;
        }

        .error-message {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }

        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }

        .error-alert p {
            font-size: 13px;
            color: #b91c1c;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
        }

        .remember-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #b45309;
            cursor: pointer;
        }

        .remember-row label {
            margin: 0;
            font-size: 13px;
            font-weight: 400;
            cursor: pointer;
            color: #374151;
        }

        .btn-primary {
            width: 100%;
            padding: 11px;
            background: #b45309;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-primary:hover {
            background: #92400e;
        }

        .card-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: #6b7280;
        }

        .card-footer a {
            color: #b45309;
            text-decoration: none;
            font-weight: 600;
        }

        .card-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="card-header">
        <div class="brand">BidVault</div>
        <h1>Sign in to your account</h1>
    </div>

    {{-- General error alert (e.g. account suspended) --}}
    @if ($errors->any())
        <div class="error-alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                autofocus
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
            >
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
            >
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="remember-row">
            <input
                type="checkbox"
                id="remember"
                name="remember"
                {{ old('remember') ? 'checked' : '' }}
            >
            <label for="remember">Keep me signed in</label>
        </div>

        <button type="submit" class="btn-primary">Sign In</button>

    </form>

    <div class="card-footer">
        Don't have an account?
        <a href="{{ route('register') }}">Create one</a>
    </div>

</div>

</body>
</html>