<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to handle image paths when displaying images
function getImagePath($imageName) {
    // If image name is empty or null, return default image
    if (empty($imageName)) {
        return 'img/default-food.jpg';
    }
    
    // Remove any 'images/' prefix if it exists (to clean up any existing data)
    if (strpos($imageName, 'images/') === 0) {
        $imageName = substr($imageName, 7); // Remove 'images/' prefix
    }
    
    // Construct the full path by adding 'images/' prefix
    $fullPath = 'images/' . $imageName;
    
    // Check if the file exists
    if (file_exists($fullPath)) {
        return $fullPath;
    } else {
        // Log the missing file
        error_log("Image not found: " . $fullPath);
        return 'img/default-food.jpg';
    }
}

// Global variable for currentOrderId (PHP variable)
$currentOrderId = null;
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/07264d6aa5.js" crossorigin="anonymous"></script>
    <script>
    // Global variable to track current order ID
    let currentOrderId = null;

    // Function to view table orders with improved error handling
    function viewTableOrders(tableNumber) {
        console.log('Loading orders for table:', tableNumber);
        if (!tableNumber) {
            console.error('No table number provided');
            alert('Error: Table number is required');
            return;
        }
        
        // Show loading indicator
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'block';
        
        // Add timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        fetch(`get_table_orders.php?table=${tableNumber}&_=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 500) {
                        throw new Error('Server error: The request failed. Please try again or contact support.');
                    }
                    return response.json().then(data => {
                        throw new Error(data.error || `Server error: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Table order data:', data);
                
                // Check if there's valid order data
                if (!data || !data.order_id) {
                    throw new Error(`No active order found for table ${tableNumber}. Create an order first.`);
                }
                
                // Set the currentOrderId without redeclaring
                window.currentOrderId = data.order_id;
                console.log('Set currentOrderId to:', window.currentOrderId);
                
                // Ensure the modal element exists first
                let orderModalElement = document.getElementById('orderModal');
                if (!orderModalElement) {
                    console.log('Creating order modal element');
                    orderModalElement = document.createElement('div');
                    orderModalElement.id = 'orderModal';
                    orderModalElement.className = 'modal fade';
                    orderModalElement.setAttribute('tabindex', '-1');
                    orderModalElement.setAttribute('aria-labelledby', 'orderModalLabel');
                    orderModalElement.setAttribute('aria-hidden', 'true');
                    
                    orderModalElement.innerHTML = `
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="orderModalLabel">Table ${tableNumber} - Order #${data.order_id}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong>Status:</strong> <span id="orderStatus">${data.status || data.order_status || 'Unknown'}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Date:</strong> <span id="orderDate">${new Date(data.timestamp || data.order_date).toLocaleString()}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Customer:</strong> <span id="customerName">${data.customer_name || 'Guest'}</span>
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div id="orderProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <p id="orderStatusText">Status: ${data.status || data.order_status || 'Unknown'}</p>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orderItems">
                                            <!-- Items will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <div id="orderTotal" class="order-total fw-bold">₹0.00</div>
                                    <div class="order-status-update mt-3 d-flex align-items-center justify-content-center">
                                        <select id="orderStatusDropdown" class="form-select me-2" style="max-width: 200px;">
                                            <option value="Pending">Pending</option>
                                            <option value="Preparing">Preparing</option>
                                            <option value="Ready">Ready</option>
                                            <option value="Plating">Plating</option>
                                            <option value="Served">Served</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                        <button type="button" class="btn btn-primary" style="background-color: #fea116; border-color: #fea116;" onclick="updateOrderStatusFromDropdown()">Update </button>
                                    </div>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(orderModalElement);
                } else {
                    // Update modal title with table and order info
                    const modalLabelElement = document.getElementById('orderModalLabel');
                    if (modalLabelElement) {
                        modalLabelElement.textContent = `Table ${tableNumber} - Order #${data.order_id}`;
                    }
                    
                    // Update order details - with null checks
                    const orderStatus = document.getElementById('orderStatus');
                    if (orderStatus) orderStatus.textContent = data.status || data.order_status || 'Unknown';
                    
                    const orderDate = document.getElementById('orderDate');
                    if (orderDate) orderDate.textContent = new Date(data.timestamp || data.order_date).toLocaleString();
                    
                    const customerName = document.getElementById('customerName');
                    if (customerName) customerName.textContent = data.customer_name || 'Guest';
                    
                    const orderStatusText = document.getElementById('orderStatusText');
                    if (orderStatusText) orderStatusText.textContent = `Status: ${data.status || data.order_status || 'Unknown'}`;
                }
                
                // Now the orderItems element should definitely exist - get a reference
                const itemsTable = document.getElementById('orderItems');
                if (!itemsTable) {
                    console.error('Order items table element still not found after creation attempt');
                    if (loader) loader.style.display = 'none';
                    return;
                }
                
                // Clear existing items
                itemsTable.innerHTML = '';
                
                let total = 0;
                
                if (Array.isArray(data.items) && data.items.length > 0) {
                    data.items.forEach(item => {
                        const price = parseFloat(item.price) || 0;
                        const quantity = parseInt(item.quantity) || 0;
                        const itemTotal = price * quantity;
                        total += itemTotal;
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.food_name || 'Unknown Item'}</td>
                            <td>${quantity}</td>
                            <td>₹${price.toFixed(2)}</td>
                            <td>₹${itemTotal.toFixed(2)}</td>
                            <td>
                                <select class="form-select form-select-sm" onchange="updateItemStatus(${item.id || 0}, this.value)">
                                    <option value="Pending" ${item.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                    <option value="Preparing" ${item.status === 'Preparing' ? 'selected' : ''}>Preparing</option>
                                    <option value="Ready" ${item.status === 'Ready' ? 'selected' : ''}>Ready</option>
                                    <option value="Plating" ${item.status === 'Plating' ? 'selected' : ''}>Plating</option>
                                    <option value="Served" ${item.status === 'Served' ? 'selected' : ''}>Served</option>
                                    <option value="Cancelled" ${item.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </td>
                        `;
                        itemsTable.appendChild(row);
                    });
                } else {
                    console.warn('No items found in order data');
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="5" class="text-center">No items found in this order</td>';
                    itemsTable.appendChild(row);
                }
                
                // Update the order total
                const orderTotalElement = document.getElementById('orderTotal');
                if (orderTotalElement) {
                    orderTotalElement.textContent = `₹${total.toFixed(2)}`;
                }
                
                // Set the dropdown to match current status
                const orderStatusDropdown = document.getElementById('orderStatusDropdown');
                if (orderStatusDropdown) {
                    const currentStatus = data.status || data.order_status || 'Pending';
                    orderStatusDropdown.value = currentStatus;
                }
                
                // Update progress bar based on status
                if (typeof updateProgressBar === 'function') {
                    updateProgressBar(data.status || data.order_status);
                }
                
                // Show the modal with fallback options
                try {
                    if (typeof bootstrap !== 'undefined') {
                        const orderModal = new bootstrap.Modal(orderModalElement);
                        orderModal.show();
                    } else {
                        // Fallback if Bootstrap is not available
                        orderModalElement.style.display = 'block';
                        orderModalElement.classList.add('show');
                        document.body.classList.add('modal-open');
                        
                        // Create a backdrop
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                } catch (e) {
                    console.error('Error showing modal:', e);
                    // Simple fallback
                    orderModalElement.style.display = 'block';
                    orderModalElement.classList.add('show');
                }
                
                if (loader) loader.style.display = 'none';
                
                // Update table colors after loading order data
                setTimeout(updateTableColors, 500);
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                alert('Error loading order details: ' + error.message);
                if (loader) loader.style.display = 'none';
            });
    }

    // Function to update order status
    function updateOrderStatus(status) {
        console.log('Updating order status to:', status);
        
        if (!window.currentOrderId) {
            console.error('No current order ID set');
            alert('Error: No order selected');
            return;
        }
        
        console.log('Using order ID:', window.currentOrderId);
        
        const formData = new FormData();
        formData.append('order_id', window.currentOrderId);
        formData.append('status', status);

        // Debug: Log the form data
        console.log('Sending data:', {
            order_id: window.currentOrderId,
            status: status
        });

        fetch('update_order_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Update response:', data);
            
            if (data.success) {
                // Update the UI
                const orderStatus = document.getElementById('orderStatus');
                if (orderStatus) orderStatus.textContent = status;
                
                updateProgressBar(status);
                
                // Refresh table status
                updateTableStatus();
                
                // Notify user
                alert('Order status updated successfully!');
            } else {
                alert('Error: ' + (data.message || 'Failed to update order status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status: ' + error.message);
        });
    }

    // Function to update item status
    function updateItemStatus(itemId, status) {
        console.log('Updating item status:', itemId, status);
        
        if (!itemId) {
            console.error('No item ID provided');
            alert('Error: Item ID is required');
            return;
        }
        
        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('status', status);

        // Debug: Log the form data
        console.log('Sending data:', {
            item_id: itemId,
            status: status
        });

        fetch('update_item_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Update response:', data);
            
            if (data.success) {
                // Check if all items have the same status
                const allSelects = document.querySelectorAll('#orderItems select');
                let allSame = true;
                
                allSelects.forEach(select => {
                    if (select.value !== status) {
                        allSame = false;
                    }
                });
                
                if (allSame) {
                    // If all items have the same status, update the order status display
                    const orderStatus = document.getElementById('orderStatus');
                    if (orderStatus) orderStatus.textContent = status;
                    
                    updateProgressBar(status);
                }
                
                // Refresh table status in case changes affected display
                updateTableStatus();
            } else {
                alert('Error: ' + (data.message || 'Failed to update item status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating item status: ' + error.message);
        });
    }

    // Function to update progress bar based on status
    function updateProgressBar(status) {
        console.log('Updating progress bar for status:', status);
        
        const progressBar = document.getElementById('orderProgressBar');
        const statusText = document.getElementById('orderStatusText');
        
        if (!progressBar || !statusText) {
            console.warn('Progress bar elements not found');
            return;
        }
        
        switch(status) {
            case 'Pending':
                progressBar.style.width = '20%';
                progressBar.className = 'progress-bar bg-warning';
                statusText.textContent = 'Pending';
                break;
            case 'Preparing':
                progressBar.style.width = '40%';
                progressBar.className = 'progress-bar bg-info';
                statusText.textContent = 'Preparing';
                break;
            case 'Ready':
                progressBar.style.width = '60%';
                progressBar.className = 'progress-bar bg-secondary';
                statusText.textContent = 'Ready';
                break;
            case 'Plating':
                progressBar.style.width = '80%';
                progressBar.className = 'progress-bar bg-primary';
                statusText.textContent = 'Plating';
                break;
            case 'Served':
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-success';
                statusText.textContent = 'Served';
                break;
            case 'Cancelled':
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-danger';
                statusText.textContent = 'Cancelled';
                break;
            default:
                progressBar.style.width = '0%';
                progressBar.className = 'progress-bar';
                statusText.textContent = 'Unknown Status';
        }
    }

    // Updated function to use window.currentOrderId to avoid redeclaration issues
    function updateOrderStatusFromDropdown() {
        const statusDropdown = document.getElementById('orderStatusDropdown');
        if (!statusDropdown) {
            console.error('Status dropdown not found');
            alert('Error: Status dropdown not found');
            return;
        }
        
        const status = statusDropdown.value;
        console.log('Updating order status to:', status);
        
        if (!window.currentOrderId) {
            console.error('No current order ID set');
            alert('Error: No order selected. Please select an order first.');
            return;
        }
        
        const formData = new FormData();
        formData.append('order_id', window.currentOrderId);
        formData.append('status', status);
        
        // Show loader if available
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'block';
        
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
            console.log('Update response:', data);
            
            if (loader) loader.style.display = 'none';
            
            if (data.success) {
                // Update UI elements
                const orderStatus = document.getElementById('orderStatus');
                if (orderStatus) orderStatus.textContent = status;
                
                const orderStatusText = document.getElementById('orderStatusText');
                if (orderStatusText) orderStatusText.textContent = `Status: ${status}`;
                
                if (typeof updateProgressBar === 'function') {
                    updateProgressBar(status);
                }
                
                if (typeof updateTableStatus === 'function') {
                    updateTableStatus();
                }
                
                // Update table colors after status change
                setTimeout(updateTableColors, 500);
                
                alert('Order status updated successfully!');
            } else {
                alert('Error: ' + (data.message || 'Failed to update order status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (loader) loader.style.display = 'none';
            alert('Error updating order status: ' + error.message);
        });
    }

    // Improved function to update table colors based on order status
    function updateTableColors() {
        console.log('Updating table colors based on active orders');
        
        // Show loading indicator if available
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'block';
        
        // Add timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Fetch active tables data
        fetch(`get_active_tables.php?_=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Active tables data received:', data);
                
                // Reset all tables to default color first
                document.querySelectorAll('[id^="table-"]').forEach(tableElement => {
                    tableElement.classList.remove('bg-success', 'text-white');
                    tableElement.classList.add('bg-light');
                    
                    // Also reset table status text
                    const statusElement = tableElement.querySelector('.table-status');
                    if (statusElement) {
                        statusElement.textContent = 'Available';
                    }
                });
                
                // If we have active tables, highlight them
                if (data.success && Array.isArray(data.tables) && data.tables.length > 0) {
                    data.tables.forEach(table => {
                        // Extract table number and status
                        const tableNumber = typeof table === 'object' ? table.table_number : table;
                        const tableStatus = typeof table === 'object' ? table.status : null;
                        
                        // Find the table element
                        const tableElement = document.getElementById(`table-${tableNumber}`);
                        
                        // Skip tables that are marked as 'Served'
                        if (tableStatus === 'Served') {
                            console.log(`Table ${tableNumber} has 'Served' status, not highlighting`);
                            return;
                        }
                        
                        if (tableElement) {
                            // Highlight the table
                            tableElement.classList.remove('bg-light');
                            tableElement.classList.add('bg-success', 'text-white');
                            
                            // Update status text if available
                            const statusElement = tableElement.querySelector('.table-status');
                            if (statusElement && tableStatus) {
                                statusElement.textContent = `Active - ${tableStatus}`;
                            } else if (statusElement) {
                                statusElement.textContent = 'Active Order';
                            }
                            
                            console.log(`Highlighted table ${tableNumber} with status: ${tableStatus || 'Active'}`);
                        } else {
                            console.warn(`Table element for table ${tableNumber} not found`);
                        }
                    });
                } else {
                    console.log('No active tables to highlight');
                }
                
                if (loader) loader.style.display = 'none';
            })
            .catch(error => {
                console.error('Error updating table colors:', error);
                if (loader) loader.style.display = 'none';
                
                // Show error message to user
                alert('Error updating table status. Please refresh the page.');
            });
    }

    // Function to update the table status in the UI
    function updateTableStatus() {
        console.log('Updating table status in UI');
        
        // Show loading indicator if available
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'block';
        
        // Add timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Fetch current table statuses
        fetch(`get_table_statuses.php?_=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Table status data:', data);
                
                if (data.success && Array.isArray(data.tables)) {
                    // Update each table's status text in the UI
                    data.tables.forEach(table => {
                        const tableStatusElement = document.getElementById(`table-status-${table.number}`);
                        if (tableStatusElement) {
                            tableStatusElement.textContent = table.status || 'Available';
                            
                            // Update the class based on status
                            tableStatusElement.className = 'table-status';
                            if (table.status === 'Occupied') {
                                tableStatusElement.classList.add('text-danger');
                            } else if (table.status === 'Reserved') {
                                tableStatusElement.classList.add('text-warning');
                            }
                        }
                    });
                }
                
                // Also update the table colors
                updateTableColors();
                
                if (loader) loader.style.display = 'none';
            })
            .catch(error => {
                console.error('Error fetching table statuses:', error);
                
                // Fallback: Just update the colors based on the current data
                try {
                    updateTableColors();
                } catch (e) {
                    console.error('Error in fallback updateTableColors call:', e);
                }
                
                if (loader) loader.style.display = 'none';
            });
    }

    // Simpler fallback version if there is no API endpoint available
    function updateTableStatus_fallback() {
        console.log('Using fallback table status update method');
        
        // This is a simple version that just refreshes the table colors
        // without requiring a new API endpoint
        try {
            // Just trigger a refresh of the table colors
            updateTableColors();
        } catch (e) {
            console.error('Error in fallback table status update:', e);
        }
    }

    // Check which version to use - if the endpoint doesn't exist, use the fallback
    function checkAndUpdateTableStatus() {
        // Try to use the full version first
        fetch('get_table_statuses.php', { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    // The endpoint exists, use the full version
                    updateTableStatus();
                } else {
                    // Endpoint not found, use the fallback
                    updateTableStatus_fallback();
                }
            })
            .catch(error => {
                console.warn('Error checking table status endpoint:', error);
                // Use the fallback on any error
                updateTableStatus_fallback();
            });
    }

    // Update the references to updateTableStatus to use the checked version
    document.addEventListener('DOMContentLoaded', function() {
        // Replace direct calls with the checked version
        window.updateTableStatus = checkAndUpdateTableStatus;
        
        console.log('DOM fully loaded, initializing table status');
        
        // Rest of your initialization code...
    });

    // Define these functions in the global window object to ensure they're accessible from HTML onclick attributes
    window.showBookings = function() {
        // Hide other views
        document.getElementById('ordersView').style.display = 'none';
        document.getElementById('menuView').style.display = 'none';
        
        // Show bookings view
        const bookingsView = document.getElementById('bookingsView');
        bookingsView.style.display = 'block';
        
        // Load bookings using get_booking_details.php instead of get_all_bookings.php
        fetch('get_booking_details.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const tbody = document.getElementById('bookingsTableBody');
                tbody.innerHTML = '';
                
                if (data.success && data.bookings) {
                    data.bookings.forEach(booking => {
                        const row = document.createElement('tr');
                        
                        row.innerHTML = `
                            <td>${booking.booking_id}</td>
                            <td>${booking.customer_name}</td>
                            <td>${booking.table_number}</td>
                            <td>${booking.email}</td>
                            <td>${booking.booking_date} ${booking.booking_time}</td>
                            <td>${booking.guest_count}</td>
                            <td>${booking.special_requests || '-'}</td>
                            <td>${booking.special_option || '-'}</td>
                            <td>
                                <span class="badge bg-${booking.status === 'Confirmed' ? 'success' : 
                                                        booking.status === 'Cancelled' ? 'danger' : 'warning'}">
                                    ${booking.status}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="updateBookingStatus(${booking.booking_id}, 'Confirmed')">
                                    Confirm
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="updateBookingStatus(${booking.booking_id}, 'Cancelled')">
                                    Cancel
                                </button>
                                <button class="btn btn-sm btn-info" onclick="notifyCustomer(${booking.booking_id})">
                                    Notify
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading bookings:', error);
                const tbody = document.getElementById('bookingsTableBody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center text-danger">
                            Error loading bookings. Please try again.
                        </td>
                    </tr>
                `;
            });
        
        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelector('.nav-link[onclick="showBookings()"]').classList.add('active');
    };

    window.showMenu = function() {
        console.log("Showing menu view");
        
        try {
            // Hide other views and show menu view
            const ordersView = document.getElementById('ordersView');
            const bookingsView = document.getElementById('bookingsView');
            const menuView = document.getElementById('menuView');
            
            if (ordersView) ordersView.style.display = 'none';
            if (bookingsView) bookingsView.style.display = 'none';
            if (menuView) menuView.style.display = 'block';
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            const menuLink = document.querySelector('.nav-link[onclick="showMenu()"]');
            if (menuLink) menuLink.classList.add('active');
            
            // Fetch and display current menu items
            fetch('get_menu_items.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const menuGrid = document.getElementById('menuItemsGrid');
                    if (!menuGrid) {
                        console.error('Menu grid element not found');
                        return;
                    }
                    
                    // Clear existing items
                    menuGrid.innerHTML = '';
                    
                    // Add each menu item to the grid (if items exist)
                    if (data.items && Array.isArray(data.items)) {
                        data.items.forEach(item => {
                            const itemElement = document.createElement('div');
                            itemElement.className = 'menu-item';
                            itemElement.onclick = () => showMenuItemDetails(item);
                            
                            // Get image path
                            const imagePath = item.itemimage ? 
                                (item.itemimage.startsWith('images/') ? item.itemimage : 'images/' + item.itemimage) : 
                                'img/default-food.jpg';
                            
                            itemElement.innerHTML = `
                                <img src="${imagePath}" 
                                     onerror="this.src='img/default-food.jpg'" 
                                     alt="${item.itemname}" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                                <h5>${item.itemname}</h5>
                                <p class="category">${item.category}</p>
                                <p class="price">₹${item.price}</p>
                            `;
                            
                            menuGrid.appendChild(itemElement);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching menu items:', error);
                    alert('Error loading menu items. Please try again.');
                });
        } catch (e) {
            console.error('Error in showMenu function:', e);
        }
    };

    // Keep your existing showOrders function global as well
    window.showOrders = function() {
        console.log("Showing orders view");
        // Show orders view, hide other views
        const ordersView = document.getElementById('ordersView');
        const bookingsView = document.getElementById('bookingsView');
        const menuView = document.getElementById('menuView');
        
        if (ordersView) ordersView.style.display = 'block';
        if (bookingsView) bookingsView.style.display = 'none';
        if (menuView) menuView.style.display = 'none';
        
        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        const ordersLink = document.querySelector('.nav-link[onclick="showOrders()"]');
        if (ordersLink) ordersLink.classList.add('active');
    };

    // Add this function to handle customer notification
    function notifyCustomer(bookingId) {
        // Get the button that was clicked
        const button = event.target;
        const originalText = button.textContent;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        
        fetch('notify_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success state
                button.classList.remove('btn-info');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="fas fa-check"></i> Sent';
                
                // Show toast notification
                showToast('Success', 'Booking confirmation email sent successfully', 'success');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.disabled = false;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-info');
                    button.textContent = originalText;
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to send notification');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button to error state
            button.disabled = false;
            button.classList.remove('btn-info');
            button.classList.add('btn-danger');
            button.textContent = 'Failed';
            
            // Show error toast
            showToast('Error', 'Failed to send notification. Please try again.', 'error');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.classList.remove('btn-danger');
                button.classList.add('btn-info');
                button.textContent = originalText;
            }, 3000);
        });
    }

    // Add this helper function for toast notifications
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast show bg-${type === 'success' ? 'success' : 'danger'} text-white`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Remove toast after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
        return container;
    }

    // The rest of your existing JavaScript...
    function showPayments() {
            // Hide other views
            document.getElementById('ordersView').style.display = 'none';
            document.getElementById('bookingsView').style.display = 'none';
            document.getElementById('menuView').style.display = 'none';
            
            // Show payments view
            document.getElementById('payments').style.display = 'block';
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            document.querySelector('.nav-link[onclick="showPayments()"]').classList.add('active');
        }

        // ... rest of your script code ...
    </script>
    <style>
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

    .table-box.bg-success {
        --bs-bg-opacity: 1;
        background-color:rgb(204, 229, 255) !important;
        border-color:rgb(128, 190, 255);
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
        background-color:transparent ; 
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

    .sidebar {
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .nav-link {
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .nav-link:hover, .nav-link.active {
        background-color: rgba(255,255,255,0.1);
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .menu-item {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .menu-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .menu-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .menu-item h5 {
        margin: 10px 0;
        color: #333;
    }

    .menu-item .category {
        color: #666;
        font-size: 14px;
        margin: 5px 0;
    }

    .menu-item .price {
        color: #0A2558;
        font-weight: bold;
        margin: 5px 0;
    }

    .modal-footer .btn {
        margin-left: 10px;
    }

    #editItemBtn {
        background-color: #007bff;
        border-color: #007bff;
    }

    #deleteItemBtn {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    #editItemBtn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    #deleteItemBtn:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    .booked {
        background-color: #ffd700 !important;
        border-color: #ffc107 !important;
    }

    .bookings-view .table-box {
        position: relative;
    }

    .bookings-view .booking-time {
        position: absolute;
        bottom: 10px;
        font-size: 12px;
        color: #666;
    }

    .table th {
        white-space: nowrap;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        margin: 0 2px;
    }

    .badge {
        padding: 0.5em 0.75em;
    }
    </style>
</head>
<body>
    <!-- Add this right after the opening <body> tag -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-dark text-white" style="min-width: 250px; min-height: 100vh;">
            <div class="p-3">
                <h4 class="mb-3">Kitchen Admin Dashboard 👨‍🍳</h4>
                <div class="nav flex-column">
                    <a href="#" class="nav-link text-white active" onclick="showOrders()">📦 Orders</a>
                    <a href="#" class="nav-link text-white" onclick="showBookings()">📅 Table Reservations</a>
                    <a href="#" class="nav-link text-white" onclick="showMenu()">📜 Menu</a>
                    <a href="#ordersView" class="nav-link text-white" onclick="showPayments()"><i class="fas fa-money-bill-wave me-2"></i> Payments</a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Orders View -->
            <div id="ordersView">
                <!-- Header -->
                <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
                    <h4 class="text-white mb-0">      </h4>
                    <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
                </div>

                <!-- Table Layout -->
                <div class="container-fluid mt-4">
                    <h4 class="mb-3">Table Layout</h4>
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
<!-- Add this inside the ordersView div after the table layout section 
<div class="container-fluid mt-4">
    <h4 class="mb-3">Active Orders</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Order ID</th>
                    <th>Table</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Time</th>
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
                                        <p><strong>Order ID:</strong> <span id="modalOrderId"></span></p>
                                        <p><strong>Table:</strong> <span id="modalTableNumber"></span></p>
                                        <p><strong>Time:</strong> <span id="modalOrderTime"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Order Status:</strong></p>
                                        <div class="progress-container mb-3">
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar" id="orderProgressBar" role="progressbar" style="width: 0%">
                                                    <span id="orderStatusText">Pending</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="status-buttons mt-2">
                                            <button class="btn btn-outline-primary btn-sm me-2" onclick="updateOrderStatus('Plating')">Plating</button>
                                            <button class="btn btn-outline-success btn-sm me-2" onclick="updateOrderStatus('Served')">Served</button>
                                            <button class="btn btn-outline-danger btn-sm me-2" onclick="updateOrderStatus('Cancelled')">Cancel</button>
                                        </div>
                                        <p class="mt-3">
                                            <strong>Payment Status:</strong> 
                                            <span id="modalPaymentStatus" class="badge bg-warning">Pending</span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="table-responsive mt-4">
                                    <table class="table table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalOrderItems">
                                            <!-- Order items will be inserted here -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                                <td colspan="2"><strong id="modalGrandTotal">₹0.00</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings View -->
            <div id="bookingsView" style="display: none;">
                <div class="container-fluid mt-4">
                    <h4 class="mb-3">Table Bookings</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="bg-dark text-light">Booking ID</th>
                                    <th class="bg-dark text-light">Name</th>
                                    <th class="bg-dark text-light">Table</th>
                                    <th class="bg-dark text-light">Email</th>
                                    <th class="bg-dark text-light">Date & Time</th>
                                    <th class="bg-dark text-light">Person Count</th>
                                    <th class="bg-dark text-light">Special Request</th>
                                    <th class="bg-dark text-light">Event</th>
                                    <th class="bg-dark text-light">Status</th>
                                    <th class="bg-dark text-light">Actions<th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody">
                                <!-- Bookings will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Menu View -->
            <div id="menuView" style="display: none;">
                <!-- Add Menu Item Form -->
                <div class="container-fluid mt-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">Add Menu Item</h5>
                                </div>
                                <div class="card-body">
                                    <form id="addMenuForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label class="form-label">Item Name</label>
                                            <input type="text" class="form-control" name="itemname" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="category" required>
                                                <option value="Starters">Starters</option>
                                                <option value="Main Course">Main Course</option>
                                                <option value="Desserts">Desserts</option>
                                                <option value="Beverages">Beverages</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Price</label>
                                            <input type="number" class="form-control" name="price" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="itemdescription" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detailed Information</label>
                                            <textarea class="form-control" name="itemdetailed" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Image</label>
                                            <input type="file" class="form-control" name="itemimage" accept="image/*" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Add Item</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Menu Items Display -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">Menu Items</h5>
                                </div>
                                <div class="card-body">
                                    <div class="menu-grid" id="menuItemsGrid">
                                        <?php
                                        // Modify this part in your PHP where you list menu items
$query = "SELECT f.*, fd.itemdetailed FROM tbl_fooditem f 
LEFT JOIN tbl_fooditemdetailed fd ON f.itemid = fd.itemid 
ORDER BY f.category";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $imagePath = getImagePath($row['itemimage']);
    // Convert to JSON and escape properly
    echo '<div class="menu-item" onclick="showMenuItemDetails(' . json_encode($row) . ')">';
    echo '<img src="' . $imagePath . '" onerror="this.src=\'img/default-food.jpg\'" alt="' . htmlspecialchars($row['itemname']) . '" style="width: 80px; height: 80px; object-fit: cover;">';
    echo '<h5>' . htmlspecialchars($row['itemname']) . '</h5>';
    echo '<p class="category">' . htmlspecialchars($row['category']) . '</p>';
    echo '<p class="price">₹' . htmlspecialchars($row['price']) . '</p>';
    echo '</div>';
}
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                <!-- Header -->
                <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
                    <h4 class="text-white mb-0">Payment Transactions</h4>
                    <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
                </div>
                
                <div class="container-fluid mt-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">All Payment Transactions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th>Bill No</th>
                                            <th>Customer Name</th>
                                            <th>Payment Mode</th>
                                            <th>Total Amount</th>
                                            <th>Payment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch payment transactions from database
                                        $sql = "SELECT order_id, customer_name, payment_method, total, payment_status 
                                                FROM tbl_orders 
                                                ORDER BY order_id DESC";
                                        $result = mysqli_query($conn, $sql);

                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                // Determine badge class for payment status
                                                $statusClass = '';
                                                switch($row['payment_status']) {
                                                    case 'Paid':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'Pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'Failed':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-secondary';
                                                }
                                                
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                                                echo "<td>₹" . number_format($row['total'], 2) . "</td>";
                                                echo "<td><span class='badge {$statusClass}'>" . htmlspecialchars($row['payment_status']) . "</span></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No payment transactions found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove the duplicate payments section that was outside the main container -->



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    let currentOrderId = null;
    let currentItemId = null;

    document.addEventListener('DOMContentLoaded', function() {
        console.log("DOM fully loaded");
        
        // For event listeners that might be causing problems
        try {
            // Safely add event listeners by checking if elements exist first
            const addMenuForm = document.getElementById('addMenuForm');
            if (addMenuForm) {
                console.log("Found addMenuForm, adding listener");
                addMenuForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch('add_menu_item.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Menu item added successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error adding menu item');
                    });
                });
            } else {
                console.log("addMenuForm not found");
            }
            
            // Fix the edit/delete button event handlers by adding them directly to the modal
            const editItemBtn = document.getElementById('editItemBtn');
            if (editItemBtn) {
                console.log("Found editItemBtn, adding direct listener");
                editItemBtn.addEventListener('click', function() {
                    editMenuItem(window.currentItemId);
                });
            } else {
                console.log("editItemBtn not found");
            }
            
            const deleteItemBtn = document.getElementById('deleteItemBtn');
            if (deleteItemBtn) {
                console.log("Found deleteItemBtn, adding direct listener");
                deleteItemBtn.addEventListener('click', function() {
                    deleteMenuItem(window.currentItemId);
                });
            } else {
                console.log("deleteItemBtn not found");
            }
            
            // Initialize other functions safely
            console.log("Initializing dashboard functions");
            
            // Check if functions exist before calling them
            if (typeof loadOrders === 'function') {
                loadOrders();
                setInterval(loadOrders, 30000);
            } else {
                console.log("loadOrders function not defined");
            }
            
            // Make sure updateTableStatusSafely is defined
            if (typeof updateTableStatusSafely === 'function') {
                updateTableStatusSafely();
                setInterval(updateTableStatusSafely, 30000);
            } else {
                console.log("updateTableStatusSafely function not defined");
            }
            
            // Make sure showOrders is defined
            if (typeof showOrders === 'function') {
                showOrders(); // Show orders view by default
            } else {
                console.log("showOrders function not defined");
            }
        } catch (error) {
            console.error("Error initializing dashboard:", error);
        }
    });

    // Fix image loading by using onerror handler
    document.addEventListener('DOMContentLoaded', function() {
        // Add global image error handler
        document.addEventListener('error', function(e) {
            if (e.target.tagName.toLowerCase() === 'img') {
                console.log('Image failed to load:', e.target.src);
                e.target.src = 'images/placeholder.png'; // Fallback image
                e.target.onerror = null; // Prevent infinite loop
            }
        }, true);
    });

    function showMenuItemDetails(item) {
        console.log("Item data received:", item); // Log the actual item data

        // Check if item exists and has required properties
        if (!item || !item.itemid) {
            console.error('Invalid item data:', item);
            alert('Error: Invalid item data');
            return;
        }

        try {
            // Store the current item ID globally
            window.currentItemId = item.itemid; // Use window. to ensure global scope
            console.log('Current item ID set to:', window.currentItemId);
            
            // Update modal content
            const nameElem = document.getElementById('menuItemName');
            const imageElem = document.getElementById('menuItemImage');
            const categoryElem = document.getElementById('menuItemCategory');
            const priceElem = document.getElementById('menuItemPrice');
            const descElem = document.getElementById('menuItemDescription');
            const detailedElem = document.getElementById('menuItemDetailed');
            
            if (nameElem) nameElem.textContent = item.itemname || 'Unknown';
            if (imageElem) {
                // Properly handle image path - always add 'images/' prefix to the filename
                let imagePath = 'img/default-food.jpg'; // Default image
                
                if (item.itemimage) {
                    // Remove any existing 'images/' prefix to ensure we don't duplicate it
                    let cleanImageName = item.itemimage;
                    if (cleanImageName.startsWith('images/')) {
                        cleanImageName = cleanImageName.substring(7);
                    }
                    imagePath = 'images/' + cleanImageName;
                }
                
                console.log('Setting image path to:', imagePath);
                imageElem.src = imagePath;
                
                // Add error handler to fall back to default image
                imageElem.onerror = function() { 
                    console.log('Image failed to load, using default');
                    this.src = 'img/default-food.jpg'; 
                    this.onerror = null; // Prevent infinite loop
                };
            }
            if (categoryElem) categoryElem.textContent = item.category || 'Uncategorized';
            if (priceElem) priceElem.textContent = '₹' + (item.price || '0');
            if (descElem) descElem.textContent = item.itemdescription || 'No description available';
            if (detailedElem) detailedElem.textContent = item.itemdetailed || 'No detailed description available';
            
            // Show the modal
            const modalElement = document.getElementById('menuItemModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        } catch (error) {
            console.error('Error showing menu item details:', error);
            alert('Error displaying menu item details');
        }
    }

    function editMenuItem(itemId) {
        if (!itemId) {
            console.error('No item ID provided for editing');
            alert('Error: Invalid item ID');
            return;
        }
        
        if (confirm('Are you sure you want to edit this item?')) {
            // Redirect to update.php with the item ID
            window.location.href = 'update.php?id=' + itemId;
        }
    }

    function deleteMenuItem(itemId) {
        // Make sure we have a valid itemId
        const idToDelete = itemId || window.currentItemId;
        
        console.log('Attempting to delete item ID:', idToDelete);
        
        if (!idToDelete) {
            console.error('No item ID provided');
            alert('Error: Invalid item ID');
            return;
        }

        if (confirm('Are you sure you want to delete this item?')) {
            console.log("Sending delete request for item ID:", idToDelete);
            
            // Use URLSearchParams for proper form encoding
            const params = new URLSearchParams();
            params.append('itemid', idToDelete);
            
            fetch('delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(result => {
                console.log('Raw server response:', result); // Debug the raw response
                
                // Handle success regardless of format
                if (result.includes('success') || result.includes('successfully')) {
                    alert('Item deleted successfully');
                    try {
                        const modalElement = document.getElementById('menuItemModal');
                        if (modalElement) {
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) {
                                modal.hide();
                            }
                        }
                    } catch (e) {
                        console.error("Error closing modal:", e);
                    }
                    
                    // Reload the page after a short delay
                    setTimeout(() => location.reload(), 500);
                } else {
                    throw new Error(result || 'Failed to delete item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting item: ' + error.message);
            });
        }
    }

    function viewBookingDetails(tableNumber) {
        fetch(`get_booking_details.php?table_number=${tableNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.booking) {
                    // Implement booking details view
                    alert(`Booking for table ${tableNumber}: ${data.booking.customer_name} at ${data.booking.datetime}`);
                } else {
                    alert('No booking for this table');
                }
            })
            .catch(error => {
                console.error('Error loading booking details:', error);
                alert('Error loading booking details');
            });
    }

    function loadBookings() {
        fetch('get_all_bookings.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('bookingsTableBody');
                tbody.innerHTML = '';
                
                data.bookings.forEach(booking => {
                    const row = document.createElement('tr');
                    
                    // Format the datetime
                    const dateTime = new Date(booking.datetime);
                    const formattedDateTime = dateTime.toLocaleString();
                    
                    row.innerHTML = `
                        <td>${booking.booking_id}</td>
                        <td>${booking.name}</td>
                        <td>${booking.table_number}</td>
                        <td>${booking.email}</td>
                        <td>${formattedDateTime}</td>
                        <td>${booking.people_count}</td>
                        <td>${booking.special_request || '-'}</td>
                        <td>${booking.special_option || '-'}</td>
                        <td>
                            <span class="badge bg-${booking.status === 'Confirmed' ? 'success' : 
                                                    booking.status === 'Cancelled' ? 'danger' : 'warning'}">
                                ${booking.status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="updateBookingStatus(${booking.booking_id}, 'Confirmed')">
                                Confirm
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="updateBookingStatus(${booking.booking_id}, 'Cancelled')">
                                Cancel
                            </button>
                            <button class="btn btn-sm btn-info" onclick="notifyCustomer(${booking.booking_id})">
                                Notify
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error loading bookings:', error);
                alert('Error loading bookings. Please try again.');
            });
    }

    // Add this to your existing showBookings function
    window.showBookings = function() {
        // Hide other views
        document.getElementById('ordersView').style.display = 'none';
        document.getElementById('menuView').style.display = 'none';
        
        // Show bookings view
        const bookingsView = document.getElementById('bookingsView');
        bookingsView.style.display = 'block';
        
        // Load bookings using get_booking_details.php instead of get_all_bookings.php
        fetch('get_booking_details.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const tbody = document.getElementById('bookingsTableBody');
                tbody.innerHTML = '';
                
                if (data.success && data.bookings) {
                    data.bookings.forEach(booking => {
                        const row = document.createElement('tr');
                        
                        row.innerHTML = `
                            <td>${booking.booking_id}</td>
                            <td>${booking.customer_name}</td>
                            <td>${booking.table_number}</td>
                            <td>${booking.email}</td>
                            <td>${booking.booking_date} ${booking.booking_time}</td>
                            <td>${booking.guest_count}</td>
                            <td>${booking.special_requests || '-'}</td>
                            <td>${booking.special_option || '-'}</td>
                            <td>
                                <span class="badge bg-${booking.status === 'Confirmed' ? 'success' : 
                                                        booking.status === 'Cancelled' ? 'danger' : 'warning'}">
                                    ${booking.status}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="updateBookingStatus(${booking.booking_id}, 'Confirmed')">
                                    Confirm
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="updateBookingStatus(${booking.booking_id}, 'Cancelled')">
                                    Cancel
                                </button>
                                <button class="btn btn-sm btn-info" onclick="notifyCustomer(${booking.booking_id})">
                                    Notify
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading bookings:', error);
                const tbody = document.getElementById('bookingsTableBody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center text-danger">
                            Error loading bookings. Please try again.
                        </td>
                    </tr>
                `;
            });
        
        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelector('.nav-link[onclick="showBookings()"]').classList.add('active');
    };

    // Refresh bookings every 30 seconds
    setInterval(loadBookings, 30000);
    </script>

    <!-- Add this modal structure before the closing </body> tag -->
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
                            <p><strong>Order ID:</strong> <span id="modalOrderId"></span></p>
                            <p><strong>Table:</strong> <span id="modalTableNumber"></span></p>
                            <p><strong>Time:</strong> <span id="modalOrderTime"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Status:</strong></p>
                            <div class="progress-container mb-3">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar" id="orderProgressBar" role="progressbar" style="width: 0%">
                                        <span id="orderStatusText">Pending</span>
                                    </div>
                                </div>
                            </div>
                            <div class="status-buttons mt-2">
                                <button class="btn btn-outline-primary btn-sm me-2" onclick="updateOrderStatus('Plating')">Plating</button>
                                <button class="btn btn-outline-success btn-sm me-2" onclick="updateOrderStatus('Served')">Served</button>
                                <button class="btn btn-outline-danger btn-sm me-2" onclick="updateOrderStatus('Cancelled')">Cancel</button>
                            </div>
                            <p class="mt-3">
                                <strong>Payment Status:</strong> 
                                <span id="modalPaymentStatus" class="badge bg-warning">Pending</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="modalOrderItems">
                                <!-- Order items will be inserted here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                    <td colspan="2"><strong id="modalGrandTotal">₹0.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Item Details Modal -->
    <div class="modal fade" id="menuItemModal" tabindex="-1" aria-labelledby="menuItemModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="menuItemModalLabel">Food Item Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-3">
              <img id="menuItemImage" src="" alt="Food Item" class="img-fluid rounded" style="max-height: 200px;">
            </div>
            <h3 id="menuItemName" class="mb-2"></h3>
            <p class="badge bg-secondary mb-2" id="menuItemCategory"></p>
            <h4 id="menuItemPrice" class="mb-3"></h4>
            <p id="menuItemDescription" class="mb-3"></p>
            <div class="mb-3">
              <h5>Detailed Description:</h5>
              <p id="menuItemDetailed"></p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="editItemBtn">Edit</button>
            <button type="button" class="btn btn-danger" id="deleteItemBtn">Delete</button>
          </div>
        </div>
      </div>
    </div>
     <!-- Toast container for notifications -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
</body>
</html>
