// Sidebar Toggle
const sidebarToggle = document.querySelector('.sidebar-toggle');
const adminSidebar = document.querySelector('.admin-sidebar');
const adminContent = document.querySelector('.admin-content');

sidebarToggle.addEventListener('click', () => {
    adminSidebar.classList.toggle('active');
    adminContent.classList.toggle('active');
});

// Table Row Click
document.querySelectorAll('.admin-table tbody tr').forEach(row => {
    row.addEventListener('click', (e) => {
        // Don't navigate if clicking on a button or link
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.parentElement.tagName === 'A') {
            return;
        }
        
        // Find the first link in the row and follow it
        const link = row.querySelector('a');
        if (link) {
            window.location.href = link.href;
        }
    });
});

// Search Functionality
const searchBox = document.querySelector('.search-box input');
if (searchBox) {
    searchBox.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.admin-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

// Confirmation for delete actions
document.querySelectorAll('.btn-danger').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
});

// Tab functionality for admin pages
const adminTabs = document.querySelectorAll('.admin-tab');
if (adminTabs.length > 0) {
    adminTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.admin-tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            tab.classList.add('active');
            const tabId = tab.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

// Initialize charts (using Chart.js - you'll need to include the library)
if (typeof Chart !== 'undefined') {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 15000, 22000, 18000, 25000],
                    backgroundColor: 'rgba(74, 107, 255, 0.2)',
                    borderColor: 'rgba(74, 107, 255, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Date picker initialization (using flatpickr - include the library)
if (typeof flatpickr !== 'undefined') {
    flatpickr('.date-picker', {
        dateFormat: 'Y-m-d',
        allowInput: true
    });
}
