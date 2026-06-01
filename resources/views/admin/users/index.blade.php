<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 24px; color: #1a1a1a; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .subtitle { color: #555; margin: 0 0 28px; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        thead tr { border-bottom: 2px solid #e5e7eb; }
        th { text-align: left; padding: 10px 12px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; font-weight: 600; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:hover { background: #fafafa; }
        td { padding: 12px 12px; vertical-align: middle; }
        .user-name { font-weight: 600; color: #1a1a1a; }
        .user-email { color: #6b7280; font-size: 0.85rem; margin-top: 2px; }
        .badge {
            display: inline-block; font-size: 0.72rem; font-weight: 600;
            padding: 2px 8px; border-radius: 99px; text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-active    { background: #dcfce7; color: #166534; }
        .badge-suspended { background: #fef3c7; color: #92400e; }
        .badge-banned    { background: #fee2e2; color: #991b1b; }
        .badge-role      { background: #e0e7ff; color: #3730a3; margin-right: 4px; }
        .badge-verified   { background: #dcfce7; color: #166534; }
        .badge-unverified { background: #f3f4f6; color: #6b7280; }
        .date { font-size: 0.85rem; color: #6b7280; white-space: nowrap; }
        .empty { text-align: center; padding: 40px 0; color: #9ca3af; font-size: 0.95rem; }
        .pagination { display: flex; gap: 6px; margin-top: 28px; flex-wrap: wrap; align-items: center; }
        .pagination a, .pagination span {
            padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 6px;
            font-size: 0.85rem; text-decoration: none; color: #374151;
        }
        .pagination a:hover { background: #f9fafb; }
        .pagination span.active { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }
        .pagination span.disabled { color: #d1d5db; }
        .meta { font-size: 0.85rem; color: #6b7280; margin-bottom: 16px; }
        .back-link { font-size: 0.88rem; color: #555; text-decoration: none; display: inline-block; margin-bottom: 24px; }
        .back-link:hover { color: #1a1a1a; text-decoration: underline; }
    </style>
</head>
<body>

    <a class="back-link" href="{{ route('admin.dashboard') }}">← Back to Admin Dashboard</a>

    <h1>User Management</h1>
    <p class="subtitle">All registered users on the platform.</p>

    @if ($users->total() > 0)
        <p class="meta">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users.
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Role(s)</th>
                <th>Status</th>
                <th>Email Verified</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        <div class="user-name">{{ $user->name }}</div>
                        <div class="user-email">{{ $user->email }}</div>
                    </td>
                    <td>
                        @forelse ($user->roles as $role)
                            <span class="badge badge-role">{{ $role->name }}</span>
                        @empty
                            <span style="color:#9ca3af; font-size:0.85rem;">—</span>
                        @endforelse
                    </td>
                    <td>
                        @if ($user->status === 'active')
                            <span class="badge badge-active">Active</span>
                        @elseif ($user->status === 'suspended')
                            <span class="badge badge-suspended">Suspended</span>
                        @else
                            <span class="badge badge-banned">Banned</span>
                        @endif
                    </td>
                    <td>
                        @if ($user->hasVerifiedEmail())
                            <span class="badge badge-verified">Verified</span>
                        @else
                            <span class="badge badge-unverified">Unverified</span>
                        @endif
                    </td>
                    <td class="date">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($users->hasPages())
        <div class="pagination">
            {{-- Previous page --}}
            @if ($users->onFirstPage())
                <span class="disabled">← Prev</span>
            @else
                <a href="{{ $users->previousPageUrl() }}">← Prev</a>
            @endif

            {{-- Page numbers --}}
            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                @if ($page == $users->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next page --}}
            @if ($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}">Next →</a>
            @else
                <span class="disabled">Next →</span>
            @endif
        </div>
    @endif

</body>
</html>