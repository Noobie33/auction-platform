<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing — Seller</title>
    <style>
        body             { font-family: sans-serif; max-width: 720px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1               { font-size: 1.6rem; margin-bottom: 4px; }
        p.subtitle       { color: #555; margin: 0 0 28px; }

        .badge           { display: inline-block; background: #16a34a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }

        .back-link       { display: inline-block; margin-bottom: 28px; font-size: 0.875rem; color: #6b7280; text-decoration: none; }
        .back-link:hover { color: #1a1a1a; }

        .section-title   { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #6b7280; margin: 32px 0 16px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }

        .form-group      { margin-bottom: 20px; }
        label            { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 6px; }
        .required-star   { color: #dc2626; }
        .hint            { font-size: 0.8rem; color: #6b7280; margin-top: 4px; }
        .slug-preview    { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }

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

        .image-section   { margin-top: 36px; }
        .image-grid      { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
        .image-card      { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
        .image-card img  { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; margin-bottom: 8px; }
        .primary-badge   { display: inline-block; background: #dcfce7; color: #16a34a; font-size: 0.75rem; padding: 2px 8px; border-radius: 99px; margin-bottom: 6px; }
        .image-alt       { color: #6b7280; font-size: 0.85rem; margin: 6px 0; }
        .btn-delete-img  { background: #dc2626; color: #fff; border: none; padding: 7px 14px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .btn-delete-img:hover { background: #b91c1c; }

        .divider         { border: none; border-top: 1px solid #e5e7eb; margin: 36px 0; }
        button.logout    { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button.logout:hover { background: #b91c1c; }
    </style>
</head>
<body>

    <span class="badge">Seller</span>
    <h1>Edit Listing</h1>
    <p class="subtitle">Editing: <strong>{{ $listing->title }}</strong></p>

    <a class="back-link" href="{{ route('seller.listings.index') }}">← Back to My Listings</a>

    <form method="POST" action="{{ route('seller.listings.update', $listing) }}">
        @csrf
        @method('PATCH')

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
                                    {{ old('category_id', $listing->category_id) == $child->id ? 'selected' : '' }}
                                >{{ $child->name }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option
                            value="{{ $parent->id }}"
                            {{ old('category_id', $listing->category_id) == $parent->id ? 'selected' : '' }}
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
                value="{{ old('title', $listing->title) }}"
                class="{{ $errors->has('title') ? 'is-invalid' : '' }}"
                placeholder="e.g. Canon EOS 5D Mark IV Camera Body"
                maxlength="255"
                autofocus
            >
            <p class="hint">
                Current slug: <span class="slug-preview">{{ $listing->slug }}</span>
                — regenerated automatically if the title changes.
            </p>
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
                placeholder="Describe the item in detail. Minimum 20 characters."
                maxlength="5000"
            >{{ old('description', $listing->description) }}</textarea>
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
                <option value="new"
                    {{ old('condition', $listing->condition) === 'new' ? 'selected' : '' }}>New</option>
                <option value="used"
                    {{ old('condition', $listing->condition) === 'used' ? 'selected' : '' }}>Used</option>
                <option value="refurbished"
                    {{ old('condition', $listing->condition) === 'refurbished' ? 'selected' : '' }}>Refurbished</option>
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
                value="{{ old('location', $listing->location) }}"
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
                    value="{{ old('starting_price', $listing->starting_price) }}"
                    class="{{ $errors->has('starting_price') ? 'is-invalid' : '' }}"
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
                    value="{{ old('reserve_price', $listing->reserve_price) }}"
                    class="{{ $errors->has('reserve_price') ? 'is-invalid' : '' }}"
                    placeholder="Optional"
                    min="1"
                    step="0.01"
                >
                <p class="hint">Hidden from buyers. Minimum amount you'll accept.</p>
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
                    value="{{ old('min_bid_increment', $listing->min_bid_increment) }}"
                    class="{{ $errors->has('min_bid_increment') ? 'is-invalid' : '' }}"
                    min="1"
                    step="0.01"
                >
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
                    value="{{ old('buy_now_price', $listing->buy_now_price) }}"
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
                    value="{{ old('starts_at', $listing->starts_at?->format('Y-m-d\TH:i')) }}"
                    class="{{ $errors->has('starts_at') ? 'is-invalid' : '' }}"
                >
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
                    value="{{ old('ends_at', $listing->ends_at?->format('Y-m-d\TH:i')) }}"
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
            <input type="hidden" name="auto_extend" value="0">
            <div class="checkbox-row">
                <input
                    type="checkbox"
                    id="auto_extend"
                    name="auto_extend"
                    value="1"
                    {{ old('auto_extend', $listing->auto_extend ? '1' : '0') === '1' ? 'checked' : '' }}
                >
                <label for="auto_extend">Auto-extend auction if a bid is placed in the final minutes</label>
            </div>
            @error('auto_extend')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('seller.listings.index') }}" class="btn-cancel">Cancel</a>
        </div>

    </form>

        {{-- ── Image Management ───────────────────────────────────────────── --}}
    <section class="image-section">
        <p class="section-title">Images</p>

        @if ($listing->images->isNotEmpty())
            <div class="image-grid">
                @foreach ($listing->images as $image)
                    <div class="image-card">
                        <img
                            src="{{ asset('storage/' . $image->image_path) }}"
                            alt="{{ $image->alt_text ?? '' }}"
                        >

                        @if ($image->is_primary)
                            <span class="primary-badge">Primary</span>
                        @endif

                        @if ($image->alt_text)
                            <p class="image-alt">{{ $image->alt_text }}</p>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('seller.listings.images.destroy', [$listing, $image]) }}"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-img">Delete image</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="hint">No images uploaded yet.</p>
        @endif

        <form
            method="POST"
            action="{{ route('seller.listings.images.store', $listing) }}"
            enctype="multipart/form-data"
            style="margin-top: 24px;"
        >
            @csrf

            <div class="form-group">
                <label for="image">
                    Upload Image <span class="required-star">*</span>
                </label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="{{ $errors->has('image') ? 'is-invalid' : '' }}"
                    required
                >
                <p class="hint">JPG, PNG or WebP. Maximum 2 MB.</p>
                @error('image')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="alt_text">Alt Text</label>
                <input
                    type="text"
                    id="alt_text"
                    name="alt_text"
                    value="{{ old('alt_text') }}"
                    maxlength="255"
                    class="{{ $errors->has('alt_text') ? 'is-invalid' : '' }}"
                    placeholder="Optional image description"
                >
                @error('alt_text')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Upload image</button>
        </form>
    </section>


    <hr class="divider">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout">Log Out</button>
    </form>

</body>
</html>