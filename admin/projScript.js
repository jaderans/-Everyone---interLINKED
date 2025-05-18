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

// Tab Switching
const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');

tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        const tabId = button.getAttribute('data-tab');

        // Hide all tabs and remove active class
        tabContents.forEach(tab => tab.style.display = 'none');
        tabButtons.forEach(btn => btn.classList.remove('active'));

        // Show selected tab and add active class
        document.getElementById(tabId).style.display = 'block';
        button.classList.add('active');
    });
});

// Modal handling
const modals = {
    'upload-image-modal': {
        openBtn: document.getElementById('change-photo-btn'),
        closeBtn: document.querySelector('#upload-image-modal .close-modal'),
        cancelBtn: document.getElementById('cancel-upload'),
        modal: document.getElementById('upload-image-modal')
    },
    'edit-admin-modal': {
        openBtns: document.querySelectorAll('.edit-admin'),
        closeBtn: document.querySelector('#edit-admin-modal .close-modal'),
        cancelBtn: document.getElementById('cancel-edit-admin'),
        modal: document.getElementById('edit-admin-modal')
    }
};

// Open modal function
function openModal(modal) {
    modal.style.display = 'block';
}

// Close modal function
function closeModal(modal) {
    modal.style.display = 'none';
}

// Setup modal event listeners
Object.keys(modals).forEach(key => {
    const modal = modals[key];

    // Open button(s)
    if(modal.openBtn) {
        modal.openBtn.addEventListener('click', () => openModal(modal.modal));
    } else if(modal.openBtns) {
        modal.openBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const adminId = this.getAttribute('data-id');

                if(key === 'view-admin-modal') {
                    loadAdminDetails(adminId);
                } else if(key === 'delete-admin-modal') {
                    document.getElementById('delete_admin_id').value = adminId;
                } else if(key === 'edit-admin-modal') {
                    loadAdminForEdit(adminId);
                }

                openModal(modal.modal);
            });
        });
    }

    // Close button
    if(modal.closeBtn) {
        modal.closeBtn.addEventListener('click', () => closeModal(modal.modal));
    }

    // Cancel button
    if(modal.cancelBtn) {
        modal.cancelBtn.addEventListener('click', () => closeModal(modal.modal));
    }

    // Close when clicking outside
    window.addEventListener('click', (e) => {
        if(e.target === modal.modal) {
            closeModal(modal.modal);
        }
    });
});

// Image preview
const profileImageInput = document.getElementById('profile_image');
const imagePreview = document.getElementById('image-preview');

profileImageInput.addEventListener('change', function() {
    const file = this.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Edit profile button links to profile tab
document.getElementById('edit-profile-btn').addEventListener('click', function() {
    // Click the profile details tab
    document.querySelector('[data-tab="profile-details"]').click();
    // Scroll to profile form
    document.getElementById('profile-form').scrollIntoView({ behavior: 'smooth' });
});

// Search functionality
document.getElementById('admin-search').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.getElementById('admin-table-body').querySelectorAll('tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

// Load admin details for view modal
function loadAdminDetails(adminId) {
    // In a real application, you would fetch this data via AJAX
    // For now we'll use dummy data based on the table
    fetch(`get_admin.php?id=${adminId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('view-admin-name').textContent = data.firstName + ' ' + data.lastName;
            document.getElementById('view-admin-email').textContent = data.email;
            document.getElementById('view-admin-username').textContent = data.username;
            document.getElementById('view-admin-contact').textContent = data.contact;
            document.getElementById('view-admin-country').textContent = data.country;
            document.getElementById('view-admin-birthday').textContent = data.birthday;
            document.getElementById('view-admin-image').src = data.image || '../../imgs/profile.png';
        })
        .catch(error => {
            console.error('Error loading admin details:', error);
            alert('Error loading administrator details. Please try again.');
        });
}

// Load admin data for edit modal
function loadAdminForEdit(adminId) {
    // In a real application, you would fetch this data via AJAX
    fetch(`get_admin.php?id=${adminId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_admin_id').value = adminId;
            document.getElementById('edit_first_name').value = data.firstName;
            document.getElementById('edit_last_name').value = data.lastName;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_contact').value = data.contact;
            document.getElementById('edit_country').value = data.country;
            document.getElementById('edit_birthday').value = data.birthday;
        })
        .catch(error => {
            console.error('Error loading admin data for edit:', error);
            alert('Error loading administrator data. Please try again.');
        });
}

// Password validation
document.getElementById('password-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if(newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirm password do not match.');
        return false;
    }

    // Password strength validation
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if(!passwordRegex.test(newPassword)) {
        e.preventDefault();
        alert('Password does not meet the requirements. Please check the requirements list.');
        return false;
    }
});

function deleteProject(projectId) {
    if (!confirm('Are you sure you want to delete this project?')) return;

    fetch('deleteProject.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'project_id=' + encodeURIComponent(projectId)
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload(); // or remove project from DOM
            }
        })
        .catch(error => console.error('Error:', error));
}