Your Leave of Absence has been APPROVED — EVSU Ormoc Campus

Control Number: {{ $loa->control_number }}

Dear {{ $loa->full_name }},

Your Leave of Absence request has been APPROVED by all required offices.
All five approval stages have been completed:

  [OK] Department Head{{ $loa->deptHeadActor ? ' — ' . $loa->deptHeadActor->name : '' }}{{ $loa->dept_head_at ? ' (' . $loa->dept_head_at->format('M j, Y') . ')' : '' }}

  [OK] SASO Officer{{ $loa->sasoActor ? ' — ' . $loa->sasoActor->name : '' }}{{ $loa->saso_at ? ' (' . $loa->saso_at->format('M j, Y') . ')' : '' }}

  [OK] Campus Director{{ $loa->campusDirectorActor ? ' — ' . $loa->campusDirectorActor->name : '' }}{{ $loa->campus_director_at ? ' (' . $loa->campus_director_at->format('M j, Y') . ')' : '' }}

  [OK] Registrar{{ $loa->registrarActor ? ' — ' . $loa->registrarActor->name : '' }}{{ $loa->registrar_at ? ' (' . $loa->registrar_at->format('M j, Y') . ')' : '' }}

  [OK] Guidance Office{{ $loa->guidanceActor ? ' — ' . $loa->guidanceActor->name : '' }}{{ $loa->guidance_at ? ' (' . $loa->guidance_at->format('M j, Y') . ')' : '' }}

LEAVE DETAILS
-------------
Leave period : {{ $loa->start_date?->format('M j, Y') }} to {{ $loa->return_date?->format('M j, Y') }}
Program      : {{ $loa->program?->name ?? '—' }}
Department   : {{ $loa->department?->name ?? '—' }}

NEXT STEPS
----------
Please proceed to the Registrar's Office to complete any required clearance
procedures before your leave begins. Keep this email and your control number
for reference.

For questions, contact the SASO Office or your Department Head.

LeaveFlow · EVSU Ormoc Campus
