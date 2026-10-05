LOA Resubmitted — Action Required | EVSU Ormoc Campus

Control Number: {{ $loa->control_number }}

Dear {{ $recipient->name }},

A student has resubmitted their Leave of Absence application after it was
previously not approved. The application is now back in the pipeline and
is awaiting your review.

APPLICATION DETAILS
-------------------
Student     : {{ $loa->full_name }}
Department  : {{ $loa->department?->name ?? '—' }}
Leave period: {{ $loa->start_date?->format('M j, Y') }} to {{ $loa->return_date?->format('M j, Y') }}
Resubmission: #{{ $loa->resubmit_count }}

Please log in to the LeaveFlow portal to review and take action.

LeaveFlow · EVSU Ormoc Campus
