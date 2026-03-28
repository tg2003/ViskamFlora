// viskam_flora_full/assets/js/main.js

/**
 * Global Javascript functions for Viskam Flora
 */

document.addEventListener('DOMContentLoaded', () => {
    // Add any global initialization here

    // Example: Auto fade-out for alert messages (if any exist)
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300); // Wait for transition
            });
        }, 5000);
    }
});

// Update cart quantity handler in frontend interfaces
function updateCartQty(productId, change) {
    const input = document.getElementById('qty_' + productId);
    if (!input) return;
    
    let currentQty = parseInt(input.value);
    let newQty = currentQty + change;
    
    if (newQty >= 1) {
        input.value = newQty;
        // The actual update mechanism would usually involve a form submit or an AJAX call.
        // We'll leave it to the specific page logic, or trigger a form submit.
    }
}
