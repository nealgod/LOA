Your Leave of Absence request has been submitted — EVSU Ormoc Campus

--------------------------------------------------
CONTROL NUMBER: {{ $loa->control_number }}
--------------------------------------------------

Keep this number. You will need it to follow up on your request.

SUBMITTED DETAILS
-----------------
Student ID       : {{ $loa->student_id }}
Full name        : {{ $loa->full_name }}
Department       : {{ $loa->department?->name ?? '—' }}
Program          : {{ $loa->program?->name ?? '—' }}
Year level       : {{ $loa->year_level ?? '—' }}
Leave period     : {{ $loa->start_date?->format('M j, Y') }} to {{ $loa->return_date?->format('M j, Y') }}
Date submitted   : {{ $loa->submitted_at?->format('M j, Y g:i A') }}
Parent / Guardian: {{ $loa->parent_full_name }} ({{ $loa->parent_relationship }}) — {{ $loa->parent_phone }}
@if ($loa->attachments->isNotEmpty())
Attachments      : {{ $loa->attachments->count() }} file(s) uploaded
@endif

Reason:
{{ $loa->reason }}

WHAT HAPPENS NEXT
-----------------
Your Department Head will review your application and may contact your
parent or guardian at the number you provided. You will receive email
updates as your request moves through each approval step.

If you did not submit this request, contact the SASO Office immediately.

LeaveFlow · EVSU Ormoc Campus
