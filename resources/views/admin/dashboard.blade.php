<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.6rem; margin-bottom: 4px; }
        p { color: #555; margin: 0 0 24px; }
        .badge { display: inline-block; background: #1a1a1a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }
        nav { display: flex; flex-direction: column; gap: 10px; margin-bottom: 36px; }
        nav a {
            display: inline-block; padding: 10px 16px; border: 1px solid #e5e7eb;
            border-radius: 6px; text-decoration: none; color: #1a1a1a; transition: background 0.1s;
        }
        nav a:hover { background: #f9fafb; }
        nav a span.nav-label { font-weight: 600; font-size: 0.95rem; }
        nav a span.nav-hint { display: block; font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        button { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button:hover { background: #b91c1c; }
    </style>
</head>
<body>

    <span class="badge">Admin</span>
    <h1>Welcome, {{ auth()->user()->name }}</h1>
    <p>Manage users, seller applications, and platform settings below.</p>

    <nav>
        <a href="{{ route('admin.users.index') }}">
            <span class="nav-label">User Management</span>
            <span class="nav-hint">View all registered users, roles, and account status.</span>
        </a>
        <a href="{{ route('admin.seller-applications.index') }}">
            <span class="nav-label">Seller Applications</span>
            <span class="nav-hint">Review pending seller applications and manage approvals.</span>
        </a>
    </nav>

    <hr class="divider">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log Out</button>
    </form>

</body>
</html>