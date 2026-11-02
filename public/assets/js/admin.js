const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay=document.getElementById('overlay') ;

toggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active') ;
});
overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active') ;
});

// Modal functionality for categories
const modal = document.getElementById('categoryModal');
const openModalBtn = document.getElementById('openModalBtn');
const closeBtn = document.getElementsByClassName('close')[0];
const cancelBtn = document.getElementById('cancelBtn');
const categoryForm = document.getElementById('categoryForm');

// Open modal
if (openModalBtn) {
    openModalBtn.onclick = function () {
        modal.style.display = 'block';
    }
}

// Close modal when clicking X
if (closeBtn) {
    closeBtn.onclick = function () {
        modal.style.display = 'none';
    }
}

// Close modal when clicking cancel
if (cancelBtn) {
    cancelBtn.onclick = function () {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = 'none';
        categoryForm.reset();
    }
}

// Reset form when modal is closed
function closeModal() {
    modal.style.display = 'none';
    categoryForm.reset();
}

if (closeBtn) {
    closeBtn.onclick = closeModal;
}

if (cancelBtn) {
    cancelBtn.onclick = closeModal;
}

// Edit modal functionality
const editModal = document.getElementById('editCategoryModal');
const editCloseBtn = document.getElementsByClassName('edit-close')[0];
const editCancelBtn = document.getElementById('editCancelBtn');
const editCategoryForm = document.getElementById('editCategoryForm');

// Close edit modal
function closeEditModal() {
    editModal.style.display = 'none';
    if (editCategoryForm) editCategoryForm.reset();
}

if (editCloseBtn) {
    editCloseBtn.onclick = closeEditModal;
}

if (editCancelBtn) {
    editCancelBtn.onclick = closeEditModal;
}

// Close edit modal when clicking outside
window.onclick = function (event) {
    if (event.target == modal) {
        closeModal();
    }
    if (event.target == editModal) {
        closeEditModal();
    }
}

// Show edit modal if we have category data
document.addEventListener('DOMContentLoaded', function () {
    // Check if edit modal should be shown (data will be passed from PHP)
    const editModal = document.getElementById('editCategoryModal');
    const editCategoryId = document.getElementById('editCategoryId');

    if (editModal && editCategoryId && editCategoryId.value) {
        editModal.style.display = 'block';
    }
});

// Handle toggle status buttons
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('toggle-status-btn')) {
        e.preventDefault();
        const button = e.target;
        const categoryId = button.getAttribute('data-id');
        const currentStatus = button.getAttribute('data-current-status');

        // Disable button during request
        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = '⏳';

        // Prepare form data
        const formData = new FormData();
        formData.append('id', categoryId);

        // Send AJAX requestjhjkjk
        fetch('/IbTechnologie/public/admin/categories/toggle-status', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // modifier le boutton et changer l'apparence
                    const row = button.closest('tr');
                    const statusBadge = row.querySelector('.status-badge');

                    if (data.new_status === '1') {
                        // Activer
                        button.setAttribute('data-current-status', '1');
                        button.textContent = '🔴';
                        button.title = 'Désactiver';
                        row.classList.remove('inactive');
                        row.classList.add('active');
                        statusBadge.textContent = 'Active';
                        statusBadge.classList.remove('status-inactive');
                        statusBadge.classList.add('status-active');

                        // Add edit link back
                        /*const actionsCell = button.parentElement;
                        const editLink = document.createElement('a');
                        editLink.href = '?edit=' + categoryId;
                        editLink.className = 'edit-link';
                        editLink.setAttribute('data-id', categoryId);
                        editLink.textContent = '✏️';
                        actionsCell.insertBefore(editLink, button.nextSibling);
                        actionsCell.insertBefore(document.createTextNode(' '), editLink.nextSibling);*/
                    } else {
                        // Deactivated
                        button.setAttribute('data-current-status', '0');
                        button.textContent = '🟢';
                        button.title = 'Activer';
                        row.classList.remove('active');
                        row.classList.add('inactive');
                        statusBadge.textContent = 'Désactivée';
                        statusBadge.classList.remove('status-active');
                        statusBadge.classList.add('status-inactive');

                        // Remove edit link/*
                        const editLink = row.querySelector('.edit-link');
                        /*if (editLink) {
                            editLink.remove();
                        }*/
                    }
                } else {
                    showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
                    button.textContent = originalText;
                }
                showNotification('Statut mis a jour', '1');
            })
            .catch(error => {
                showNotification('Erreur de connexion', 'error');
                button.textContent = originalText;
            })
            .finally(() => {
                // Re-enable button
                button.disabled = false;
            });
    }
});

// Notification system
function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Add to page
    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);

    // Hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}