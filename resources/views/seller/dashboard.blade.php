<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.6rem; margin-bottom: 4px; }
        p { color: #555; margin: 6px 0; }
        .badge { display: inline-block; background: #16a34a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }
        form { margin-top: 32px; }
        button { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button:hover { background: #b91c1c; }
        nav { display: flex; flex-direction: column; gap: 10px; margin: 24px 0; }
        nav a { display: block; padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #1a1a1a; }
        nav a:hover { background: #f9fafb; }
        .nav-label { font-weight: 600; font-size: 0.95rem; }
        .nav-hint { display: block; font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0 0 28px; }
    </style>
</head>
<body>
    <span class="badge">Seller</span>
    <h1>Welcome, {{ auth()->user()->name }}</h1>
    <p>Your seller dashboard is ready. Listing and auction tools will be added in a later phase.</p>

    <nav>
        <a href="{{ route('seller.listings.index') }}">
            <span class="nav-label">My Listings</span>
            <span class="nav-hint">View and manage your auction listings.</span>
        </a>
    </nav>

    <hr class="divider">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log Out</button>
    </form>
</body>
</html>