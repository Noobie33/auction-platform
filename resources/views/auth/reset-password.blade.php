<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { font-family: sans-serif; max-width: 480px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.4rem; margin-bottom: 20px; }
        .field { margin-bottom: 16px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
        input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
        input:focus { outline: none; border-color: #1a1a1a; }
        .error { color: #dc2626; font-size: 0.82rem; margin-top: 4px; }
        button { width: 100%; background: #1a1a1a; color: #fff; border: none; padding: 10px; border-radius: 6px; font-size: 0.95rem; cursor: pointer; margin-top: 4px; }
        button:hover { background: #333; }
    </style>
</head>
<body>

    <h1>Reset your password</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- Token and email are submitted as hidden fields.
             The broker uses both to validate and locate the reset request. --}}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                autofocus
                autocomplete="email"
            >
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">New password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            >
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            >
            @error('password_confirmation')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Reset password</button>
    </form>

</body>
</html>