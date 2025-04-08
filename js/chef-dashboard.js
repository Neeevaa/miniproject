// Chef Dashboard JavaScript Functions
document.addEventListener('DOMContentLoaded', function() {
    // Create loader if it doesn't exist
    if (!document.getElementById('loader')) {
        const loaderDiv = document.createElement('div');
        loaderDiv.id = 'loader';
        loaderDiv.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; text-align:center; padding-top:20%;';
        
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-light';
        spinner.setAttribute('role', 'status');
        
        const spinnerText = document.createElement('span');
        spinnerText.className = 'visually-hidden';
        spinnerText.textContent = 'Loading...';
        
        spinner.appendChild(spinnerText);
        loaderDiv.appendChild(spinner);
        document.body.appendChild(loaderDiv);
    }
    
    // Create connection warning if it doesn't exist
    if (!document.getElementById('connectionWarning')) {
        const warningDiv = document.createElement('div');
        warningDiv.id = 'connectionWarning';
        warningDiv.className = 'alert alert-warning';
        warningDiv.style.display = 'none';
        warningDiv.style.position = 'fixed';
        warningDiv.style.top = '20px';
        warningDiv.style.left = '50%';
        warningDiv.style.transform = 'translateX(-50%)';
        warningDiv.style.zIndex = '9999';
        warningDiv.style.width = 'auto';
        warningDiv.style.maxWidth = '90%';
        
        warningDiv.innerHTML = `
            <strong>Warning:</strong> Connection issues detected. The dashboard may not display current information.
            <button class="btn btn-sm btn-warning ms-3" onclick="location.reload()">Refresh Page</button>
        `;
        
        document.body.appendChild(warningDiv);
    }
    
    // Initialize dashboard
    updateTableStatus();
    setInterval(updateTableStatus, 30000); // Update every 30 seconds
    
    // Periodically check for feedback submissions
    setInterval(checkActiveFeedback, 60000); // Check every minute
});

// Helper function to safely access DOM elements
function getElement(id) {
    const element = document.getElementById(id);
    if (!element) {
        console.warn(`Element with ID "${id}" not found`);
    }
    return element;
}

function loadOrderDetails(tableNumber) {
    console.log('Loading details for table:', tableNumber);
    
    const loader = getElement('loader');
    if (loader) loader.style.display = 'block';
    
    // Rest of the function remains the same but with checks for DOM elements
    // ...
}

function loadOrders() {
    console.log('Loading orders...');
    fetch('api/get_orders.php')
        .then(response => {
            // Check if response is ok before trying to parse JSON
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const tableBody = document.getElementById('ordersTableBody');
            if (!tableBody) {
                console.error('Orders table body not found!');
                return;
            }
            
            tableBody.innerHTML = '';
            if (Array.isArray(data)) {
                data.forEach(order => {
                    const statusClass = order.order_status === 'Served' ? 'success' :
                                      order.order_status === 'Plating' ? 'primary' :
                                      order.order_status === 'Preparing' ? 'info' : 'warning';
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${order.order_id}</td>
                        <td>${order.table_number}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" onclick="viewTableOrders(${order.table_number})">
                                View Details
                            </button>
                        </td>
                        <td>
                            <span class="badge bg-${statusClass}">${order.order_status}</span>
                        </td>
                        <td>${new Date(order.order_date).toLocaleTimeString()}</td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                console.error('Invalid data format received:', data);
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            // Display a more user-friendly error message
            document.getElementById('ordersTableBody').innerHTML = 
                '<tr><td colspan="6" class="text-center text-danger">Error loading orders. Please try refreshing the page.</td></tr>';
        });
}

function updateTableStatus() {
    console.log('Updating table status...');
    
    // Check if loader element exists before trying to access it
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'block';
    }
    
    fetch('api/get_table_status.php')
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || `Server error: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Table status data:', data);
            
            // Reset all tables
            for(let i = 1; i <= 12; i++) {
                const tableBox = document.getElementById(`table-${i}`);
                const tableStatus = document.getElementById(`table-status-${i}`);
                const paymentStatus = document.getElementById(`payment-status-${i}`);
                
                if (tableBox && tableStatus && paymentStatus) {
                    tableBox.classList.remove('occupied');
                    tableStatus.textContent = 'Available';
                    paymentStatus.classList.remove('payment-pending', 'payment-done');
                }
            }
            
            // Make sure we have something to iterate over
            if (!Array.isArray(data)) {
                console.log('No orders to display or invalid data format');
                if (loader) loader.style.display = 'none';
                return;
            }
            
            // Update occupied tables
            data.forEach(order => {
                if (!order || !order.table_number) {
                    console.warn('Invalid order data:', order);
                    return;
                }
                
                // Skip tables where feedback has been submitted
                if (order.feedback_submitted === 'Yes') {
                    console.log(`Table ${order.table_number} has submitted feedback, marking as available`);
                    return;
                }
                
                const tableBox = document.getElementById(`table-${order.table_number}`);
                const tableStatus = document.getElementById(`table-status-${order.table_number}`);
                const paymentStatus = document.getElementById(`payment-status-${order.table_number}`);
                
                if (tableBox && tableStatus && paymentStatus) {
                    tableBox.classList.add('occupied');
                    // Use order_status if available, otherwise fall back to status
                    const statusToDisplay = order.order_status || order.status || 'Processing';
                    tableStatus.textContent = `Occupied - ${statusToDisplay}`;
                    paymentStatus.classList.add(
                        order.payment_status === 'Paid' ? 'payment-done' : 'payment-pending'
                    );
                }
            });
            
            if (loader) loader.style.display = 'none';
        })
        .catch(error => {
            console.error('Error updating table status:', error);
            if (loader) loader.style.display = 'none';
            
            const warningEl = document.getElementById('connectionWarning');
            if (warningEl) {
                warningEl.style.display = 'block';
                setTimeout(() => {
                    warningEl.style.display = 'none';
                }, 5000);
            }
        });
}

let currentOrderId = null;
let currentOrder = null;

function viewOrderDetails(orderId) {
    console.log('Viewing order:', orderId);
    fetch(`api/get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load order details');
            }
            
            // Update modal content
            document.getElementById('modalOrderId').textContent = data.order_id;
            document.getElementById('modalCustomerName').textContent = data.customer_name;
            document.getElementById('modalTableNumber').textContent = data.table_number;
            
            const itemsBody = document.getElementById('modalOrderItems');
            if (!itemsBody) {
                console.error('Modal items body not found!');
                return;
            }
            
            itemsBody.innerHTML = '';
            let grandTotal = 0;
            
            data.items.forEach(item => {
                const total = item.quantity * item.price;
                grandTotal += total;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.food_name}</td>
                    <td>${item.quantity}</td>
                    <td>₹${item.price.toFixed(2)}</td>
                    <td>₹${total.toFixed(2)}</td>
                `;
                itemsBody.appendChild(row);
            });
            
            document.getElementById('modalGrandTotal').textContent = `₹${grandTotal.toFixed(2)}`;
            
            // Show modal
            const orderModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            orderModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details: ' + error.message);
        });
}

function updateOrderStatus(orderId, status) {
    console.log('Updating order status:', orderId, status);
    document.getElementById('loader').style.display = 'block';
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);
    
    fetch('api/update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `Server error: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Order status update result:', data);
        document.getElementById('loader').style.display = 'none';
        
        if (data.success) {
            // Update order status text directly instead of using modalElements
            const orderStatusElement = document.getElementById('orderStatusText');
            if (orderStatusElement) {
                orderStatusElement.textContent = status;
            }
            
            // Update progress bar
            updateProgressBar(status);
            
            // Update table view
            updateTableStatus();
        } else {
            alert('Failed to update order status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loader').style.display = 'none';
        alert('Error updating order status: ' + error.message);
    });
}

// Add this new function to check feedback status
function checkFeedbackStatus(orderId) {
    fetch(`api/check_feedback.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.feedback_submitted === 'Yes') {
                console.log(`Feedback submitted for order ${orderId}, table can be freed`);
                // If feedback is submitted, refresh table status to free up table
                updateTableStatus();
            }
        })
        .catch(error => {
            console.error('Error checking feedback status:', error);
        });
}

function redirectToCustomerStatus(tableNumber) {
    console.log('Redirecting to customer status for table:', tableNumber);
    window.location.href = `customer-order-status.php?table_number=${tableNumber}`;
}

function viewTableOrders(tableNumber) {
    console.log('Viewing orders for table:', tableNumber);
    
    fetch(`api/get_table_orders.php?table=${tableNumber}`)
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || `Server error: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Table order data:', data);
            
            if (data && data.order_id) {
                // Store the current order globally
                currentOrder = data;
                
                // Update modal elements
                const modalElements = {
                    orderId: document.getElementById('modalOrderId'),
                    tableNumber: document.getElementById('modalTableNumber'),
                    orderTime: document.getElementById('modalOrderTime'),
                    progressBar: document.getElementById('orderProgressBar'),
                    statusText: document.getElementById('orderStatusText'),
                    paymentStatus: document.getElementById('modalPaymentStatus'),
                    orderItems: document.getElementById('modalOrderItems'),
                    grandTotal: document.getElementById('modalGrandTotal')
                };

                // Update modal content
                if (modalElements.orderId) modalElements.orderId.textContent = currentOrder.order_id;
                if (modalElements.tableNumber) modalElements.tableNumber.textContent = currentOrder.table_number;
                if (modalElements.orderTime) modalElements.orderTime.textContent = 
                    new Date(currentOrder.order_date).toLocaleString();
                if (modalElements.paymentStatus) {
                    modalElements.paymentStatus.textContent = currentOrder.payment_status || 'Pending';
                    modalElements.paymentStatus.className = `badge ${currentOrder.payment_status === 'Paid' ? 'bg-success' : 'bg-warning'}`;
                }
                
                // Update progress bar with current status
                updateProgressBar(currentOrder.order_status);
                
                // Update order items
                if (modalElements.orderItems) {
                    modalElements.orderItems.innerHTML = '';
                    let grandTotal = 0;
                    
                    if (Array.isArray(currentOrder.items)) {
                        currentOrder.items.forEach(item => {
                            const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                            grandTotal += itemTotal;
                            
                            // Make sure we have a valid item ID - use either id, order_item_id, or item_id
                            const itemId = item.id || item.order_item_id || item.item_id;
                            
                            if (!itemId) {
                                console.warn('Item missing ID:', item);
                            }
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${item.food_name}</td>
                                <td>${item.quantity}</td>
                                <td>₹${parseFloat(item.price).toFixed(2)}</td>
                                <td>₹${itemTotal.toFixed(2)}</td>
                                <td>
                                    <select class="form-select form-select-sm" 
                                        onchange="updateItemStatus(${itemId || 0}, this.value)" 
                                        ${!itemId ? 'disabled title="Missing item ID"' : ''}>
                                        <option value="Pending" ${item.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                        <option value="Preparing" ${item.status === 'Preparing' ? 'selected' : ''}>Preparing</option>
                                        <option value="Ready" ${item.status === 'Ready' ? 'selected' : ''}>Ready</option>
                                        <option value="Plating" ${item.status === 'Plating' ? 'selected' : ''}>Plating</option>
                                        <option value="Served" ${item.status === 'Served' ? 'selected' : ''}>Served</option>
                                        <option value="Cancelled" ${item.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>
                                </td>
                            `;
                            modalElements.orderItems.appendChild(row);
                        });
                    } else {
                        // If no items array, create a single row with the total
                        const totalRow = document.createElement('tr');
                        totalRow.innerHTML = `
                            <td>Full Order</td>
                            <td>1</td>
                            <td>₹${parseFloat(currentOrder.total || 0).toFixed(2)}</td>
                            <td>₹${parseFloat(currentOrder.total || 0).toFixed(2)}</td>
                            <td>
                                <select class="form-select form-select-sm" onchange="updateOrderStatus('${currentOrder.order_id}', this.value)">
                                    <option value="Pending" ${(currentOrder.status === 'Pending' || currentOrder.order_status === 'Pending') ? 'selected' : ''}>Pending</option>
                                    <option value="Preparing" ${(currentOrder.status === 'Preparing' || currentOrder.order_status === 'Preparing') ? 'selected' : ''}>Preparing</option>
                                    <option value="Ready" ${(currentOrder.status === 'Ready' || currentOrder.order_status === 'Ready') ? 'selected' : ''}>Ready</option>
                                    <option value="Served" ${(currentOrder.status === 'Served' || currentOrder.order_status === 'Served') ? 'selected' : ''}>Served</option>
                                </select>
                            </td>
                        `;
                        modalElements.orderItems.appendChild(totalRow);
                        grandTotal = parseFloat(currentOrder.total || 0);
                    }
                    
                    // Update grand total
                    if (modalElements.grandTotal) {
                        modalElements.grandTotal.textContent = `₹${grandTotal.toFixed(2)}`;
                    }
                }

                // Show modal
                const orderModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                orderModal.show();
            } else {
                // If no order found, alert the user
                console.log('No active order for table', tableNumber);
                alert('No active order for table ' + tableNumber);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading table orders: ' + error.message);
        });
}

function updateProgressBar(status) {
    const progressBar = document.getElementById('orderProgressBar');
    const statusText = document.getElementById('orderStatusText');
    
    if (!progressBar || !statusText) {
        console.error('Progress bar elements not found');
        return;
    }

    // Default values if status is undefined
    let progressWidth = '25%';
    let progressClass = 'bg-warning';
    let text = 'Pending';

    // Provide a default value if status is undefined or null
    status = status || 'Pending';
    console.log('Setting progress bar status to:', status);

    switch(status) {
        case 'Pending':
            progressWidth = '25%';
            progressClass = 'bg-warning';
            text = 'Pending';
            break;
        case 'Preparing':
            progressWidth = '50%';
            progressClass = 'bg-info';
            text = 'Preparing';
            break;
        case 'Ready':
            // Add support for "Ready" status
            progressWidth = '75%';
            progressClass = 'bg-primary';
            text = 'Ready';
            break;
        case 'Plating':
            progressWidth = '75%';
            progressClass = 'bg-primary';
            text = 'Plating';
            break;
        case 'Served':
            progressWidth = '100%';
            progressClass = 'bg-success';
            text = 'Served';
            break;
        default:
            console.warn('Unknown status:', status);
            // Use default values for unknown statuses
            break;
    }

    // Remove all existing bg-* classes
    progressBar.className = progressBar.className.replace(/bg-\w+/g, '');
    // Add new class and set width
    progressBar.className = `progress-bar ${progressClass}`;
    progressBar.style.width = progressWidth;
    statusText.textContent = text;
}

function updateItemStatus(itemId, status) {
    console.log('Updating item status:', itemId, status);
    
    // Check for undefined or invalid item ID
    if (!itemId || itemId === "undefined" || itemId === undefined) {
        console.error('Invalid item ID:', itemId);
        alert('Error: Invalid item ID. Please reload the page and try again.');
        return;
    }
    
    // Validate status
    if (!status) {
        console.error('Invalid status:', status);
        alert('Error: Please select a valid status');
        return;
    }
    
    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('status', status);
    
    // Debug log
    console.log('Sending update for item ID:', itemId, 'with status:', status);
    
    fetch('api/update_item_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `Server error: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Item status update result:', data);
        
        if (data.success) {
            // Check if all items have the same status and update order status in modal if needed
            const allStatusElements = document.querySelectorAll('#orderItems select');
            let allSame = true;
            let firstStatus = null;
            
            allStatusElements.forEach((select, index) => {
                if (index === 0) {
                    firstStatus = select.value;
                } else if (select.value !== firstStatus) {
                    allSame = false;
                }
            });
            
            if (allSame && firstStatus) {
                const orderStatusEl = document.getElementById('orderStatus');
                if (orderStatusEl) {
                    orderStatusEl.textContent = firstStatus;
                }
                
                // Update progress bar if available
                if (typeof updateProgressBar === 'function') {
                    updateProgressBar(firstStatus);
                }
            }
            
            // Update table view
            updateTableStatus();
        } else {
            alert('Failed to update item status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating item status: ' + error.message);
    });
}

function checkActiveFeedback() {
    console.log('Checking for completed feedback submissions');
    
    fetch('api/check_all_feedback.php')
        .then(response => response.json())
        .then(data => {
            if (data.feedback_orders && data.feedback_orders.length > 0) {
                console.log('Found orders with completed feedback:', data.feedback_orders);
                // Refresh table status to update the UI
                updateTableStatus();
            }
        })
        .catch(error => {
            console.error('Error checking feedback status:', error);
        });
}

// Add event listener for modal close
document.addEventListener('DOMContentLoaded', function() {
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('hidden.bs.modal', function (event) {
            if (currentOrder && currentOrder.order_status === 'Completed' && currentOrder.payment_status === 'Paid') {
                redirectToCustomerStatus(currentOrder.table_number);
            }
        });
    }
});