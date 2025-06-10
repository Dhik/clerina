// resources/js/content-plan-helpers.js
// Helper functions for Content Plan management

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-CA'); // YYYY-MM-DD format
}

/**
 * Format datetime for display
 */
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString();
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status, label) {
    const colors = {
        'draft': 'secondary',
        'content_writing': 'info',
        'creative_review': 'warning',
        'admin_support': 'primary',
        'content_editing': 'dark',
        'ready_to_post': 'success',
        'posted': 'success'
    };
    
    const color = colors[status] || 'light';
    return `<span class="badge badge-${color}">${label}</span>`;
}

/**
 * Truncate text for display
 */
function truncateText(text, length = 50) {
    if (!text) return '-';
    return text.length > length ? text.substring(0, length) + '...' : text;
}

/**
 * Show loading state
 */
function showLoading(element) {
    element.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    element.prop('disabled', true);
}

/**
 * Hide loading state
 */
function hideLoading(element, originalText) {
    element.html(originalText);
    element.prop('disabled', false);
}

/**
 * Show validation errors
 */
function showValidationErrors(errors) {
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Show new errors
    $.each(errors, function(field, messages) {
        const input = $(`[name="${field}"]`);
        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
    });
}

/**
 * Clear validation errors
 */
function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

/**
 * AJAX error handler
 */
function handleAjaxError(xhr, defaultMessage = 'An error occurred') {
    if (xhr.responseJSON && xhr.responseJSON.errors) {
        showValidationErrors(xhr.responseJSON.errors);
    } else {
        const message = xhr.responseJSON?.message || defaultMessage;
        toastr.error(message);
    }
}

/**
 * Confirm delete action
 */
function confirmDelete(callback, title = 'Are you sure?', text = "You won't be able to revert this!") {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

/**
 * Show success message
 */
function showSuccess(message, title = 'Success!') {
    Swal.fire(title, message, 'success');
}

/**
 * Show error message
 */
function showError(message, title = 'Error!') {
    Swal.fire(title, message, 'error');
}

/**
 * Reset form and clear errors
 */
function resetForm(formId) {
    $(`#${formId}`)[0].reset();
    clearValidationErrors();
}

/**
 * Set form field value safely
 */
function setFieldValue(fieldId, value) {
    const field = $(`#${fieldId}`);
    if (field.length) {
        field.val(value || '');
    }
}

/**
 * Get field value safely
 */
function getFieldValue(fieldId) {
    const field = $(`#${fieldId}`);
    return field.length ? field.val() : '';
}

/**
 * Initialize select2 for better dropdowns
 */
function initializeSelect2(selector = '.select2') {
    if ($.fn.select2) {
        $(selector).select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
}

/**
 * Initialize daterangepicker
 */
function initializeDatePicker(selector = '.datepicker') {
    if ($.fn.daterangepicker) {
        $(selector).daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    }
}

/**
 * Auto-resize textareas
 */
function autoResizeTextarea(selector = 'textarea') {
    $(selector).each(function() {
        this.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
}

/**
 * Character counter for textareas
 */
function addCharacterCounter(selector, maxLength = 2200) {
    $(selector).on('input', function() {
        const currentLength = $(this).val().length;
        const counter = $(this).siblings('.character-counter');
        
        if (counter.length === 0) {
            $(this).after(`<small class="form-text text-muted character-counter"></small>`);
        }
        
        const counterElement = $(this).siblings('.character-counter');
        counterElement.text(`${currentLength}/${maxLength} characters`);
        
        if (currentLength > maxLength) {
            counterElement.removeClass('text-muted').addClass('text-danger');
        } else {
            counterElement.removeClass('text-danger').addClass('text-muted');
        }
    });
}

/**
 * Export functions for global use
 */
window.ContentPlanHelpers = {
    formatDate,
    formatDateTime,
    getStatusBadge,
    truncateText,
    showLoading,
    hideLoading,
    showValidationErrors,
    clearValidationErrors,
    handleAjaxError,
    confirmDelete,
    showSuccess,
    showError,
    resetForm,
    setFieldValue,
    getFieldValue,
    initializeSelect2,
    initializeDatePicker,
    autoResizeTextarea,
    addCharacterCounter
};