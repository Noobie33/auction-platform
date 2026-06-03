<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management — Admin</title>
    <style>
        body            { font-family: sans-serif; max-width: 960px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1              { font-size: 1.6rem; margin-bottom: 4px; }
        p.subtitle      { color: #555; margin: 0 0 24px; }

        .badge          { display: inline-block; background: #1a1a1a; color: #fff; font-size: 0.75rem; padding: 2px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-active   { background: #16a34a; }
        .badge-inactive { background: #9ca3af; }

        .page-header    { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .back-link      { font-size: 0.875rem; color: #6b7280; text-decoration: none; }
        .back-link:hover{ color: #1a1a1a; }
        .btn-create     { display: inline-block; background: #1a1a1a; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
        .btn-create:hover { background: #333; }

        .stats          { display: flex; gap: 16px; margin-bottom: 28px; }
        .stat           { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 16px; flex: 1; }
        .stat-number    { font-size: 1.4rem; font-weight: 700; }
        .stat-label     { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }

        table           { width: 100%; border-collapse: collapse; }
        thead th        { text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; padding: 8px 12px; border-bottom: 2px solid #e5e7eb; }
        tbody tr        { border-bottom: 1px solid #f3f4f6; }
        tbody tr:hover  { background: #f9fafb; }
        td              { padding: 10px 12px; font-size: 0.9rem; vertical-align: middle; }

        .parent-row td           { font-weight: 600; background: #f9fafb; }
        .child-row td:first-child { padding-left: 30px; color: #4b5563; }

        .slug           { font-family: monospace; font-size: 0.8rem; color: #6b7280; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
        .count          { font-size: 0.85rem; color: #6b7280; }

        /* Actions column — Unit 5 will add archive/restore, Unit 6 will add delete */
        .actions-cell   { white-space: nowrap; }
        .action-link    { font-size: 0.8rem; color: #1a1a1a; text-decoration: none; font-weight: 500; padding: 3px 8px; border: 1px solid #d1d5db; border-radius: 4px; margin-right: 4px; }
        .action-link:hover { background: #f3f4f6; }

        .empty          { padding: 40px 0; text-align: center; color: #6b7280; }

        .divider        { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        button          { background: #dc2626; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        button:hover    { background: #b91c1c; }
    </style>
</head>
<body>

    <span class="badge">Admin</span>
    <h1>Category Management</h1>
    <p class="subtitle">All auction categories and subcategories.</p>

    <div class="page-header">
        <a class="back-link" href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>
        <a class="btn-create" href="{{ route('admin.categories.create') }}">+ Create Category</a>
    </div>

    @php
        $totalCount  = $categories->sum(fn ($c) => 1 + $c->children->count());
        $activeCount = $categories->where('is_active', true)->count();
    @endphp

    <div class="stats">
        <div class="stat">
            <div class="stat-number">{{ $categories->count() }}</div>
            <div class="stat-label">Parent Categories</div>
        </div>
        <div class="stat">
            <div class="stat-number">{{ $totalCount }}</div>
            <div class="stat-label">Total Categories</div>
        </div>
        <div class="stat">
            <div class="stat-number">{{ $activeCount }}</div>
            <div class="stat-label">Active Parents</div>
        </div>
    </div>

    @if ($categories->isEmpty())
        <p class="empty">No categories yet. <a href="{{ route('admin.categories.create') }}">Create the first one.</a></p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Listings</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr class="parent-row">
                        <td>{{ $category->name }}</td>
                        <td><span class="slug">{{ $category->slug }}</span></td>
                        <td>
                            @if ($category->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td><span class="count">{{ $category->listings_count }}</span></td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="action-link">Edit</a>
                        </td>
                    </tr>

                    @foreach ($category->children as $child)
                        <tr class="child-row">
                            <td>↳ {{ $child->name }}</td>
                            <td><span class="slug">{{ $child->slug }}</span></td>
                            <td>
                                @if ($child->is_active)
                                    <span class="badge badge-active">Active</span>
                                @else
                                    <span class="badge badge-inactive">Inactive</span>
                                @endif
                            </td>
                            <td><span class="count">{{ $child->listings_count }}</span></td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.categories.edit', $child) }}" class="action-link">Edit</a>
                            </td>
                        </tr>
                    @endforeach
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