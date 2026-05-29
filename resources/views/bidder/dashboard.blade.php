<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidder Dashboard</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.6rem; margin-bottom: 4px; }
        p { color: #555; margin: 6px 0; }
        .badge { display: inline-block; background: #2563eb; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }
        form { margin-top: 32px; }
        button { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button:hover { background: #b91c1c; }
    </style>
</head>
<body>
    <span class="badge">Bidder</span>
    <h1>Welcome, {{ auth()->user()->name }}</h1>
    <p>Your bidder dashboard is ready. Auction browsing and bidding tools will be added in a later phase.</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log Out</button>
    </form>
</body>
</html>