Your Leave of Absence was NOT APPROVED — EVSU Ormoc Campus

Control Number: {{ $loa->control_number }}

Dear {{ $loa->full_name }},

We regret to inform you that your Leave of Absence request has been
NOT APPROVED during the review process.

REVIEWED BY
-----------
@if ($loa->rejectedByActor)
{{ $loa->rejectedByActor->name }} ({{ $loa->rejectedByActor->role->label() }})
@if ($loa->rejected_at){{ $loa->rejected_at->format('M j, Y \a\t g:i A') }}
@endif
@endif
@if ($loa->rejection_reason)

REASON
------
{{ $loa->rejection_reason }}

@endif
WHAT YOU CAN DO
---------------
If you believe this decision was made in error or would like to address
the concerns raised, please visit the SASO Office or contact your
Department Head for guidance on next steps.

For questions or assistance, contact the SASO Office or your Department Head.

LeaveFlow · EVSU Ormoc Campus
