<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Auctions</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 1080px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }

        .badge { display: inline-block; background: #2563eb; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; }
        h1 { font-size: 1.8rem; margin: 0 0 6px; }
        p.subtitle { color: #555; margin: 0 0 32px; }

        .card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px; }

        .card { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; }
        .card-image { width: 100%; height: 180px; object-fit: cover; display: block; }
        .card-placeholder { width: 100%; height: 180px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; color: #9ca3af; font-size: 0.85rem; }
        .card-body { padding: 14px 16px 18px; }
        .card-category { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 6px; }
        .card-title { font-size: 1rem; font-weight: 600; margin: 0 0 12px; color: #1a1a1a; }
        .card-price { font-size: 0.875rem; margin-bottom: 4px; }
        .card-price .label { color: #6b7280; }
        .card-price .amount { font-weight: 700; color: #1a1a1a; }
        .card-ends { font-size: 0.8rem; color: #6b7280; margin-top: 10px; }
        .card-ends .ends-label { font-weight: 600; }

        .empty { text-align: center; padding: 80px 0; }
        .empty h2 { font-size: 1.2rem; margin: 0 0 8px; color: #374151; }
        .empty p { margin: 0; color: #6b7280; font-size: 0.95rem; }

        .pagination { display: flex; align-items: center; justify-content: center; gap: 16px; margin: 40px 0; }
        .page-link { display: inline-block; padding: 8px 18px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; text-decoration: none; color: #374151; }
        .page-link:hover { background: #f9fafb; }
        .page-link.disabled { color: #9ca3af; border-color: #e5e7eb; cursor: default; pointer-events: none; }
        .page-info { font-size: 0.875rem; color: #6b7280; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 40px 0 24px; }
        .nav-links { font-size: 0.875rem; }
        .nav-links a { color: #2563eb; text-decoration: none; }
        .nav-links a:hover { text-decoration: underline; }
                .filter-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 32px; }
        .filter-form input[type="text"] { flex: 1; min-width: 180px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #1a1a1a; }
        .filter-form select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; color: #1a1a1a; background: #fff; cursor: pointer; }
        .filter-form button { padding: 8px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
        .filter-form button:hover { background: #1d4ed8; }
        .filter-clear { font-size: 0.8rem; color: #6b7280; text-decoration: none; white-space: nowrap; }
        .filter-clear:hover { color: #374151; text-decoration: underline; }
        .empty a { color: #2563eb; text-decoration: none; }
        .empty a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <span class="badge">Live Auctions</span>
    <h1>Browse Auctions</h1>
    <p class="subtitle">Discover active listings ending soon.</p>
        <form method="GET" action="{{ route('auctions.index') }}" class="filter-form">
        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Search listings..."
        >

        <select name="category">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>

        <button type="submit">Search</button>

        @if ($search !== '' || $categoryId > 0)
            <a href="{{ route('auctions.index') }}" class="filter-clear">Clear filters</a>
        @endif
    </form>

        @if ($listings->isEmpty())
        <div class="empty">
            @if ($search !== '' || $categoryId > 0)
                <h2>No listings match your search.</h2>
                <p>Try different keywords or <a href="{{ route('auctions.index') }}">browse all auctions</a>.</p>
            @else
                <h2>No active auctions right now.</h2>
                <p>Check back soon — new listings are added regularly.</p>
            @endif
        </div>
    @else
        <div class="card-grid">
            @foreach ($listings as $listing)
                @php
                    $primaryImage = $listing->images->firstWhere('is_primary', true)
                        ?? $listing->images->first();
                @endphp

                <div class="card">
                    @if ($primaryImage)
                        <img
                            class="card-image"
                            src="{{ asset('storage/' . $primaryImage->image_path) }}"
                            alt="{{ $primaryImage->alt_text ?: $listing->title }}"
                        >
                    @else
                        <div class="card-placeholder">No image available</div>
                    @endif

                    <div class="card-body">
                        <p class="card-category">{{ $listing->category?->name ?? '—' }}</p>
                        <h2 class="card-title">{{ $listing->title }}</h2>

                        <div class="card-price">
                            @if ($listing->current_bid !== null)
                                <span class="label">Current bid:</span>
                                <span class="amount">${{ number_format($listing->current_bid, 2) }}</span>
                            @else
                                <span class="label">Starting at:</span>
                                <span class="amount">${{ number_format($listing->starting_price, 2) }}</span>
                            @endif
                        </div>

                        <p class="card-ends">
                            <span class="ends-label">Ends:</span>
                            {{ $listing->ends_at?->format('d M Y, g:i A') ?? '—' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($listings->hasPages())
            <div class="pagination">
                @if ($listings->onFirstPage())
                    <span class="page-link disabled">← Previous</span>
                @else
                    <a class="page-link" href="{{ $listings->previousPageUrl() }}">← Previous</a>
                @endif

                <span class="page-info">
                    Page {{ $listings->currentPage() }} of {{ $listings->lastPage() }}
                    &nbsp;·&nbsp; {{ $listings->total() }} {{ $listings->total() === 1 ? 'listing' : 'listings' }}
                </span>

                @if ($listings->hasMorePages())
                    <a class="page-link" href="{{ $listings->nextPageUrl() }}">Next →</a>
                @else
                    <span class="page-link disabled">Next →</span>
                @endif
            </div>
        @endif
    @endif

    <hr class="divider">

    <div class="nav-links">
        <a href="{{ url('/') }}">← Back to Home</a>
    </div>

</body>
</html>