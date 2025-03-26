<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location: login.php");
    exit;
}
$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq"; 

// Connect to MySQL server and select the database
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} 

// Fetch orders function
function getOrders($conn, $status = null) {
    $query = "
        SELECT 
            o.order_id,
            o.customer_name,
            o.table_number,
            o.order_status,
            o.order_date,
            COUNT(oi.item_id) as item_count,
            SUM(oi.quantity * oi.price) as total_amount
        FROM tbl_orders o
        LEFT JOIN tbl_order_items oi ON o.order_id = oi.order_id
    ";
    
    if ($status) {
        $status = mysqli_real_escape_string($conn, $status);
        $query .= " WHERE o.order_status = '$status'";
    }
    
    $query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";
    
    $result = mysqli_query($conn, $query);
    $orders = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    
    return $orders;
}

// Get all orders or filter by status if provided
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$orders = getOrders($conn, $status_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Progress Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .status-card {
            border-radius: 10px;
            min-height: 120px;
            transition: all 0.3s;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .order-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .order-row:hover {
            background-color: rgba(254, 161, 22, 0.1);
        }
        
        .status-badge-Pending {
            background-color: #ffc107;
        }
        
        .status-badge-In-Progress {
            background-color: #0d6efd;
        }
        
        .status-badge-Completed {
            background-color: #198754;
        }
        
        .status-badge-Cancelled {
            background-color: #dc3545;
        }
        
        .actions-column button {
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .order-row:hover .actions-column button {
            opacity: 1;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        
        .progress-container {
            width: 100%;
            max-width: 300px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }
        
        .progress-bar {
            height: 20px;
            background-color: #fea116;
            border-radius: 8px;
            transition: width 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Order Progress Dashboard 🍳</h4>
        <div>
            <a href="kitchenadmin.php" class="btn btn-outline-light me-2">Back to Dashboard</a>
            <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="progress-container">
                <div class="progress-bar" id="progressBar" style="width: 0%">0%</div>
            </div>
            <p class="mt-3" id="updateStatus">Updating orders...</p>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Status Summary Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3">Order Status Summary</h5>
                <div class="row">
                    <?php
                    // Get counts for each status
                    $statuses = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
                    $colors = ['warning', 'primary', 'success', 'danger'];
                    $icons = ['clock', 'gear-fill', 'check-circle-fill', 'x-circle-fill'];
                    
                    foreach ($statuses as $index => $status) {
                        $query = "SELECT COUNT(*) as count FROM tbl_orders WHERE order_status = '$status'";
                        $result = mysqli_query($conn, $query);
                        $count = 0;
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            $count = mysqli_fetch_assoc($result)['count'];
                        }
                        
                        echo '
                        <div class="col-md-3 mb-3">
                            <div class="status-card bg-' . $colors[$index] . ' bg-opacity-10 p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">' . $status . '</h6>
                                    <i class="bi bi-' . $icons[$index] . ' text-' . $colors[$index] . '"></i>
                                </div>
                                <h2 class="mt-2 mb-0 fw-bold text-' . $colors[$index] . '">' . $count . '</h2>
                                <a href="?status=' . $status . '" class="mt-auto text-' . $colors[$index] . ' small">View Orders</a>
                            </div>
                        </div>';
                    }
                    ?>
                    <div class="col-md-3 mb-3">
                        <div class="status-card bg-dark bg-opacity-10 p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Total Orders</h6>
                                <i class="bi bi-list-check"></i>
                            </div>
                            <?php
                            $query = "SELECT COUNT(*) as count FROM tbl_orders";
                            $result = mysqli_query($conn, $query);
                            $total_count = 0;
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                $total_count = mysqli_fetch_assoc($result)['count'];
                            }
                            ?>
                            <h2 class="mt-2 mb-0 fw-bold"><?php echo $total_count; ?></h2>
                            <a href="order-progress.php" class="mt-auto text-dark small">View All</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter and Actions Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 me-3">Order Management</h5>
                    <?php if ($status_filter): ?>
                        <span class="badge status-badge-<?php echo str_replace(' ', '-', $status_filter); ?> py-2 px-3">
                            Showing: <?php echo $status_filter; ?> Orders
                        </span>
                        <a href="order-progress.php" class="btn btn-sm btn-link">Clear Filter</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button id="updateAllBtn" class="btn btn-success me-2">Update All Selected</button>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Bulk Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item bulk-status-btn" data-status="Pending">Mark as Pending</button></li>
                        <li><button class="dropdown-item bulk-status-btn" data-status="In Progress">Mark as In Progress</button></li>
                        <li><button class="dropdown-item bulk-status-btn" data-status="Completed">Mark as Completed</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item bulk-status-btn text-danger" data-status="Cancelled">Mark as Cancelled</button></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="ordersTable">
                <thead class="table-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $status_class = 'status-badge-' . str_replace(' ', '-', $order['order_status']);
                            $order_time = strtotime($order['order_date']);
                            $waiting_time = round((time() - $order_time) / 60);
                            $time_class = $waiting_time > 30 ? 'text-danger' : ($waiting_time > 15 ? 'text-warning' : 'text-success');
                            ?>
                            <tr class="order-row" data-order-id="<?php echo $order['order_id']; ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input order-checkbox" data-order-id="<?php echo $order['order_id']; ?>">
                                </td>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>Table <?php echo $order['table_number']; ?></td>
                                <td><?php echo $order['item_count']; ?> items</td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <?php echo date('Y-m-d H:i', $order_time); ?>
                                    <div class="<?php echo $time_class; ?> small">
                                        <?php echo $waiting_time; ?> min ago
                                    </div>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm status-select" data-order-id="<?php echo $order['order_id']; ?>">
                                        <option value="Pending" <?php echo $order['order_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $order['order_status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Completed" <?php echo $order['order_status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $order['order_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td class="actions-column">
                                    <button class="btn btn-sm btn-outline-primary view-order-btn" data-order-id="<?php echo $order['order_id']; ?>">
                                        View
                                    </button>
                                    <button class="btn btn-sm btn-outline-success update-status-btn" data-order-id="<?php echo $order['order_id']; ?>">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <?php if ($status_filter): ?>
                                    No orders found with status: <?php echo $status_filter; ?>
                                <?php else: ?>
                                    No orders found
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #fea116; color: white;">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="modalUpdateBtn">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    
    <!-- Include JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all checkbox
            const selectAllCheckbox = document.getElementById('selectAll');
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');
            
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                orderCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
            
            // Individual checkboxes
            orderCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(orderCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
            
            // Status selects
            const statusSelects = document.querySelectorAll('.status-select');
            statusSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    row.classList.add('table-warning');
                });
            });
            
            // Update status buttons
            const updateStatusBtns = document.querySelectorAll('.update-status-btn');
            updateStatusBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    const statusSelect = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                    const status = statusSelect.value;
                    
                    updateOrderStatus(orderId, status);
                });
            });
            
            // View order details
            const viewOrderBtns = document.querySelectorAll('.view-order-btn');
            viewOrderBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    openOrderDetailsModal(orderId);
                });
            });
            
            // Update all selected button
            const updateAllBtn = document.getElementById('updateAllBtn');
            updateAllBtn.addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one order to update.');
                    return;
                }
                
                const orderUpdates = {};
                let hasChanges = false;
                
                checkedBoxes.forEach(checkbox => {
                    const orderId = checkbox.getAttribute('data-order-id');
                    const statusSelect = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                    const newStatus = statusSelect.value;
                    const originalStatus = statusSelect.querySelector(`option[value="${newStatus}"]`).hasAttribute('selected');
                    
                    if (!originalStatus) {
                        orderUpdates[orderId] = newStatus;
                        hasChanges = true;
                    }
                });
                
                if (hasChanges) {
                    updateMultipleOrders(orderUpdates);
                } else {
                    alert('No status changes detected for the selected orders.');
                }
            });
            
            // Bulk status buttons
            const bulkStatusBtns = document.querySelectorAll('.bulk-status-btn');
            bulkStatusBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                    
                    if (checkedBoxes.length === 0) {
                        alert('Please select at least one order to update.');
                        return;
                    }
                    
                    if (confirm(`Are you sure you want to mark ${checkedBoxes.length} order(s) as "${status}"?`)) {
                        const orderUpdates = {};
                        
                        checkedBoxes.forEach(checkbox => {
                            const orderId = checkbox.getAttribute('data-order-id');
                            orderUpdates[orderId] = status;
                            
                            // Update the select element to match
                            const statusSelect = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                            statusSelect.value = status;
                        });
                        
                        updateMultipleOrders(orderUpdates);
                    }
                });
            });
            
            // Modal update button
            const modalUpdateBtn = document.getElementById('modalUpdateBtn');
            modalUpdateBtn.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const statusSelect = document.querySelector(`#orderDetailsContent select.order-status-select`);
                
                if (statusSelect) {
                    const status = statusSelect.value;
                    updateOrderStatus(orderId, status);
                }
            });
            
            // Function to update a single order status
            function updateOrderStatus(orderId, status) {
                const loadingOverlay = document.getElementById('loadingOverlay');
                const progressBar = document.getElementById('progressBar');
                const updateStatus = document.getElementById('updateStatus');
                
                loadingOverlay.style.display = 'flex';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                updateStatus.textContent = `Updating order #${orderId}...`;
                
                // Simulate progress
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 5;
                    progressBar.style.width = `${Math.min(progress, 90)}%`;
                    progressBar.textContent = `${Math.min(progress, 90)}%`;
                    
                    if (progress >= 90) {
                        clearInterval(interval);
                    }
                }, 50);
                
                fetch('update_all_orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orders: {
                            [orderId]: status
                        }
                    })
                })
                .then(response => response.json())
                .then(data => {
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    updateStatus.textContent = data.message;
                    
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                        
                        if (data.success) {
                            // Update the page
                            location.reload();
                        } else {
                            alert(data.message || 'An error occurred while updating the order.');
                        }
                    }, 1000);
                })
                .catch(error => {
                    clearInterval(interval);
                    loadingOverlay.style.display = 'none';
                    console.error('Error:', error);
                    alert('An error occurred while updating the order. Please try again.');
                });
            }
            
            // Function to update multiple orders at once
            function updateMultipleOrders(orderUpdates) {
                const loadingOverlay = document.getElementById('loadingOverlay');
                const progressBar = document.getElementById('progressBar');
                const updateStatus = document.getElementById('updateStatus');
                const orderCount = Object.keys(orderUpdates).length;
                
                loadingOverlay.style.display = 'flex';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                updateStatus.textContent = `Updating ${orderCount} orders...`;
                
                // Simulate progress
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 2;
                    progressBar.style.width = `${Math.min(progress, 90)}%`;
                    progressBar.textContent = `${Math.min(progress, 90)}%`;
                    
                    if (progress >= 90) {
                        clearInterval(interval);
                    }
                }, 30);
                
                fetch('update_all_orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orders: orderUpdates
                    })
                })
                .then(response => response.json())
                .then(data => {
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    updateStatus.textContent = data.message;
                    
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                        
                        if (data.success) {
                            // Update the page
                            location.reload();
                        } else {
                            alert(data.message || 'An error occurred while updating orders.');
                        }
                    }, 1000);
                })
                .catch(error => {
                    clearInterval(interval);
                    loadingOverlay.style.display = 'none';
                    console.error('Error:', error);
                    alert('An error occurred while updating orders. Please try again.');
                });
            }
            
            // Function to open order details modal
            function openOrderDetailsModal(orderId) {
                const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                const modalContent = document.getElementById('orderDetailsContent');
                const modalUpdateBtn = document.getElementById('modalUpdateBtn');
                
                // Set the order ID to the update button
                modalUpdateBtn.setAttribute('data-order-id', orderId);
                
                // Show loading state
                modalContent.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order #${orderId} details...</p>
                    </div>
                `;
                
                modal.show();
                
                // Fetch order details
                fetch(`get_order_details.php?order_id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const order = data.order;
                            const items = data.items;
                            
                            // Calculate total
                            let total = 0;
                            items.forEach(item => {
                                total += (item.quantity * item.price);
                            });
                            
                            // Build modal content
                            let content = `
                                <div class="order-info mb-3">
                                    <div class="d-flex justify-content-between">
                                        <p><strong>Order #${order.order_id}</strong></p>
                                        <p><strong>Table:</strong> ${order.table_number}</p>
                                    </div>
                                    <p><strong>Customer:</strong> ${order.customer_name}</p>
                                    <p><strong>Time:</strong> ${new Date(order.order_date).toLocaleString()}</p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <select class="form-select form-select-sm mt-1 order-status-select">
                                            <option value="Pending" ${order.order_status === 'Pending' ? 'selected' : ''}>Pending</option>
                                            <option value="In Progress" ${order.order_status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                                            <option value="Completed" ${order.order_status === 'Completed' ? 'selected' : ''}>Completed</option>
                                            <option value="Cancelled" ${order.order_status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                    </p>
                                </div>
                                <hr>
                                <h6>Items</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            
                            items.forEach(item => {
                                const itemTotal = item.quantity * item.price;
                                content += `
                                    <tr>
                                        <td>${item.item_name}</td>
                                        <td class="text-center">${item.quantity}</td>
                                        <td class="text-end">₹${item.price.toFixed(2)}</td>
                                        <td class="text-end">₹${itemTotal.toFixed(2)}</td>
                                    </tr>
                                `;
                            });
                            
                            content += `
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total:</th>
                                                <th class="text-end">₹${total.toFixed(2)}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <strong>Notes:</strong>
                                    <p class="mb-0">${order.notes ? order.notes : 'No special instructions'}</p>
                                </div>
                            `;
                            
                            modalContent.innerHTML = content;
                        } else {
                            modalContent.innerHTML = `
                                <div class="alert alert-danger">
                                    ${data.message || 'Failed to load order details.'}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        modalContent.innerHTML = `
                            <div class="alert alert-danger">
                                An error occurred while loading order details. Please try again.
                            </div>
                        `;
                    });
            }
        });
    </script>
</body>
</html>