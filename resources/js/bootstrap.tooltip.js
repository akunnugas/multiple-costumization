import { Tooltip } from 'bootstrap';

// Initialize tooltips on elements
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (tooltipTriggerEl) {
    new Tooltip(tooltipTriggerEl);
});