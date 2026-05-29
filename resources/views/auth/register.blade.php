<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Auction Platform</title>
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
            padding: 32px 16px;
        }

        .card {
            background: #ffffff;
            width: 100%;
            max-width: 460px;
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

        .card-header p {
            margin-top: 6px;
            font-size: 13px;
            color: #6b7280;
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

        label .optional {
            font-weight: 400;
            color: #9ca3af;
            font-size: 12px;
            margin-left: 4px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"],
        input[type="tel"] {
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
        input[type="text"]:focus,
        input[type="tel"]:focus {
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

        .hint {
            font-size: 11px;
            color: #9ca3af;
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

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 22px 0;
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
        <h1>Create your account</h1>
        <p>Join as a bidder and start exploring auctions</p>
    </div>

    {{-- General error alert --}}
    @if ($errors->any())
        <div class="error-alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        {{-- Full name --}}
        <div class="form-group">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                autocomplete="name"
                autofocus
                class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
            >
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- Username --}}
        <div class="form-group">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                autocomplete="username"
                class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
            >
            @error('username')
                <p class="error-message">{{ $message }}</p>
            @else
                <p class="hint">3 to 30 characters. Letters, numbers, hyphens, and underscores only.</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
            >
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone (optional) --}}
        <div class="form-group">
            <label for="phone">
                Phone Number
                <span class="optional">(optional)</span>
            </label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                autocomplete="tel"
                class="{{ $errors->has('phone') ? 'is-invalid' : '' }}"
            >
            @error('phone')
                <p class="error-message">{{ $message }}</p>
            @else
                <p class="hint">Required later when completing a purchase.</p>
            @enderror
        </div>

        <hr class="divider">

        {{-- Password --}}
        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="new-password"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
            >
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @else
                <p class="hint">At least 8 characters with letters and numbers.</p>
            @enderror
        </div>

        {{-- Confirm password --}}
        {{-- name must be exactly password_confirmation for Laravel's confirmed rule --}}
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
            >
            @error('password_confirmation')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary">Create Account</button>

    </form>

    <div class="card-footer">
        Already have an account?
        <a href="{{ route('login') }}">Sign in</a>
    </div>

</div>

</body>
</html>