<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body { font-family: sans-serif; max-width: 480px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.4rem; margin-bottom: 8px; }
        p { color: #555; line-height: 1.6; margin: 8px 0 20px; font-size: 0.95rem; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
        input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
        input:focus { outline: none; border-color: #1a1a1a; }
        .error { color: #dc2626; font-size: 0.82rem; margin-top: 4px; }
        .success { background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.9rem; color: #166534; }
        button { width: 100%; background: #1a1a1a; color: #fff; border: none; padding: 10px; border-radius: 6px; font-size: 0.95rem; cursor: pointer; margin-top: 16px; }
        button:hover { background: #333; }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 0.85rem; color: #555; text-decoration: none; }
        .back-link:hover { color: #1a1a1a; }
    </style>
</head>
<body>

    <h1>Forgot your password?</h1>
    <p>Enter your email address and we will send you a link to reset your password.</p>

    @if (session('status'))
        <div class="success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <label for="email">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
            >
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Send reset link</button>
    </form>

    <a class="back-link" href="{{ route('login') }}">Back to login</a>

</body>
</html>