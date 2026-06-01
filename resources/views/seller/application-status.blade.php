<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Application Status</title>
    <style>
        body { font-family: sans-serif; max-width: 580px; margin: 60px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .subtitle { color: #555; margin: 0 0 28px; font-size: 0.95rem; }
        .success {
            background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px;
            padding: 12px 16px; margin-bottom: 24px; font-size: 0.9rem; color: #166534;
        }
        .status-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; margin-bottom: 28px; }
        .status-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .status-label { font-size: 0.82rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge {
            display: inline-block; font-size: 0.78rem; font-weight: 600;
            padding: 3px 12px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .meta { font-size: 0.85rem; color: #6b7280; margin-bottom: 20px; }
        .section-label { font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #374151; }
        .motivation-text {
            background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px;
            padding: 12px 14px; font-size: 0.9rem; color: #374151;
            line-height: 1.6; white-space: pre-wrap;
        }
        .admin-note {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px;
            padding: 12px 14px; margin-top: 16px; line-height: 1.6;
        }
        .admin-note-label { font-size: 0.82rem; font-weight: 600; color: #991b1b; margin-bottom: 4px; }
        .admin-note-body { font-size: 0.9rem; color: #7f1d1d; }
        .info {
            border-radius: 6px; padding: 12px 16px;
            font-size: 0.9rem; margin-top: 16px; line-height: 1.5;
        }
        .info-pending  { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .info-approved { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        a.back-link { color: #555; font-size: 0.9rem; text-decoration: none; }
        a.back-link:hover { color: #1a1a1a; text-decoration: underline; }
    </style>
</head>
<body>

    <h1>Seller Application Status</h1>
    <p class="subtitle">Track the progress of your seller application below.</p>

    @if (session('status'))
        <div class="success">{{ session('status') }}</div>
    @endif

    <div class="status-card">

        <div class="status-row">
            <span class="status-label">Status</span>
            @if ($application->status === 'pending')
                <span class="badge badge-pending">Pending Review</span>
            @elseif ($application->status === 'approved')
                <span class="badge badge-approved">Approved</span>
            @else
                <span class="badge badge-rejected">Not Approved</span>
            @endif
        </div>

        <p class="meta">Submitted on {{ $application->created_at->format('F j, Y') }}</p>

        <div class="section-label">Your motivation</div>
        <div class="motivation-text">{{ $application->motivation }}</div>

        @if ($application->status === 'rejected' && $application->admin_note)
            <div class="admin-note">
                <p class="admin-note-label">Reviewer note</p>
                <p class="admin-note-body">{{ $application->admin_note }}</p>
            </div>
        @endif

        @if ($application->status === 'pending')
            <div class="info info-pending">
                Your application is under review. This typically takes a few business days.
                We will be in touch once a decision has been made.
            </div>
        @elseif ($application->status === 'approved')
            <div class="info info-approved">
                Congratulations — your application has been approved.
                Your seller account will be activated shortly.
            </div>
        @endif

    </div>

    <a class="back-link" href="{{ route('dashboard') }}">Back to dashboard</a>

</body>
</html>