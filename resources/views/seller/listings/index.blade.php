<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings — Seller</title>
    <style>
        body              { font-family: sans-serif; max-width: 860px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1                { font-size: 1.6rem; margin-bottom: 4px; }
        p.subtitle        { color: #555; margin: 0 0 24px; }

        /* Seller brand badge — green to match seller dashboard */
        .badge            { display: inline-block; background: #16a34a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }

        /* Per-row status badges */
        .status-badge     { display: inline-block; font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.04em; }
        .status-draft     { background: #f3f4f6; color: #6b7280; }
        .status-active    { background: #dcfce7; color: #16a34a; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .status-ended     { background: #dbeafe; color: #2563eb; }
        .status-failed    { background: #fef3c7; color: #d97706; }

        .page-header      { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .back-link        { font-size: 0.875rem; color: #6b7280; text-decoration: none; }
        .back-link:hover  { color: #1a1a1a; }

        table             { width: 100%; border-collapse: collapse; }
        thead th          { text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; padding: 8px 12px; border-bottom: 2px solid #e5e7eb; }
        tbody tr          { border-bottom: 1px solid #f3f4f6; }
        tbody tr:hover    { background: #f9fafb; }
        td                { padding: 10px 12px; font-size: 0.9rem; vertical-align: middle; }

        .listing-title    { font-weight: 600; }
        .text-muted       { color: #6b7280; font-size: 0.85rem; }

        .empty            { text-align: center; padding: 52px 0; }
        .empty p          { color: #6b7280; margin: 0; font-size: 0.95rem; }

        .divider          { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        button            { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button:hover      { background: #b91c1c; }
        .btn-create      { display: inline-block; background: #16a34a; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
        .btn-create:hover { background: #15803d; }
        .actions-cell     { white-space: nowrap; }
        .action-link      { font-size: 0.8rem; color: #16a34a; text-decoration: none; font-weight: 500; padding: 3px 8px; border: 1px solid #16a34a; border-radius: 4px; }
        .action-link:hover { background: #f0fdf4; }
        /* Flash banners */
        .alert-success    { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 10px 14px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error      { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 10px 14px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }

        /* Inline action forms — keeps buttons on one row beside the Edit link */
        .action-form      { display: inline; }
        button.action-btn { font-size: 0.8rem; font-weight: 500; padding: 3px 8px; border-radius: 4px; cursor: pointer; margin-right: 4px; background: #fff; }
        button.action-btn-publish { color: #16a34a; border: 1px solid #16a34a; }
        button.action-btn-publish:hover { background: #f0fdf4; }
        button.action-btn-cancel  { color: #dc2626; border: 1px solid #dc2626; }
        button.action-btn-cancel:hover  { background: #fef2f2; }
        button.action-btn-delete { color: #dc2626; border: 1px solid #dc2626; }
        button.action-btn-delete:hover { background: #fef2f2; }
    </style>
</head>
<body>

    <span class="badge">Seller</span>
    <h1>My Listings</h1>
    <p class="subtitle">All auction listings you have created.</p>

    <div class="page-header">
        <a class="back-link" href="{{ route('seller.dashboard') }}">← Back to Dashboard</a>
        <a class="btn-create" href="{{ route('seller.listings.create') }}">+ Create Listing</a>
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if ($listings->isEmpty())
        <div class="empty">
            <p>You have not created any listings yet.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Starting Price</th>
                    <th>Current Bid</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($listings as $listing)
                    <tr>
                        <td>
                            <span class="listing-title">{{ $listing->title }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $listing->category?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $listing->status }}">
                                {{ $listing->status }}
                            </span>
                        </td>
                        <td>{{ number_format($listing->starting_price, 2) }}</td>
                        <td>
                            @if ($listing->current_bid !== null)
                                {{ number_format($listing->current_bid, 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $listing->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="actions-cell">
                            @if ($listing->isDraft())
                                <a href="{{ route('seller.listings.edit', $listing) }}" class="action-link">Edit</a>

                                <form
                                    method="POST"
                                    action="{{ route('seller.listings.publish', $listing) }}"
                                    class="action-form"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn action-btn-publish">Publish</button>
                                </form>
                                <form
                                    method="POST"
                                    action="{{ route('seller.listings.destroy', $listing) }}"
                                    class="action-form"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete">Delete</button>
                                </form>
                            @endif

                            @if ($listing->isActive())
                                <form
                                    method="POST"
                                    action="{{ route('seller.listings.cancel', $listing) }}"
                                    class="action-form"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn action-btn-cancel">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <hr class="divider">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log Out</button>
    </form>

</body>
</html>