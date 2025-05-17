// Global variables
let currentProjectId = null;

// DOM Elements
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const projectModal = document.getElementById('projectModal');
    const statusModal = document.getElementById('statusModal');
    const closeModal = document.getElementById('closeModal');
    const closeStatusModal = document.getElementById('closeStatusModal');
    const addProjectBtn = document.getElementById('addProjectBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelStatusBtn = document.getElementById('cancelStatusBtn');
    const projectForm = document.getElementById('projectForm');
    const statusForm = document.getElementById('statusForm');
    const selectAllCheckbox = document.getElementById('selectAll');
    const startDateInput = document.getElementById('projectStartDate');
    const endDateInput = document.getElementById('projectEndDate');

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value;
            window.location.href = `adminProj.php?search=${encodeURIComponent(searchTerm)}&filter=${getActiveFilter()}`;
        });
    }

    // Modal controls
    if (addProjectBtn) addProjectBtn.addEventListener('click', () => openProjectModal());
    if (closeModal) closeModal.addEventListener('click', closeProjectModal);
    if (closeStatusModal) closeStatusModal.addEventListener('click', closeStatusUpdateModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeProjectModal);
    if (cancelStatusBtn) cancelStatusBtn.addEventListener('click', closeStatusUpdateModal);

    window.addEventListener('click', function (event) {
        if (event.target === projectModal) closeProjectModal();
        if (event.target === statusModal) closeStatusUpdateModal();
    });

    // Select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.project-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Date validation
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            endDateInput.min = this.value;
            if (endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });

        endDateInput.addEventListener('change', function () {
            if (startDateInput.value && this.value < startDateInput.value) {
                alert('End date cannot be earlier than start date');
                this.value = startDateInput.value;
            }
        });
    }

    // Project form submission
    if (projectForm) {
        projectForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('saveProject.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the project');
                });
        });
    }

    // Status form submission
    if (statusForm) {
        statusForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('updateStatus.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating status');
                });
        });
    }
});

// Utility Functions
function getActiveFilter() {
    return new URLSearchParams(window.location.search).get('filter') || 'all';
}

function filterProjects(status) {
    const search = new URLSearchParams(window.location.search).get('search') || '';
    window.location.href = `adminProj.php?filter=${status}&search=${encodeURIComponent(search)}`;
}

function openProjectModal(projectId = null) {
    currentProjectId = projectId;
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('projectForm');
    const today = new Date();
    const formattedToday = today.toISOString().split('T')[0];

    if (projectId) {
        modalTitle.textContent = 'Edit Project';
        loadProjectData(projectId);
    } else {
        modalTitle.textContent = 'Add New Project';
        form.reset();
        document.getElementById('projectId').value = '';
        document.getElementById('projectStartDate').value = formattedToday;

        const endDate = new Date();
        endDate.setDate(today.getDate() + 30);
        document.getElementById('projectEndDate').value = endDate.toISOString().split('T')[0];
        document.getElementById('projectStatus').value = 'Pending';
    }

    document.getElementById('projectModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeProjectModal() {
    document.getElementById('projectModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentProjectId = null;
}

function openStatusModal(projectId) {
    document.getElementById('statusProjectId').value = projectId;
    document.getElementById('statusModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeStatusUpdateModal() {
    document.getElementById('statusModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function editProject(projectId) {
    openProjectModal(projectId);
}

function updateStatus(projectId) {
    openStatusModal(projectId);
}

function loadProjectData(projectId) {
    fetch(`getProject.php?id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const project = data.project;
                document.getElementById('projectId').value = project.PRO_ID;
                document.getElementById('projectTitle').value = project.PRO_TITLE;
                document.getElementById('projectType').value = project.PRO_TYPE;
                document.getElementById('projectPriority').value = project.PRO_PRIORITY_LEVEL;
                document.getElementById('projectStartDate').value = project.PRO_START_DATE;
                document.getElementById('projectEndDate').value = project.PRO_END_DATE;
                document.getElementById('projectAssignee').value = project.USER_ID || '';
                document.getElementById('projectCommissionedBy').value = project.PRO_COMMISSIONED_BY;
                document.getElementById('projectDescription').value = project.PRO_DESCRIPTION;
                document.getElementById('projectStatus').value = project.PRO_STATUS;
            } else {
                alert('Error loading project data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading project data');
        });
}

// Bulk delete
function bulkDeleteProjects() {
    const checkedBoxes = document.querySelectorAll('.project-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select projects to delete');
        return;
    }

    if (!confirm(`Are you sure you want to delete ${checkedBoxes.length} project(s)?`)) {
        return;
    }

    const projectIds = Array.from(checkedBoxes).map(cb => cb.value);

    fetch('bulkDeleteProjects.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ projectIds: projectIds })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting projects');
        });
}
