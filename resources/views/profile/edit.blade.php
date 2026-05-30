<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body { font-family: sans-serif; max-width: 580px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .subtitle { color: #555; margin: 0 0 28px; font-size: 0.95rem; }
        .field { margin-bottom: 18px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
        input, textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
            border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;
            font-family: inherit;
        }
        input:focus, textarea:focus { outline: none; border-color: #1a1a1a; }
        input[readonly] { background: #f9fafb; color: #6b7280; cursor: not-allowed; }
        textarea { resize: vertical; min-height: 100px; }
        .hint { font-size: 0.8rem; color: #6b7280; margin-top: 3px; }
        .error { color: #dc2626; font-size: 0.82rem; margin-top: 4px; }
        .success {
            background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px;
            padding: 12px 16px; margin-bottom: 24px; font-size: 0.9rem; color: #166534;
        }
        .badge-verified {
            display: inline-block; background: #16a34a; color: #fff;
            font-size: 0.72rem; padding: 2px 8px; border-radius: 99px;
            text-transform: uppercase; letter-spacing: 0.04em; margin-left: 6px;
            vertical-align: middle;
        }
        .badge-unverified {
            display: inline-block; background: #d97706; color: #fff;
            font-size: 0.72rem; padding: 2px 8px; border-radius: 99px;
            text-transform: uppercase; letter-spacing: 0.04em; margin-left: 6px;
            vertical-align: middle;
        }
        .required { color: #dc2626; }
        .actions { display: flex; align-items: center; gap: 20px; margin-top: 28px; }
        .btn-primary {
            background: #1a1a1a; color: #fff; border: none; padding: 9px 24px;
            border-radius: 6px; cursor: pointer; font-size: 0.95rem;
        }
        .btn-primary:hover { background: #333; }
        a.back-link { color: #555; font-size: 0.9rem; text-decoration: none; }
        a.back-link:hover { color: #1a1a1a; text-decoration: underline; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 36px 0; }
        .btn-logout {
            background: none; border: none; color: #888; font-size: 0.85rem;
            cursor: pointer; padding: 0; text-decoration: underline;
        }
        .btn-logout:hover { color: #1a1a1a; }
    </style>
</head>
<body>

    <h1>Edit Profile</h1>
    <p class="subtitle">Update your account information below.</p>

    @if (session('status'))
        <div class="success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        {{-- Email — read-only, change not supported here --}}
        <div class="field">
            <label>
                Email address
                @if (auth()->user()->hasVerifiedEmail())
                    <span class="badge-verified">Verified</span>
                @else
                    <span class="badge-unverified">Unverified</span>
                @endif
            </label>
            <input
                type="email"
                value="{{ auth()->user()->email }}"
                readonly
                autocomplete="off"
            >
            <p class="hint">Email address cannot be changed from this page.</p>
        </div>

        {{-- Name --}}
        <div class="field">
            <label for="name">Full name <span class="required">*</span></label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                autocomplete="name"
            >
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Username --}}
        <div class="field">
            <label for="username">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username', $user->username) }}"
                autocomplete="username"
            >
            <p class="hint">Letters, numbers, hyphens, and underscores only. Leave blank to remove.</p>
            @error('username')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div class="field">
            <label for="phone">Phone number</label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $user->phone) }}"
                autocomplete="tel"
            >
            @error('phone')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Bio --}}
        <div class="field">
            <label for="bio">Bio</label>
            <textarea
                id="bio"
                name="bio"
                maxlength="1000"
            >{{ old('bio', $user->bio) }}</textarea>
            <p class="hint">Maximum 1000 characters.</p>
            @error('bio')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn-primary">Save changes</button>
            <a class="back-link" href="{{ route('dashboard') }}">Back to dashboard</a>
        </div>

    </form>

    <hr class="divider">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Log out</button>
    </form>

</body>
</html>