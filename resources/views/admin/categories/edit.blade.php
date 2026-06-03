<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category — Admin</title>
    <style>
        body             { font-family: sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1               { font-size: 1.6rem; margin-bottom: 4px; }
        p.subtitle       { color: #555; margin: 0 0 24px; }

        .badge           { display: inline-block; background: #1a1a1a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }

        .back-link       { display: inline-block; margin-bottom: 24px; font-size: 0.875rem; color: #6b7280; text-decoration: none; }
        .back-link:hover { color: #1a1a1a; }

        .form-group      { margin-bottom: 20px; }
        label            { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 6px; }
        .required-star   { color: #dc2626; }
        .hint            { font-size: 0.8rem; color: #6b7280; margin-top: 4px; }
        .slug-preview    { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }

        input[type="text"],
        textarea,
        select           { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.9rem; color: #1a1a1a; box-sizing: border-box; font-family: inherit; }
        input[type="text"]:focus,
        textarea:focus,
        select:focus     { outline: none; border-color: #1a1a1a; box-shadow: 0 0 0 3px rgba(26,26,26,0.06); }
        textarea         { resize: vertical; min-height: 100px; }
        .is-invalid      { border-color: #dc2626; }

        .checkbox-row    { display: flex; align-items: center; gap: 10px; }
        .checkbox-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
        .checkbox-row label { margin-bottom: 0; font-weight: normal; cursor: pointer; }

        .field-error     { font-size: 0.8rem; color: #dc2626; margin-top: 5px; }

        .actions         { display: flex; align-items: center; gap: 12px; margin-top: 32px; }
        .btn-primary     { background: #1a1a1a; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600; }
        .btn-primary:hover { background: #333; }
        .btn-cancel      { display: inline-block; padding: 10px 20px; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #6b7280; font-size: 0.9rem; }
        .btn-cancel:hover { background: #f9fafb; color: #1a1a1a; }

        .divider         { border: none; border-top: 1px solid #e5e7eb; margin: 32px 0; }
        button.logout    { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button.logout:hover { background: #b91c1c; }
    </style>
</head>
<body>

    <span class="badge">Admin</span>
    <h1>Edit Category</h1>
    <p class="subtitle">Update category details for <strong>{{ $category->name }}</strong>.</p>

    <a class="back-link" href="{{ route('admin.categories.index') }}">← Back to Categories</a>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PATCH')

        {{-- Name ──────────────────────────────────────────────────────────── --}}
        <div class="form-group">
            <label for="name">
                Name <span class="required-star">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $category->name) }}"
                class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                placeholder="e.g. Mobile Phones"
                maxlength="255"
                autofocus
            >
            <p class="hint">
                Current slug: <span class="slug-preview">{{ $category->slug }}</span>
                — regenerated automatically if the name changes.
            </p>
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description ────────────────────────────────────────────────────── --}}
        <div class="form-group">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                class="{{ $errors->has('description') ? 'is-invalid' : '' }}"
                placeholder="Optional — shown to buyers and used for SEO."
                maxlength="1000"
            >{{ old('description', $category->description) }}</textarea>
            @error('description')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Parent Category ─────────────────────────────────────────────────── --}}
        <div class="form-group">
            <label for="parent_id">Parent Category</label>
            <select
                id="parent_id"
                name="parent_id"
                class="{{ $errors->has('parent_id') ? 'is-invalid' : '' }}"
            >
                <option value="">— None (top-level category) —</option>
                @foreach ($parents as $parent)
                    <option
                        value="{{ $parent->id }}"
                        {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}
                    >
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            <p class="hint">Only active top-level categories are available as parents.</p>
            @error('parent_id')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Active ──────────────────────────────────────────────────────────── --}}
        <div class="form-group">
            {{-- Hidden input ensures is_active = 0 reaches the server when checkbox is unchecked. --}}
            <input type="hidden" name="is_active" value="0">
            <div class="checkbox-row">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $category->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                >
                <label for="is_active">Active — visible to buyers</label>
            </div>
            @error('is_active')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-cancel">Cancel</a>
        </div>

    </form>

    <hr class="divider">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout">Log Out</button>
    </form>

</body>
</html>