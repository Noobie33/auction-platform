<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Applications</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 1100px; margin: 40px auto; padding: 0 24px; color: #1a1a1a; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .subtitle { color: #555; margin: 0 0 24px; font-size: 0.95rem; }
        .back-link { font-size: 0.88rem; color: #555; text-decoration: none; display: inline-block; margin-bottom: 24px; }
        .back-link:hover { color: #1a1a1a; text-decoration: underline; }

        /* Filter tabs */
        .filters { display: flex; gap: 6px; margin-bottom: 24px; flex-wrap: wrap; }
        .filters a {
            padding: 6px 16px; border: 1px solid #e5e7eb; border-radius: 99px;
            font-size: 0.85rem; text-decoration: none; color: #374151; transition: background 0.1s;
        }
        .filters a:hover { background: #f9fafb; }
        .filters a.active { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }

        /* Meta */
        .meta { font-size: 0.85rem; color: #6b7280; margin-bottom: 16px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        thead tr { border-bottom: 2px solid #e5e7eb; }
        th { text-align: left; padding: 10px 12px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; font-weight: 600; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:hover { background: #fafafa; }
        td { padding: 12px 12px; vertical-align: top; }
        .applicant-name { font-weight: 600; }
        .applicant-email { color: #6b7280; font-size: 0.85rem; margin-top: 2px; }
        .motivation-preview { color: #374151; font-size: 0.85rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .date { font-size: 0.85rem; color: #6b7280; white-space: nowrap; }
        .reviewer-info { font-size: 0.85rem; color: #374151; }
        .reviewer-name { font-weight: 600; }
        .reviewer-date { color: #6b7280; margin-top: 2px; }
        .no-reviewer { color: #9ca3af; font-size: 0.85rem; }
        .admin-note { font-size: 0.82rem; color: #6b7280; margin-top: 4px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .empty { text-align: center; padding: 48px 0; color: #9ca3af; font-size: 0.95rem; }

        /* Badges */
        .badge {
            display: inline-block; font-size: 0.72rem; font-weight: 600;
            padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.04em;
        }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* Pagination */
        .pagination { display: flex; gap: 6px; margin-top: 28px; flex-wrap: wrap; align-items: center; }
        .pagination a, .pagination span {
            padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 6px;
            font-size: 0.85rem; text-decoration: none; color: #374151;
        }
        .pagination a:hover { background: #f9fafb; }
        .pagination span.active { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }
        .pagination span.disabled { color: #d1d5db; }
    </style>
</head>
<body>

    <a class="back-link" href="{{ route('admin.dashboard') }}">← Back to Admin Dashboard</a>

    <h1>Seller Applications</h1>
    <p class="subtitle">Review and manage seller applications submitted by bidders.</p>

    {{-- Filter tabs --}}
    <div class="filters">
        <a href="{{ route('admin.seller-applications.index', ['filter' => 'pending']) }}"
           class="{{ $filter === 'pending' ? 'active' : '' }}">
            Pending
        </a>
        <a href="{{ route('admin.seller-applications.index', ['filter' => 'approved']) }}"
           class="{{ $filter === 'approved' ? 'active' : '' }}">
            Approved
        </a>
        <a href="{{ route('admin.seller-applications.index', ['filter' => 'rejected']) }}"
           class="{{ $filter === 'rejected' ? 'active' : '' }}">
            Rejected
        </a>
        <a href="{{ route('admin.seller-applications.index', ['filter' => 'all']) }}"
           class="{{ $filter === 'all' ? 'active' : '' }}">
            All
        </a>
    </div>

    @if ($applications->total() > 0)
        <p class="meta">
            Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }}
            of {{ $applications->total() }}
            {{ $filter !== 'all' ? $filter : '' }} application{{ $applications->total() !== 1 ? 's' : '' }}.
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Applicant</th>
                <th>Motivation</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Reviewed by</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $application)
                <tr>
                    <td>
                        <div class="applicant-name">
                            {{ $application->user->name ?? '—' }}
                        </div>
                        <div class="applicant-email">
                            {{ $application->user->email ?? '—' }}
                        </div>
                    </td>
                    <td>
                        <div class="motivation-preview" title="{{ $application->motivation }}">
                            {{ $application->motivation }}
                        </div>
                    </td>
                    <td>
                        @if ($application->status === 'pending')
                            <span class="badge badge-pending">Pending</span>
                        @elseif ($application->status === 'approved')
                            <span class="badge badge-approved">Approved</span>
                        @else
                            <span class="badge badge-rejected">Rejected</span>
                        @endif
                    </td>
                    <td class="date">
                        {{ $application->created_at->format('d M Y') }}
                    </td>
                    <td>
                        @if ($application->reviewer)
                            <div class="reviewer-info">
                                <div class="reviewer-name">{{ $application->reviewer->name }}</div>
                                <div class="reviewer-date">
                                    {{ $application->reviewed_at?->format('d M Y') }}
                                </div>
                                @if ($application->admin_note)
                                    <div class="admin-note" title="{{ $application->admin_note }}">
                                        Note: {{ $application->admin_note }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="no-reviewer">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">
                        No {{ $filter !== 'all' ? $filter : '' }} applications found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($applications->hasPages())
        <div class="pagination">
            @if ($applications->onFirstPage())
                <span class="disabled">← Prev</span>
            @else
                <a href="{{ $applications->previousPageUrl() }}">← Prev</a>
            @endif

            @foreach ($applications->getUrlRange(1, $applications->lastPage()) as $page => $url)
                @if ($page == $applications->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($applications->hasMorePages())
                <a href="{{ $applications->nextPageUrl() }}">Next →</a>
            @else
                <span class="disabled">Next →</span>
            @endif
        </div>
    @endif

</body>
</html>