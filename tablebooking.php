<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Booking Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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

    .table-box.booked {
        background-color: #ffe5e5;
        border-color: #ffcccc;
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

    .booking-time {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 12px;
        color: #dc3545;
    }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="bg-dark py-2 px-4 w-100 d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">Table Booking Dashboard</h4>
        <a href="logout.php" class="btn btn-primary py-2 px-4" style="background-color: #fea116; border-color: #fea116;">Logout</a>
    </div>

    <!-- Table Layout -->
    <div class="container-fluid mt-4">
        <h4 class="mb-3">Table Layout</h4>
        <div class="table-grid">
            <?php for($i = 1; $i <= 12; $i++): ?>
            <div class="table-box" id="table-<?php echo $i; ?>" onclick="viewBookingDetails(<?php echo $i; ?>)">
                <div class="table-number"><?php echo $i; ?></div>
                <div class="table-status" id="table-status-<?php echo $i; ?>">Available</div>
                <div class="booking-time" id="booking-time-<?php echo $i; ?>"></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p><strong>Booking ID:</strong> <span id="modalBookingId"></span></p>
                        <p><strong>Customer Name:</strong> <span id="modalCustomerName"></span></p>
                        <p><strong>Table Number:</strong> <span id="modalTableNumber"></span></p>
                        <p><strong>Date:</strong> <span id="modalBookingDate"></span></p>
                        <p><strong>Time:</strong> <span id="modalBookingTime"></span></p>
                        <p><strong>Number of Guests:</strong> <span id="modalGuestCount"></span></p>
                        <p><strong>Special Requests:</strong> <span id="modalSpecialRequests"></span></p>
                        <p><strong>Contact Number:</strong> <span id="modalContactNumber"></span></p>
                        <p><strong>Status:</strong> <span id="modalBookingStatus" class="badge"></span></p>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-success" onclick="updateBookingStatus(currentBookingId, 'Confirmed')">
                            Confirm
                        </button>
                        <button class="btn btn-danger" onclick="updateBookingStatus(currentBookingId, 'Cancelled')">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let currentBookingId = null;

    function updateTableStatus() {
        fetch('get_table_bookings.php')
            .then(response => response.json())
            .then(data => {
                // Reset all tables
                for(let i = 1; i <= 12; i++) {
                    const tableBox = document.getElementById(`table-${i}`);
                    const tableStatus = document.getElementById(`table-status-${i}`);
                    const bookingTime = document.getElementById(`booking-time-${i}`);
                    
                    if (tableBox && tableStatus && bookingTime) {
                        tableBox.classList.remove('booked');
                        tableStatus.textContent = 'Available';
                        bookingTime.textContent = '';
                    }
                }
                
                // Update booked tables
                data.forEach(booking => {
                    const tableBox = document.getElementById(`table-${booking.table_number}`);
                    const tableStatus = document.getElementById(`table-status-${booking.table_number}`);
                    const bookingTime = document.getElementById(`booking-time-${booking.table_number}`);
                    
                    if (tableBox && tableStatus && bookingTime) {
                        tableBox.classList.add('booked');
                        tableStatus.textContent = `Booked - ${booking.booking_status}`;
                        bookingTime.textContent = booking.booking_time;
                    }
                });
            })
            .catch(error => console.error('Error updating table status:', error));
    }

    function viewBookingDetails(tableNumber) {
        fetch(`get_booking_details.php?table_number=${tableNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.booking) {
                    currentBookingId = data.booking.booking_id;
                    
                    // Update modal content
                    document.getElementById('modalBookingId').textContent = data.booking.booking_id;
                    document.getElementById('modalCustomerName').textContent = data.booking.customer_name;
                    document.getElementById('modalTableNumber').textContent = data.booking.table_number;
                    document.getElementById('modalBookingDate').textContent = data.booking.booking_date;
                    document.getElementById('modalBookingTime').textContent = data.booking.booking_time;
                    document.getElementById('modalGuestCount').textContent = data.booking.guest_count;
                    document.getElementById('modalSpecialRequests').textContent = data.booking.special_requests || 'None';
                    document.getElementById('modalContactNumber').textContent = data.booking.contact_number;
                    
                    const statusBadge = document.getElementById('modalBookingStatus');
                    statusBadge.textContent = data.booking.booking_status;
                    statusBadge.className = `badge bg-${data.booking.booking_status === 'Confirmed' ? 'success' : 
                                                      data.booking.booking_status === 'Cancelled' ? 'danger' : 'warning'}`;
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                    modal.show();
                } else {
                    alert('No booking found for this table');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading booking details');
            });
    }

    function updateBookingStatus(bookingId, status) {
        const formData = new URLSearchParams();
        formData.append('booking_id', bookingId);
        formData.append('status', status);

        fetch('update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the status badge
                const statusBadge = document.getElementById('modalBookingStatus');
                statusBadge.textContent = status;
                statusBadge.className = `badge bg-${status === 'Confirmed' ? 'success' : 
                                                  status === 'Cancelled' ? 'danger' : 'warning'}`;
                
                // Refresh table status
                updateTableStatus();
            } else {
                alert(data.message || 'Error updating booking status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating booking status');
        });
    }

    // Initial load and periodic updates
    document.addEventListener('DOMContentLoaded', function() {
        updateTableStatus();
        setInterval(updateTableStatus, 30000); // Update every 30 seconds
    });
    </script>
</body>
</html> 