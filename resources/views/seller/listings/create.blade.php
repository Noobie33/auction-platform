<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing — Seller</title>
    <style>
        body             { font-family: sans-serif; max-width: 720px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1               { font-size: 1.6rem; margin-bottom: 4px; }
        p.subtitle       { color: #555; margin: 0 0 28px; }

        /* Seller brand — green badge */
        .badge           { display: inline-block; background: #16a34a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }

        .back-link       { display: inline-block; margin-bottom: 28px; font-size: 0.875rem; color: #6b7280; text-decoration: none; }
        .back-link:hover { color: #1a1a1a; }

        /* Section grouping */
        .section-title   { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #6b7280; margin: 32px 0 16px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }

        .form-group      { margin-bottom: 20px; }
        label            { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 6px; }
        .required-star   { color: #dc2626; }
        .hint            { font-size: 0.8rem; color: #6b7280; margin-top: 4px; }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        textarea,
        select           { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.9rem; color: #1a1a1a; box-sizing: border-box; font-family: inherit; }
        input:focus,
        textarea:focus,
        select:focus     { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,0.08); }
        textarea         { resize: vertical; min-height: 130px; }
        .is-invalid      { border-color: #dc2626; }

        /* Two-column grid for price fields */
        .grid-2          { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .checkbox-row    { display: flex; align-items: center; gap: 10px; }
        .checkbox-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
        .checkbox-row label { margin-bottom: 0; font-weight: normal; cursor: pointer; }

        .field-error     { font-size: 0.8rem; color: #dc2626; margin-top: 5px; }

        .actions         { display: flex; align-items: center; gap: 12px; margin-top: 36px; }
        .btn-primary     { background: #16a34a; color: #fff; border: none; padding: 10px 26px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600; }
        .btn-primary:hover { background: #15803d; }
        .btn-cancel      { display: inline-block; padding: 10px 20px; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #6b7280; font-size: 0.9rem; }
        .btn-cancel:hover { background: #f9fafb; color: #1a1a1a; }

        .divider         { border: none; border-top: 1px solid #e5e7eb; margin: 36px 0; }
        button.logout    { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button.logout:hover { background: #b91c1c; }
    </style>
</head>
<body>

    <span class="badge">Seller</span>
    <h1>Create Listing</h1>
    <p class="subtitle">Your listing will be saved as a draft. You can publish it when ready.</p>

    <a class="back-link" href="{{ route('seller.listings.index') }}">← Back to My Listings</a>

    <form method="POST" action="{{ route('seller.listings.store') }}">
        @csrf

        {{-- ── Category & Basics ───────────────────────────────────────────── --}}
        <p class="section-title">Listing Details</p>

        {{-- Category --}}
        <div class="form-group">
            <label for="category_id">
                Category <span class="required-star">*</span>
            </label>
            <select
                id="category_id"
                name="category_id"
                class="{{ $errors->has('category_id') ? 'is-invalid' : '' }}"
            >
                <option value="">— Select a category —</option>
                @foreach ($categories as $parent)
                    @if ($parent->children->isNotEmpty())
                        <optgroup label="{{ $parent->name }}">
                            @foreach ($parent->children as $child)
                                <option
                                    value="{{ $child->id }}"
                                    {{ old('category_id') == $child->id ? 'selected' : '' }}
                                >{{ $child->name }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option
                            value="{{ $parent->id }}"
                            {{ old('category_id') == $parent->id ? 'selected' : '' }}
                        >{{ $parent->name }}</option>
                    @endif
                @endforeach
            </select>
            @error('category_id')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Title --}}
        <div class="form-group">
            <label for="title">
                Title <span class="required-star">*</span>
            </label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title') }}"
                class="{{ $errors->has('title') ? 'is-invalid' : '' }}"
                placeholder="e.g. Canon EOS 5D Mark IV Camera Body"
                maxlength="255"
                autofocus
            >
            @error('title')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div class="form-group">
            <label for="description">
                Description <span class="required-star">*</span>
            </label>
            <textarea
                id="description"
                name="description"
                class="{{ $errors->has('description') ? 'is-invalid' : '' }}"
                placeholder="Describe the item in detail — condition, features, included accessories, etc. Minimum 20 characters."
                maxlength="5000"
            >{{ old('description') }}</textarea>
            @error('description')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Condition --}}
        <div class="form-group">
            <label for="condition">
                Condition <span class="required-star">*</span>
            </label>
            <select
                id="condition"
                name="condition"
                class="{{ $errors->has('condition') ? 'is-invalid' : '' }}"
            >
                <option value="">— Select condition —</option>
                <option value="new"         {{ old('condition') === 'new'         ? 'selected' : '' }}>New</option>
                <option value="used"        {{ old('condition') === 'used'        ? 'selected' : '' }}>Used</option>
                <option value="refurbished" {{ old('condition') === 'refurbished' ? 'selected' : '' }}>Refurbished</option>
            </select>
            @error('condition')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Location --}}
        <div class="form-group">
            <label for="location">Location</label>
            <input
                type="text"
                id="location"
                name="location"
                value="{{ old('location') }}"
                class="{{ $errors->has('location') ? 'is-invalid' : '' }}"
                placeholder="e.g. Dhaka, Bangladesh"
                maxlength="255"
            >
            <p class="hint">Optional — helps buyers estimate shipping costs.</p>
            @error('location')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── Pricing ──────────────────────────────────────────────────────── --}}
        <p class="section-title">Pricing</p>

        <div class="grid-2">
            {{-- Starting Price --}}
            <div class="form-group">
                <label for="starting_price">
                    Starting Price (৳) <span class="required-star">*</span>
                </label>
                <input
                    type="number"
                    id="starting_price"
                    name="starting_price"
                    value="{{ old('starting_price') }}"
                    class="{{ $errors->has('starting_price') ? 'is-invalid' : '' }}"
                    placeholder="100"
                    min="1"
                    step="0.01"
                >
                @error('starting_price')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reserve Price --}}
            <div class="form-group">
                <label for="reserve_price">Reserve Price (৳)</label>
                <input
                    type="number"
                    id="reserve_price"
                    name="reserve_price"
                    value="{{ old('reserve_price') }}"
                    class="{{ $errors->has('reserve_price') ? 'is-invalid' : '' }}"
                    placeholder="Optional"
                    min="1"
                    step="0.01"
                >
                <p class="hint">Minimum amount you'll accept. Hidden from buyers.</p>
                @error('reserve_price')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Min Bid Increment --}}
            <div class="form-group">
                <label for="min_bid_increment">Min Bid Increment (৳)</label>
                <input
                    type="number"
                    id="min_bid_increment"
                    name="min_bid_increment"
                    value="{{ old('min_bid_increment', '10') }}"
                    class="{{ $errors->has('min_bid_increment') ? 'is-invalid' : '' }}"
                    placeholder="10"
                    min="1"
                    step="0.01"
                >
                <p class="hint">Minimum amount each bid must exceed the current bid by.</p>
                @error('min_bid_increment')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buy Now Price --}}
            <div class="form-group">
                <label for="buy_now_price">Buy Now Price (৳)</label>
                <input
                    type="number"
                    id="buy_now_price"
                    name="buy_now_price"
                    value="{{ old('buy_now_price') }}"
                    class="{{ $errors->has('buy_now_price') ? 'is-invalid' : '' }}"
                    placeholder="Optional"
                    min="1"
                    step="0.01"
                >
                <p class="hint">Allows immediate purchase at a fixed price.</p>
                @error('buy_now_price')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ── Timing & Settings ────────────────────────────────────────────── --}}
        <p class="section-title">Timing & Settings</p>

        <div class="grid-2">
            {{-- Starts At --}}
            <div class="form-group">
                <label for="starts_at">Auction Start</label>
                <input
                    type="datetime-local"
                    id="starts_at"
                    name="starts_at"
                    value="{{ old('starts_at') }}"
                    class="{{ $errors->has('starts_at') ? 'is-invalid' : '' }}"
                >
                <p class="hint">Leave blank to set later before publishing.</p>
                @error('starts_at')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ends At --}}
            <div class="form-group">
                <label for="ends_at">Auction End</label>
                <input
                    type="datetime-local"
                    id="ends_at"
                    name="ends_at"
                    value="{{ old('ends_at') }}"
                    class="{{ $errors->has('ends_at') ? 'is-invalid' : '' }}"
                >
                <p class="hint">Must be after the start time.</p>
                @error('ends_at')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Auto Extend --}}
        <div class="form-group">
            {{-- Hidden input ensures auto_extend = 0 reaches the server when checkbox is unchecked. --}}
            <input type="hidden" name="auto_extend" value="0">
            <div class="checkbox-row">
                <input
                    type="checkbox"
                    id="auto_extend"
                    name="auto_extend"
                    value="1"
                    {{ old('auto_extend', '1') === '1' ? 'checked' : '' }}
                >
                <label for="auto_extend">Auto-extend auction if a bid is placed in the final minutes</label>
            </div>
            <p class="hint" style="margin-left: 26px;">Helps prevent last-second sniping. Recommended.</p>
            @error('auto_extend')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn-primary">Save as Draft</button>
            <a href="{{ route('seller.listings.index') }}" class="btn-cancel">Cancel</a>
        </div>

    </form>

    <hr class="divider">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout">Log Out</button>
    </form>

</body>
</html>