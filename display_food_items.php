<?php
session_start();
$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Food Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .food-item {
            cursor: pointer;
            transition: transform 0.2s;
            height: 100%;
        }
        .food-item:hover {
            transform: scale(1.05);
        }
        .food-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Food Items</h2>
        <div class="row g-4">
            <?php
            $query = "SELECT f.*, fd.itemdetailed 
                     FROM tbl_fooditem f 
                     LEFT JOIN tbl_fooditemdetailed fd ON f.itemid = fd.itemid 
                     ORDER BY f.category, f.itemname";
            $result = mysqli_query($conn, $query);
            
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="food-item card" onclick="showDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                        <img src="<?php echo htmlspecialchars($row['itemimage']); ?>" class="card-img-top food-image" alt="<?php echo htmlspecialchars($row['itemname']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['itemname']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($row['category']); ?></p>
                            <p class="card-text">₹<?php echo htmlspecialchars($row['price']); ?></p>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- Modal for item details -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalItemName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modalItemImage" class="img-fluid rounded" alt="">
                        </div>
                        <div class="col-md-6">
                            <h4>Details</h4>
                            <p id="modalItemDescription"></p>
                            <h4>Additional Information</h4>
                            <p id="modalItemDetailed"></p>
                            <h4>Price</h4>
                            <p id="modalItemPrice"></p>
                            <h4>Category</h4>
                            <p id="modalItemCategory"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetails(item) {
            document.getElementById('modalItemName').textContent = item.itemname;
            document.getElementById('modalItemImage').src = item.itemimage;
            document.getElementById('modalItemDescription').textContent = item.itemdescription;
            document.getElementById('modalItemDetailed').textContent = item.itemdetailed;
            document.getElementById('modalItemPrice').textContent = '₹' + item.price;
            document.getElementById('modalItemCategory').textContent = item.category;
            
            const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();
        }
    </script>
</body>
</html> 