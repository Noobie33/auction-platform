<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Applications | Admin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; color: #222; font-size: 14px; }
        .page { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.4rem; font-weight: 600; margin-bottom: 1.5rem; }

        /* Flash messages */
        .alert { padding: .75rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: .9rem; }
        .alert-success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
        .alert-error   { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

        /* Filter tabs */
        .tabs { display: flex; gap: .25rem; margin-bottom: 1.25rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 0; }
        .tab  { padding: .45rem .9rem; border-radius: 4px 4px 0 0; text-decoration: none;
                color: #555; border: 1px solid transparent; border-bottom: none; font-size: .875rem; }
        .tab:hover  { background: #f0f0f0; }
        .tab.active { background: #fff; border-color: #e5e7eb; color: #111; font-weight: 600;
                      margin-bottom: -2px; border-bottom: 2px solid #fff; }

        /* Table */
        .card  { background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; }
        table  { width: 100%; border-collapse: collapse; }
        thead  { background: #f9fafb; }
        th, td { padding: .6rem .85rem; text-align: left; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th     { font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafa; }
        .empty-row td { text-align: center; padding: 2rem; color: #9ca3af; }

        /* Status badges */
        .badge         { display: inline-block; padding: .15rem .55rem; border-radius: 9999px;
                         font-size: .75rem; font-weight: 600; text-transform: capitalize; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* Action forms */
        .actions { display: flex; flex-direction: column; gap: .5rem; }
        .btn { display: inline-block; padding: .3rem .75rem; border: none; border-radius: 4px;
               font-size: .8rem; font-weight: 600; cursor: pointer; }
        .btn-approve { background: #059669; color: #fff; }
        .btn-approve:hover { background: #047857; }
        .btn-reject  { background: #dc2626; color: #fff; }
        .btn-reject:hover  { background: #b91c1c; }
        .no-action { color: #9ca3af; font-size: .8rem; }

        /* Reject details/form */
        details { margin-top: .25rem; }
        summary { cursor: pointer; font-size: .8rem; font-weight: 600; color: #dc2626;
                  list-style: none; display: inline-block; padding: .3rem .75rem;
                  background: #fee2e2; border-radius: 4px; }
        summary::-webkit-details-marker { display: none; }
        summary:hover { background: #fecaca; }
        .reject-form { margin-top: .5rem; display: flex; flex-direction: column; gap: .4rem; }
        .reject-form textarea { width: 100%; min-width: 220px; padding: .4rem .6rem;
                                border: 1px solid #d1d5db; border-radius: 4px; font-size: .8rem;
                                resize: vertical; min-height: 60px; font-family: inherit; }
        .reject-form textarea:focus { outline: 2px solid #dc2626; border-color: transparent; }
        .error-msg { color: #dc2626; font-size: .75rem; }

        /* Pagination */
        .pagination { margin-top: 1.25rem; display: flex; gap: .25rem; }
        .pagination a, .pagination span { padding: .35rem .65rem; border-radius: 4px;
            border: 1px solid #e5e7eb; font-size: .8rem; text-decoration: none; color: #374151; }
        .pagination a:hover { background: #f3f4f6; }
        .pagination .active span { background: #111; color: #fff; border-color: #111; }
    </style>
</head>
<body>
<div class="page">

    <h1>Seller Applications</h1>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Filter tabs --}}
    <nav class="tabs">
        @foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('admin.seller-applications.index', ['filter' => $value]) }}"
               class="tab {{ $filter === $value ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    {{-- Applications table --}}
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Reviewed At</th>
                    <th>Admin Note</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>{{ $application->id }}</td>
                        <td>{{ $application->user->name ?? '—' }}</td>
                        <td>{{ $application->user->email ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $application->status }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>
                        <td>{{ $application->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $application->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $application->admin_note ?? '—' }}</td>
                        <td>
                            @if ($application->status === 'pending')
                                <div class="actions">

                                    {{-- Approve --}}
                                    <form method="POST"
                                          action="{{ route('admin.seller-applications.approve', $application) }}"
                                          onsubmit="return confirm('Approve application #{{ $application->id }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-approve">Approve</button>
                                    </form>

                                    {{-- Reject (inline, no JS modal) --}}
                                    <details>
                                        <summary>Reject</summary>
                                        <form method="POST"
                                              action="{{ route('admin.seller-applications.reject', $application) }}"
                                              class="reject-form">
                                            @csrf
                                            <textarea
                                                name="admin_note"
                                                placeholder="Reason for rejection (required)…"
                                                maxlength="1000"
                                                required>{{ old('admin_note') }}</textarea>
                                            @error('admin_note')
                                                <span class="error-msg">{{ $message }}</span>
                                            @enderror
                                            <div>
                                                <button type="submit" class="btn btn-reject">Confirm Reject</button>
                                            </div>
                                        </form>
                                    </details>

                                </div>
                            @else
                                <span class="no-action">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="8">No {{ $filter !== 'all' ? $filter : '' }} applications found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pagination">
        {{ $applications->links() }}
    </div>

</div>
</body>
</html>