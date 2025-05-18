document.addEventListener('DOMContentLoaded', function () {
    const menuImage = document.getElementById('menuImage');
    const staffImage = document.getElementById('staffImage');
    const menuTable = document.getElementById('menuTableContainer');
    const staffTable = document.getElementById('staffTableContainer');
    const notification = document.querySelector('.notification');

    // Notification animation
    if (notification) {
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function resetActiveState() {
        menuImage.classList.remove('active-img', 'shrink');
        staffImage.classList.remove('active-img', 'shrink');
    }

    function shrinkAllImages() {
        menuImage.classList.add('shrink');
        staffImage.classList.add('shrink');
    }

    function resetImageSizeIfNoTablesVisible() {
        if (menuTable.classList.contains('hidden') && staffTable.classList.contains('hidden')) {
            menuImage.classList.remove('shrink', 'active-img');
            staffImage.classList.remove('shrink', 'active-img');
        }
    }

    // Toggle Menu
    menuImage.addEventListener('click', () => {
        const isVisible = !menuTable.classList.contains('hidden');
        menuTable.classList.add('hidden');
        staffTable.classList.add('hidden');
        resetActiveState();

        if (!isVisible) {
            menuTable.classList.remove('hidden');
            menuImage.classList.add('active-img', 'shrink');
            staffImage.classList.add('shrink');
        } else {
            resetImageSizeIfNoTablesVisible();
        }
    });

    // Toggle Staff
    staffImage.addEventListener('click', () => {
        const isVisible = !staffTable.classList.contains('hidden');
        menuTable.classList.add('hidden');
        staffTable.classList.add('hidden');
        resetActiveState();

        if (!isVisible) {
            staffTable.classList.remove('hidden');
            staffImage.classList.add('active-img', 'shrink');
            menuImage.classList.add('shrink');
        } else {
            resetImageSizeIfNoTablesVisible();
        }
    });

    // Real-time staff status update
    function updateStaffStatus() {
        // Only update if staff table is visible
        if (staffTable.classList.contains('hidden')) return;
        fetch('fetch_staff_status.php')
            .then(response => response.json())
            .then(data => {
                const tbody = staffTable.querySelector('tbody');
                tbody.innerHTML = '';
                data.forEach(row => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${row.staffID}</td>
                            <td>${row.firstName}</td>
                            <td>${row.lastName}</td>
                            <td>
                                <span class="status-badge ${row.status}">
                                    ${row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                                </span>
                            </td>
                            <td class="actions">
                                <div class="action-buttons">
                                    <a href="../Records/STAFF RECORDS/editStaff.php?id=${row.staffID}" class="btn edit-btn">Edit</a>
                                    <a href="../Records/STAFF RECORDS/deleteStaff.php?id=${row.staffID}" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            });
    }

    // Poll every 5 seconds
    setInterval(updateStaffStatus, 5000);
    // Also update immediately when staff table is shown
    staffImage.addEventListener('click', () => {
        setTimeout(updateStaffStatus, 200); // slight delay to ensure table is visible
    });
});