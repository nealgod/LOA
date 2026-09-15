document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navPanel = document.querySelector('[data-nav-panel]');

    if (navToggle && navPanel) {
        navToggle.addEventListener('click', () => {
            navPanel.classList.toggle('hidden');
        });
    }

    const roleSelect = document.querySelector('[data-role-select]');
    const departmentWrap = document.querySelector('[data-department-wrap]');

    if (roleSelect && departmentWrap) {
        const syncDepartment = () => {
            const needsDepartment = roleSelect.value === 'department_head';
            departmentWrap.classList.toggle('hidden', !needsDepartment);
            const select = departmentWrap.querySelector('select');
            if (select) {
                select.required = needsDepartment;
                if (!needsDepartment) {
                    select.value = '';
                }
            }
        };

        roleSelect.addEventListener('change', syncDepartment);
        syncDepartment();
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrap = button.closest('[data-password-wrap]');
            const input = wrap?.querySelector('[data-password-input]');
            if (!input) {
                return;
            }

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.textContent = showing ? 'Show' : 'Hide';
        });
    });

    const departmentFilter = document.querySelector('[data-department-filter]');
    const programSelect = document.querySelector('[data-program-select]');
    const programsJson = document.querySelector('[data-programs-json]');

    if (departmentFilter && programSelect && programsJson) {
        const programs = JSON.parse(programsJson.textContent || '[]');
        const selectedProgram = programSelect.dataset.selectedProgram || '';

        const fillPrograms = () => {
            const departmentId = departmentFilter.value;
            const matches = programs.filter((program) => String(program.department_id) === String(departmentId));

            programSelect.innerHTML = '';

            if (!departmentId) {
                programSelect.append(new Option('Select department first', ''));
                return;
            }

            if (matches.length === 0) {
                programSelect.append(new Option('No programs found', ''));
                return;
            }

            if (matches.length > 1) {
                programSelect.append(new Option('Select program', ''));
            }

            matches.forEach((program) => {
                const option = new Option(program.name, program.id);
                programSelect.append(option);
            });

            if (matches.length === 1) {
                programSelect.value = String(matches[0].id);
            } else if (selectedProgram && matches.some((program) => String(program.id) === String(selectedProgram))) {
                programSelect.value = String(selectedProgram);
            }
        };

        departmentFilter.addEventListener('change', fillPrograms);
        fillPrograms();
    }
});
