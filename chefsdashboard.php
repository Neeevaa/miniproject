<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit;
}
$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq"; 

// Connect to MySQL server and select the database
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef's Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/07264d6aa5.js" crossorigin="anonymous"></script>
    
    <style>
        #loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .alert {
        margin-bottom: 15px;
        border-radius: 5px;
    }
    
    .connection-warning {
        background-color: #fff3cd;
        color: #856404;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        text-align: center;
        display: none;
    }
    .table-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding: 20px;
    }

    .table-box {
        position: relative;
        height: 120px;
        background-color: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .table-box.occupied {
        background-color: #cfe2ff;
        border-color: #9ec5fe;
    }

    .table-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .table-number {
        font-size: 24px;
        font-weight: bold;
        color: #495057;
    }

    .table-status {
        font-size: 14px;
        margin-top: 5px;
        color: #6c757d;
    }

    .payment-status {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .payment-pending {
        background-color: #ffc107;
    }

    .payment-done {
        background-color: #28a745;
    }

    .progress-container {
        position: relative;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.6s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .status-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .status-buttons .btn {
        min-width: 90px;
    }

    .badge {
        font-size: 14px;
        padding: 8px 12px;
    }

    .badge.bg-warning {
        color: #000;
    }

    .badge.bg-success {
        color: #fff;
    }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Chef's Dashboard 👨‍🍳</h4>
        <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
    </div>

    <div id="connectionWarning" class="connection-warning">
    <strong>Warning:</strong> Connection issues detected. The dashboard may not display current information.
    <button class="btn btn-sm btn-warning ms-3" onclick="location.reload()">Refresh Page</button>
</div>
    <!-- Table Layout -->
    <div class="container-fluid mt-4">
        <div class="table-grid">
            <?php for($i = 1; $i <= 12; $i++): ?>
            <div class="table-box" id="table-<?php echo $i; ?>" onclick="viewTableOrders(<?php echo $i; ?>)">
                <div class="table-number"><?php echo $i; ?></div>
                <div class="table-status" id="table-status-<?php echo $i; ?>">Available</div>
                <div class="payment-status" id="payment-status-<?php echo $i; ?>"></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Add this at the start of your body content 
    <div class="container-fluid">
         Orders Table 
        <div class="table-responsive mt-4">
            <table class="table table-bordered" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                 Orders will be loaded here dynamically 
                </tbody>
            </table>
        </div>
    </div>-->

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Bill #:</strong> <span id="modalOrderId"></span></p>
                            <p><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                            <p><strong>Table:</strong> <span id="modalTableNumber"></span></p>
                            <p><strong>Time:</strong> <span id="modalOrderTime"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Status:</strong></p>
                            <div class="progress-container mb-3">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-warning" id="orderProgressBar" role="progressbar" style="width: 0%">
                                        <span id="orderStatusText">Pending</span>
                                    </div>
                                </div>
                            </div>
                            <div class="status-buttons mt-2">
                                <button class="btn btn-outline-warning btn-sm me-2" onclick="updateOrderStatus(currentOrderId, 'Pending')">
                                    Pending
                                </button>
                                <button class="btn btn-outline-info btn-sm me-2" onclick="updateOrderStatus(currentOrderId, 'Preparing')">
                                    Preparing
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="updateOrderStatus(currentOrderId, 'Ready')">
                                    Ready
                                </button>
                            </div>
                            <p class="mt-3">
                                <strong>Payment Status:</strong> 
                                <span id="modalPaymentStatus" class="badge bg-warning">Pending</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="modalOrderItems"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                    <td id="modalGrandTotal"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
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
    });

    // Helper function to safely access DOM elements
    function getElement(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`Element with ID "${id}" not found`);
        }
        return element;
    }

    // Use this helper in your other functions
    function loadOrderDetails(tableNumber) {
        console.log('Loading details for table:', tableNumber);
        
        const loader = getElement('loader');
        if (loader) loader.style.display = 'block';
        
        // Rest of the function remains the same but with checks for DOM elements
        // ...
    }

    function loadOrders() {
        console.log('Loading orders...');
        fetch('get_orders.php')
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
        
        fetch('get_table_status.php')
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
        fetch(`get_order_details.php?order_id=${orderId}`)
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
        
        fetch('update_order_status.php', {
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
        fetch(`check_feedback.php?order_id=${orderId}`)
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
        
        fetch(`get_table_orders.php?table=${tableNumber}`)
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

    // Update modal close handler
    document.getElementById('orderDetailsModal').addEventListener('hidden.bs.modal', function (event) {
        if (currentOrder && currentOrder.order_status === 'Completed' && currentOrder.payment_status === 'Paid') {
            redirectToCustomerStatus(currentOrder.table_number);
        }
    });

    // Add this function at the top of your JavaScript code
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

    // Fix the updateItemStatus function to validate the item_id
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
        
        fetch('update_item_status.php', {
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

    // Add this function to check and handle feedback status - can be called periodically
    function checkActiveFeedback() {
        console.log('Checking for completed feedback submissions');
        
        fetch('check_all_feedback.php')
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
    
    // Periodically check for feedback submissions
    document.addEventListener('DOMContentLoaded', function() {
        // Add to existing initialization
        setInterval(checkActiveFeedback, 60000); // Check every minute
    });
    </script>
</body>
</html>
