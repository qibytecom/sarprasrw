// Custom JavaScript for SARPRAS RW System

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Image preview for file inputs
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            previewImage(e.target);
        });
    });

    // Date validation for peminjaman forms
    setupDateValidation();

    // Initialize data tables if DataTables is available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
            }
        });
    }
});

// Image preview function
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = input.parentElement.querySelector('.image-preview');
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create preview element if it doesn't exist
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview img-thumbnail mt-2';
                img.style.maxWidth = '200px';
                img.style.maxHeight = '200px';
                input.parentElement.appendChild(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Date validation for peminjaman
function setupDateValidation() {
    var tanggalPinjam = document.getElementById('tanggal_pinjam');
    var tanggalKembali = document.getElementById('tanggal_kembali');

    if (tanggalPinjam && tanggalKembali) {
        // Set minimum date to today
        var today = new Date().toISOString().split('T')[0];
        tanggalPinjam.min = today;

        tanggalPinjam.addEventListener('change', function() {
            tanggalKembali.min = this.value;
            if (tanggalKembali.value && tanggalKembali.value < this.value) {
                tanggalKembali.value = this.value;
            }
        });
    }
}

// AJAX functions for dynamic content
function loadSarprasByKategori(kategori) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_sarpras_by_kategori.php?kategori=' + encodeURIComponent(kategori), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('sarpras-container').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

// Confirm delete function
function confirmDelete(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Show loading spinner
function showLoading(elementId) {
    var element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    }
}

// Hide loading spinner
function hideLoading(elementId) {
    var element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '';
    }
}

// Format currency (if needed for future features)
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Teks berhasil disalin!', 'success');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

// Toast notification system
function showToast(message, type = 'info') {
    var toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    var toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-white bg-' + type + ' border-0';
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);
    var toast = new bootstrap.Toast(toastEl);
    toast.show();

    // Remove toast element after it's hidden
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

// Debounce function for search inputs
function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
        var later = function() {
            clearTimeout(timeout);
            func();
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
function setupSearch() {
    var searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function(input) {
        input.addEventListener('input', debounce(function() {
            performSearch(input.value, input.dataset.target);
        }, 300));
    });
}

function performSearch(query, target) {
    var rows = document.querySelectorAll('#' + target + ' tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if (text.includes(query.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Initialize search on page load
setupSearch();

// Export table to CSV
function exportTableToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    var csv = [];
    var rows = table.querySelectorAll('tr');

    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll('td, th');

        for (var j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }

        csv.push(row.join(','));
    }

    // Download CSV file
    var csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    var encodedUri = encodeURI(csvContent);
    var link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', filename + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print functionality
function printElement(elementId) {
    var printContent = document.getElementById(elementId).innerHTML;
    var originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload();
}

// Modal helper functions
function showModal(modalId) {
    var modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

function hideModal(modalId) {
    var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// Form reset helper
function resetForm(formId) {
    var form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
}

// Dynamic form field addition/removal
function addFormField(containerId, templateId) {
    var container = document.getElementById(containerId);
    var template = document.getElementById(templateId);
    var clone = template.content.cloneNode(true);
    container.appendChild(clone);
}

function removeFormField(button) {
    button.closest('.form-field').remove();
}

// Local storage helpers for form persistence
function saveFormData(formId) {
    var form = document.getElementById(formId);
    var formData = new FormData(form);
    var data = {};

    for (var [key, value] of formData.entries()) {
        data[key] = value;
    }

    localStorage.setItem('form_' + formId, JSON.stringify(data));
}

function loadFormData(formId) {
    var data = localStorage.getItem('form_' + formId);
    if (data) {
        data = JSON.parse(data);
        var form = document.getElementById(formId);

        for (var key in data) {
            var element = form.elements[key];
            if (element) {
                element.value = data[key];
            }
        }
    }
}

function clearFormData(formId) {
    localStorage.removeItem('form_' + formId);
}

// Animation helpers
function fadeIn(element) {
    element.style.opacity = '0';
    element.style.display = 'block';
    (function fade() {
        var val = parseFloat(element.style.opacity);
        if (!((val += .1) > 1)) {
            element.style.opacity = val;
            requestAnimationFrame(fade);
        }
    })();
}

function fadeOut(element) {
    element.style.opacity = '1';
    (function fade() {
        if ((element.style.opacity -= .1) < 0) {
            element.style.display = 'none';
        } else {
            requestAnimationFrame(fade);
        }
    })();
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    showToast('Terjadi kesalahan pada aplikasi. Silakan refresh halaman.', 'danger');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    showToast('Terjadi kesalahan pada aplikasi. Silakan refresh halaman.', 'danger');
});
