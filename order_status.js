function updateOrderStatus(orderId, status) {
    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI to reflect new status
            const statusCell = document.querySelector(`tr[data-order-id="${orderId}"] .order-status`);
            if (statusCell) {
                statusCell.value = status;
                // Update row color or other visual indicators
                updateRowAppearance(orderId, status);
            }
        } else {
            alert(data.message || 'An error occurred while updating the order status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function updateRowAppearance(orderId, status) {
    const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
    if (row) {
        // Remove existing status classes
        row.classList.remove('status-pending', 'status-preparing', 'status-ready', 'status-plating', 'status-served');
        // Add new status class
        row.classList.add(`status-${status.toLowerCase()}`);
    }
}

// Add event listeners to status select elements
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-status').forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.closest('tr').getAttribute('data-order-id');
            const newStatus = this.value;
            updateOrderStatus(orderId, newStatus);
        });
    });
}); 