// script.js - Shared order management functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle status changes for both admin and chef dashboards
    const statusSelectors = document.querySelectorAll('.order-status, .kitchen-status');
    statusSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            updateOrderStatus(this.dataset.orderId, this.value);
        });
    });

    // Handle complete order buttons
    const completeButtons = document.querySelectorAll('.complete-order, .complete-kitchen-order');
    completeButtons.forEach(button => {
        button.addEventListener('click', function() {
            updateOrderStatus(this.dataset.orderId, 'Completed');
        });
    });

    // Function to update order status
    function updateOrderStatus(orderId, status) {
        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page to show updated status
                location.reload();
            } else {
                alert('Error updating order status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status');
        });
    }

    // Poll for new orders every 30 seconds
    setInterval(checkForNewOrders, 30000);

    function checkForNewOrders() {
        fetch('check_new_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNewOrders) {
                location.reload();
            }
        })
        .catch(error => console.error('Error checking for new orders:', error));
    }
});