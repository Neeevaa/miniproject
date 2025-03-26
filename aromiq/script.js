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
        fetch('update-order-status.php', {
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
                alert(data.message || 'Error updating order status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the order status. Please try again.');
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
            switch(data.order_status) {
                case 'Pending': progress = 25; break;
                case 'Preparing': progress = 50; break;
                case 'Ready': progress = 75; break;
                case 'Completed': progress = 100; break;
            }
        })
        .catch(error => console.error('Error checking for new orders:', error));
    }

    // Replace or update your updateCartSession function
    function updateCartSession(cart) {
        return fetch('menu.php?update_cart=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Add this to identify AJAX requests
            },
            body: JSON.stringify(cart)
        })
        .then(response => {
            // First check if the response is OK
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            // Then try to parse the JSON
            return response.json().catch(error => {
                console.error('Error parsing JSON:', error);
                throw new Error('Invalid JSON response');
            });
        })
        .then(data => {
            // Handle the JSON response
            if (data.status === 'error') {
                console.error('Server error:', data.message);
                throw new Error(data.message);
            }
            return data;
        })
        .catch(error => {
            console.error('Error updating cart:', error);
            // Show error to user
            alert('Error updating cart: ' + error.message);
            throw error; // Re-throw to allow further handling
        });
    }
});