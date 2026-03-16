/**
 * Custom UI Logic for TruckerConnect
 * Consolidated from various Blade templates.
 */

// --- Global UI State ---
window.openSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar) sidebar.classList.remove('-translate-x-full');
    if (backdrop) backdrop.classList.remove('hidden');
};

window.closeSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar) sidebar.classList.add('-translate-x-full');
    if (backdrop) backdrop.classList.add('hidden');
};

window.toggleProfileDropdown = function() {
    const profile = document.getElementById('profileDropdown');
    const notifications = document.getElementById('notificationDropdown');
    if (profile) profile.classList.toggle('hidden');
    if (notifications) notifications.classList.add('hidden');
};

window.toggleNotifications = function() {
    const notifications = document.getElementById('notificationDropdown');
    const profile = document.getElementById('profileDropdown');
    if (notifications) notifications.classList.toggle('hidden');
    if (profile) profile.classList.add('hidden');
};

window.dismissToast = function() {
    const t = document.getElementById('toast');
    if (t) {
        t.style.opacity = '0';
        t.style.transform = 'translateY(20px)';
        setTimeout(() => t.remove(), 300);
    }
};

// --- Notification Detail Modal ---
window.showNotificationDetail = function(title, message, time) {
    const modalTitle = document.getElementById('notifModalTitle');
    const modalMessage = document.getElementById('notifModalMessage');
    const modalTime = document.getElementById('notifModalTime');
    const modal = document.getElementById('notificationModal');
    const dropdown = document.getElementById('notificationDropdown');

    if (modalTitle) modalTitle.innerText = title;
    if (modalMessage) modalMessage.innerText = message;
    if (modalTime) modalTime.innerText = time;
    if (modal) modal.classList.remove('hidden');
    if (dropdown) dropdown.classList.add('hidden');
};

window.closeNotificationModal = function() {
    const modal = document.getElementById('notificationModal');
    if (modal) modal.classList.add('hidden');
};

// --- Utility helpers ---
function computeInitials(name) {
    if (!name) return '';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
}

function setProfilePreview({ previewId, initialsId, imageUrl, name }) {
    const img = document.getElementById(previewId);
    const initials = document.getElementById(initialsId);
    const hasImage = Boolean(imageUrl);

    if (img) {
        if (hasImage) {
            img.src = imageUrl;
            img.classList.remove('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
        }
    }

    if (initials) {
        initials.textContent = computeInitials(name);
        if (hasImage) {
            initials.classList.add('hidden');
        } else {
            initials.classList.remove('hidden');
        }
    }
}

// --- Generic Modal Controls (Brokers/Drivers) ---
window.openCreateModal = function() {
    const modal = document.getElementById('createModal');
    if (!modal) return;

    setProfilePreview({
        previewId: 'create_image_preview',
        initialsId: 'create_initials',
        imageUrl: null,
        name: document.getElementById('create_full_name')?.value || ''
    });

    setProfilePreview({
        previewId: 'create_broker_image_preview',
        initialsId: 'create_broker_initials',
        imageUrl: null,
        name: document.getElementById('create_broker_full_name')?.value || ''
    });

    modal.classList.remove('hidden');
};

window.closeCreateModal = function() {
    const modal = document.getElementById('createModal');
    if (modal) modal.classList.add('hidden');
};

window.openEditModal = function(data, type = 'broker') {
    const form = document.getElementById('editForm');
    const modal = document.getElementById('editModal');

    if (!form || !modal) return;

    const isDriver = type === 'driver';
    const isBroker = type === 'broker';
    const imageUrl = data.user_image ? `/storage/${data.user_image}` : null;

    if (isDriver) {
        form.action = '/drivers/' + data.id;
        document.getElementById('edit_full_name').value = data.full_name || '';
        document.getElementById('edit_phone').value = data.phone || '';
        document.getElementById('edit_email').value = data.email || '';
        document.getElementById('edit_license_number').value = data.license_number || '';
        document.getElementById('edit_truck_info').value = data.truck_info || '';
        document.getElementById('edit_is_active').checked = data.is_active == 1;

        setProfilePreview({
            previewId: 'edit_image_preview',
            initialsId: 'edit_initials',
            imageUrl,
            name: data.full_name || ''
        });
    }

    if (isBroker) {
        form.action = '/brokers/' + data.id;
        document.getElementById('edit_full_name').value = data.full_name || '';
        document.getElementById('edit_phone').value = data.phone || '';
        document.getElementById('edit_email').value = data.email || '';
        document.getElementById('edit_is_active').checked = data.is_active == 1;

        setProfilePreview({
            previewId: 'edit_broker_image_preview',
            initialsId: 'edit_broker_initials',
            imageUrl,
            name: data.full_name || ''
        });
    }

    modal.classList.remove('hidden');
};

window.closeEditModal = function() {
    const modal = document.getElementById('editModal');
    if (modal) modal.classList.add('hidden');
};

// --- Notification Actions ---
window.markAsRead = function(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/notifications/${id}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById(`notification-${id}`);
            if (el) {
                el.classList.add('opacity-70');
                const btn = el.querySelector('button');
                if (btn) btn.remove();
            }
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
};

// --- Initialization & Global Listeners ---
document.addEventListener('click', function(e) {
    // Dropdown click-outside logic
    const profileWrapper = document.getElementById('profileDropdownWrapper');
    const notificationWrapper = document.getElementById('notificationDropdownWrapper');
    const profileDropdown = document.getElementById('profileDropdown');
    const notificationDropdown = document.getElementById('notificationDropdown');

    if (profileWrapper && !profileWrapper.contains(e.target) && profileDropdown) {
        profileDropdown.classList.add('hidden');
    }
    if (notificationWrapper && !notificationWrapper.contains(e.target) && notificationDropdown) {
        notificationDropdown.classList.add('hidden');
    }

    // Modal background click logic
    const createModal = document.getElementById('createModal');
    const editModal = document.getElementById('editModal');

    if (e.target === createModal) window.closeCreateModal();
    if (e.target === editModal) window.closeEditModal();
});

// Auto-dismiss toast if it exists
function updateImagePreview(input, previewId, initialsId) {
    const preview = document.getElementById(previewId);
    const initials = document.getElementById(initialsId);
    if (!preview || !input || !input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (initials) initials.classList.add('hidden');
    };

    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('toast')) {
        setTimeout(window.dismissToast, 4000);
    }

    const createInput = document.getElementById('create_user_image');
    const editInput = document.getElementById('edit_user_image');
    const createBrokerInput = document.getElementById('create_broker_image');
    const editBrokerInput = document.getElementById('edit_broker_image');

    const createFullNameInput = document.getElementById('create_full_name');
    const createBrokerFullNameInput = document.getElementById('create_broker_full_name');
    const editFullNameInput = document.getElementById('edit_full_name');

    if (createInput) {
        createInput.addEventListener('change', () => updateImagePreview(createInput, 'create_image_preview', 'create_initials'));
    }

    if (editInput) {
        editInput.addEventListener('change', () => updateImagePreview(editInput, 'edit_image_preview', 'edit_initials'));
    }

    if (createBrokerInput) {
        createBrokerInput.addEventListener('change', () => updateImagePreview(createBrokerInput, 'create_broker_image_preview', 'create_broker_initials'));
    }

    if (editBrokerInput) {
        editBrokerInput.addEventListener('change', () => updateImagePreview(editBrokerInput, 'edit_broker_image_preview', 'edit_broker_initials'));
    }

    if (createFullNameInput) {
        createFullNameInput.addEventListener('input', () => {
            setProfilePreview({
                previewId: 'create_image_preview',
                initialsId: 'create_initials',
                imageUrl: null,
                name: createFullNameInput.value
            });
        });
    }

    if (createBrokerFullNameInput) {
        createBrokerFullNameInput.addEventListener('input', () => {
            setProfilePreview({
                previewId: 'create_broker_image_preview',
                initialsId: 'create_broker_initials',
                imageUrl: null,
                name: createBrokerFullNameInput.value
            });
        });
    }

    if (editFullNameInput) {
        editFullNameInput.addEventListener('input', () => {
            const driverPreview = document.getElementById('edit_image_preview');
            const brokerPreview = document.getElementById('edit_broker_image_preview');

            if (driverPreview) {
                const imageUrl = driverPreview.classList.contains('hidden') ? null : driverPreview.src;
                setProfilePreview({
                    previewId: 'edit_image_preview',
                    initialsId: 'edit_initials',
                    imageUrl,
                    name: editFullNameInput.value
                });
            }

            if (brokerPreview) {
                const imageUrl = brokerPreview.classList.contains('hidden') ? null : brokerPreview.src;
                setProfilePreview({
                    previewId: 'edit_broker_image_preview',
                    initialsId: 'edit_broker_initials',
                    imageUrl,
                    name: editFullNameInput.value
                });
            }
        });
    }
});
