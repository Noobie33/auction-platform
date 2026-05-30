<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body { font-family: sans-serif; max-width: 520px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.4rem; margin-bottom: 8px; }
        p { color: #555; line-height: 1.6; margin: 8px 0; }
        .notice { background: #fefce8; border: 1px solid #fde047; border-radius: 6px; padding: 12px 16px; margin: 20px 0; font-size: 0.9rem; color: #713f12; }
        .success { background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 12px 16px; margin: 20px 0; font-size: 0.9rem; color: #166534; }
        button { background: #1a1a1a; color: #fff; border: none; padding: 9px 22px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-top: 8px; }
        button:hover { background: #333; }
        .logout-link { display: inline-block; margin-top: 24px; font-size: 0.85rem; color: #888; text-decoration: none; }
        form.logout { display: inline; }
        form.logout button { background: none; color: #888; border: none; padding: 0; font-size: 0.85rem; cursor: pointer; text-decoration: underline; }
    </style>
</head>
<body>

    <h1>Verify your email address</h1>

    <p>
        Thanks for registering. Before you continue, please verify your email address
        by clicking the link we sent to <strong>{{ auth()->user()->email }}</strong>.
    </p>

    @if (session('status') === 'A fresh verification link has been sent to your email address.')
        <div class="success">
            A fresh verification link has been sent. Please check your inbox.
        </div>
    @endif

    <div class="notice">
        If you did not receive the email, check your spam folder or request a new link below.
    </div>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Resend verification email</button>
    </form>

    <br>

    <form class="logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log out</button>
    </form>

</body>
</html>