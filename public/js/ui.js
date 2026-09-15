document.addEventListener('DOMContentLoaded', () => {
    // ── Mobile nav toggle ────────────────────────────────────────────────────
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navPanel  = document.querySelector('[data-nav-panel]');
    if (navToggle && navPanel) {
        navToggle.addEventListener('click', () => navPanel.classList.toggle('hidden'));
    }

    // ── Role → department field visibility (register form) ───────────────────
    const roleSelect      = document.querySelector('[data-role-select]');
    const departmentWrap  = document.querySelector('[data-department-wrap]');
    if (roleSelect && departmentWrap) {
        const syncDepartment = () => {
            const needs = roleSelect.value === 'department_head';
            departmentWrap.classList.toggle('hidden', !needs);
            const sel = departmentWrap.querySelector('select');
            if (sel) {
                sel.required = needs;
                if (!needs) sel.value = '';
            }
        };
        roleSelect.addEventListener('change', syncDepartment);
        syncDepartment();
    }

    // ── Password show/hide ───────────────────────────────────────────────────
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrap  = button.closest('[data-password-wrap]');
            const input = wrap?.querySelector('[data-password-input]');
            if (!input) return;
            const showing  = input.type === 'text';
            input.type     = showing ? 'password' : 'text';
            button.textContent = showing ? 'Show' : 'Hide';
        });
    });

    // ── Department → Program → Year level cascading selects ─────────────────
    const departmentFilter = document.querySelector('[data-department-filter]');
    const programSelect    = document.querySelector('[data-program-select]');
    const yearSelect       = document.querySelector('[data-year-select]');
    const programsJson     = document.querySelector('[data-programs-json]');
    const yearSaved        = document.getElementById('year_level_saved');

    if (departmentFilter && programSelect && programsJson) {
        const programs        = JSON.parse(programsJson.textContent || '[]');
        const selectedProgram = programSelect.dataset.selectedProgram || '';
        const savedYear       = yearSaved ? yearSaved.value : '';

        const fillYears = (programCode) => {
            if (!yearSelect) return;
            yearSelect.innerHTML = '';
            if (!programCode) {
                yearSelect.append(new Option('Select program first', ''));
                return;
            }
            const years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
            yearSelect.append(new Option('Select year level', ''));
            years.forEach((label, i) => {
                const val = `${programCode}-${label.replace(' Year', '')}`;
                const opt = new Option(`${programCode} — ${label}`, val);
                yearSelect.append(opt);
            });
            if (savedYear && [...yearSelect.options].some(o => o.value === savedYear)) {
                yearSelect.value = savedYear;
            }
        };

        const fillPrograms = () => {
            const departmentId = departmentFilter.value;
            const matches = programs.filter(
                (p) => String(p.department_id) === String(departmentId)
            );
            programSelect.innerHTML = '';

            if (!departmentId) {
                programSelect.append(new Option('Select department first', ''));
                fillYears('');
                return;
            }
            if (matches.length === 0) {
                programSelect.append(new Option('No programs found', ''));
                fillYears('');
                return;
            }
            if (matches.length > 1) {
                programSelect.append(new Option('Select program', ''));
            }
            matches.forEach((p) => programSelect.append(new Option(p.name, p.id)));

            if (matches.length === 1) {
                programSelect.value = String(matches[0].id);
            } else if (
                selectedProgram &&
                matches.some((p) => String(p.id) === String(selectedProgram))
            ) {
                programSelect.value = String(selectedProgram);
            }

            // Fill years based on current program selection
            const chosen = matches.find(p => String(p.id) === String(programSelect.value));
            fillYears(chosen ? chosen.code : '');
        };

        programSelect.addEventListener('change', () => {
            const chosen = programs.find(p => String(p.id) === String(programSelect.value));
            fillYears(chosen ? chosen.code : '');
        });

        departmentFilter.addEventListener('change', fillPrograms);
        fillPrograms();
    }

    // ── Student ID input — format as YYYY-NNNNN ──────────────────────────────
    const studentIdInput = document.querySelector('[data-student-id-input]');
    if (studentIdInput) {
        studentIdInput.addEventListener('input', () => {
            let val = studentIdInput.value.replace(/[^\d]/g, ''); // digits only
            if (val.length > 4) {
                val = val.slice(0, 4) + '-' + val.slice(4, 10);
            }
            studentIdInput.value = val;
        });

        studentIdInput.addEventListener('paste', (e) => {
            e.preventDefault();
            let pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^\d]/g, '');
            if (pasted.length > 4) {
                pasted = pasted.slice(0, 4) + '-' + pasted.slice(4, 10);
            }
            studentIdInput.value = pasted;
        });
    }

    // ── +63 phone input ──────────────────────────────────────────────────────
    // Accepts: 09171234567 | 9171234567 | +639171234567
    // Displays: 917-123-4567  (formatted, 10 digits after +63)
    // Stored:   +639171234567 (normalised server-side)
    const phoneInput = document.querySelector('[data-phone-input]');
    if (phoneInput) {
        // Pull out exactly 10 raw digits regardless of how the user typed them
        const extractDigits = (val) => {
            let d = val.replace(/\D/g, '');
            if (d.startsWith('0'))              d = d.slice(1);   // 09xx → 9xx
            if (d.startsWith('63') && d.length > 10) d = d.slice(2); // +639xx → 9xx
            return d.slice(0, 10);
        };

        // Format 10 digits as 9xx-xxx-xxxx
        const fmt = (d) => {
            if (d.length <= 3) return d;
            if (d.length <= 6) return d.slice(0, 3) + '-' + d.slice(3);
            return d.slice(0, 3) + '-' + d.slice(3, 6) + '-' + d.slice(6);
        };

        phoneInput.addEventListener('input', () => {
            const digits   = extractDigits(phoneInput.value);
            const formatted = fmt(digits);
            phoneInput.value = formatted;
        });

        phoneInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            phoneInput.value = fmt(extractDigits(pasted));
        });
    }
});
