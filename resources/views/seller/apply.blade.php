<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to Become a Seller</title>
    <style>
        body { font-family: sans-serif; max-width: 580px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .subtitle { color: #555; margin: 0 0 28px; font-size: 0.95rem; line-height: 1.6; }
        .field { margin-bottom: 18px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
        textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
            border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;
            font-family: inherit; resize: vertical; min-height: 160px;
        }
        textarea:focus { outline: none; border-color: #1a1a1a; }
        .hint { font-size: 0.8rem; color: #6b7280; margin-top: 3px; }
        .error { color: #dc2626; font-size: 0.82rem; margin-top: 4px; }
        .required { color: #dc2626; }
        .actions { display: flex; align-items: center; gap: 20px; margin-top: 28px; }
        .btn-primary {
            background: #1a1a1a; color: #fff; border: none; padding: 9px 24px;
            border-radius: 6px; cursor: pointer; font-size: 0.95rem;
        }
        .btn-primary:hover { background: #333; }
        a.back-link { color: #555; font-size: 0.9rem; text-decoration: none; }
        a.back-link:hover { color: #1a1a1a; text-decoration: underline; }
    </style>
</head>
<body>

    <h1>Apply to Become a Seller</h1>
    <p class="subtitle">
        Tell us about yourself and why you would like to sell on this platform.
        Applications are reviewed manually and you will be notified of the outcome.
    </p>

    <form method="POST" action="{{ route('seller.apply.store') }}">
        @csrf

        <div class="field">
            <label for="motivation">Your motivation <span class="required">*</span></label>
            <textarea
                id="motivation"
                name="motivation"
                maxlength="2000"
                required
            >{{ old('motivation') }}</textarea>
            <p class="hint">
                Minimum 50 characters, maximum 2000.
                Describe what you plan to sell and any relevant experience.
            </p>
            @error('motivation')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn-primary">Submit application</button>
            <a class="back-link" href="{{ route('dashboard') }}">Back to dashboard</a>
        </div>

    </form>

</body>
</html>