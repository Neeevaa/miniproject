<?php
// Add this at the very top of menu.php
session_start();
$session_id = session_id();
include 'connect.php';
$cartItems = [];
$sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);

// Add this at the top of the file to detect AJAX requests
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Check if we have a specific error parameter
if (isset($_GET['error'])) {
    $error_type = $_GET['error'];
    
    // If this is an AJAX request, return JSON error
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $error_type === 'order_not_found' 
                ? 'Order not found. Please try again.' 
                : 'An error occurred: ' . $error_type
        ]);
        exit;
    }
    
    // For regular page loads, show the error in HTML
    $error_message = '';
    switch($error_type) {
        case 'order_not_found':
            $error_message = 'Order not found. Please try again.';
            break;
        case 'no_order_number':
            $error_message = 'No order number provided. Please try again.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
    
    // The error message will be displayed in the HTML below
}

// Add this code where you handle the cart update in menu.php
if (isset($_GET['update_cart']) && $_GET['update_cart'] == '1') {
    header('Content-Type: application/json');
    
    try {
        // Get the JSON data from the request
        $json_data = file_get_contents('php://input');
        $cart_data = json_decode($json_data, true);
        
        if ($cart_data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data');
        }
        
        // Update the session with the cart data
        $_SESSION['cart'] = $cart_data;
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Cart updated successfully'
        ]);
    } catch (Exception $e) {
        // Return error response
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
    exit; // Stop execution after handling the AJAX request
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Aromiq  -by Aurumé</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <script src="https://kit.fontawesome.com/07264d6aa5.js" crossorigin="anonymous"></script>
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <script src="script.js" rel="script"></script>
    <style>
        body.no-scroll {
    overflow: hidden;
}

.card {
    position: fixed;
    top: 0;
    right: -100%;
    width: 400px;
    height: 100vh;
    background-color: white;
    padding: 20px;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
    transition: 0.5s;
    z-index: 1000;
}

.card.active {
    right: 0;
}

.closeShopping {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-controls button {
    padding: 5px;
    border: none;
    background: #fea116;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

.tray-icon {
    cursor: pointer;
}

.food-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
}

.popup-content {
    background-color: white;
    padding: 30px;
    border-radius: 20px;
    width: 90%;
    max-width: 600px;
    position: relative;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    max-height: 90vh;
    overflow-y: auto;
    animation: popupFadeIn 0.3s ease-out;
}

@keyframes popupFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.popup-image {
    width: 250px;
    height: 250px;
    object-fit: cover;
    border-radius: 15px;
    margin-bottom: 20px;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.close-popup {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.close-popup:hover {
    background-color: #ff4444;
    color: white;
}

.add-to-platter {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    width: 100%;
}

.add-to-platter:hover {
    background-color: #e69500;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(254, 161, 22, 0.3);
}

.food-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: rgba(254, 161, 22, 0.05);
    border-radius: 8px;
    padding: 10px;
}

.food-item-card {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
    gap: 10px;
}

.food-details {
    flex-grow: 1;
}

.food-details p {
    margin: 0;
}

.total {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #eee;
    text-align: right;
}

.total-amount {
    font-size: 1.2em;
    font-weight: bold;
    color: var(--primary);
}

.listCard {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}
    </style>
</head>

<body>
<div class="card">
    <h1>Your Platter</h1>
    <span class="closeShopping">&times;</span>
    <div class="listCard">
        <!-- Cart items will be added here dynamically -->
    </div>
    <div class="total">
        <span>Total:</span>
        <span class="total-amount">₹0.00</span>
    </div>
    <button class="btn btn-primary py-2 px-4" onclick="checkout()">Checkout</button>
    <script>
        function checkout() {
            location.href = 'checkout.php';
        }
    </script>
</div>
<div id="foodPopup" class="food-popup">
    <div class="popup-content">
        <span class="close-popup" onclick="closePopup()">&times;</span>
        <img id="popupImage" src="" alt="" class="popup-image">
        <h2 id="popupName" style="color: var(--dark); margin-bottom: 10px;"></h2>
        <h4 id="popupPrice" class="text-primary mb-3"></h4>
        <div class="description-section">
            <p id="popupDetailedDescription" class="mb-4"></p>
        </div>
        <button class="add-to-platter" onclick="addToPlatter()">
            <i class="fa fa-plus me-2"></i>Add to Platter
        </button>
    </div>
</div>
    
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start-->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
       <!--Spinner End -->


        <!-- Navbar & Hero Start -->
        <div class="container-xxl position-relative p-0">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
                <a href="index.html" class="navbar-brand p-0">
                    <h1 class="text-primary m-0"><i class="fa fa-utensils me-3"></i>Aromiq</h1>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0 pe-4">
                        <a href="index.html" class="nav-item nav-link">Home</a>
                        <a href="about.html" class="nav-item nav-link">About</a>
                        <a href="menu.html" class="nav-item nav-link active">Menu</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                            <div class="dropdown-menu m-0">
                                <a href="booking.html" class="dropdown-item">Booking</a>
                                <a href="team.html" class="dropdown-item">Our Team</a>
                                <a href="testimonial.html" class="dropdown-item">Testimonial</a>
                            </div>
                        </div>
                        <a href="contact.html" class="nav-item nav-link">Contact</a> 
                    </div>
                    <div class="shopping">
                        <img src="img/tray.png" alt="Tray Icon" class="tray-icon">
                        <span class="quantity">0</span>
                    </div>
                
                </div>
            </nav>
        </div>
            </nav>
    </div>
                <div class="container-xxl py-5 bg-dark hero-header mb-5">
                <div class="container text-center my-5 pt-5 pb-4">
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Food Menu</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Pages</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Menu</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Navbar & Hero End -->
        
<!-- Menu Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
            <h1 class="mb-5">Most Popular Items</h1>
        </div>
        <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
            <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5">
                <li class="nav-item">
                    <a class="d-flex align-items-center text-start mx-3 ms-0 pb-3 active" data-bs-toggle="pill" href="#tab-1">
                        <i class="fa fa-coffee fa-2x text-primary"></i>
                        <div class="ps-3">
                            <small class="text-body">Popular</small>
                            <h6 class="mt-n1 mb-0">Starters</h6>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="d-flex align-items-center text-start mx-3 pb-3" data-bs-toggle="pill" href="#tab-2">
                        <i class="fa fa-hamburger fa-2x text-primary"></i>
                        <div class="ps-3">
                            <small class="text-body">Special</small>
                            <h6 class="mt-n1 mb-0">Main Course</h6>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="d-flex align-items-center text-start mx-3 pb-3" data-bs-toggle="pill" href="#tab-3">
                        <i class="fa fa-utensils fa-2x text-primary"></i>
                        <div class="ps-3">
                            <small class="text-body">Lovely</small>
                            <h6 class="mt-n1 mb-0">Desserts</h6>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="d-flex align-items-center text-start mx-3 pb-3" data-bs-toggle="pill" href="#tab-4">
                        <i class="fa fa-wine-glass fa-2x text-primary"></i>
                        <div class="ps-3">
                            <small class="text-body">Refreshing</small>
                            <h6 class="mt-n1 mb-0">Beverages</h6>
                        </div>
                    </a>
                </li>
            </ul>
            
            <div class="tab-content">
                <?php
                // Database connection
                $servername = "localhost";
                $username = "admin";
                $password = "1234";
                $dbname = "aromiq";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Array of categories and their tab IDs
                $categories = [
                    'Starters' => 'tab-1',
                    'Main Course' => 'tab-2',
                    'Desserts' => 'tab-3',
                    'Beverages' => 'tab-4'
                ];

                foreach ($categories as $category => $tabId) {
                    $active = ($category == 'Starters') ? 'show active' : '';
                    echo "<div id='$tabId' class='tab-pane fade $active p-0'>";
                    echo "<div class='row g-4'>";
                
                    // Updated query to include itemdetailed
                    $sql = "SELECT itemid, itemname, itemimage, price, itemdescription, itemdetailed, category FROM tbl_fooditem WHERE category = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $category);
                    $stmt->execute();
                    $result = $stmt->get_result();
                
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center food-item" 
                                 onclick="showFoodDetails(<?php echo $row['itemid']; ?>)" 
                                 data-detailed="<?php echo htmlspecialchars($row['itemdetailed'] ?? ''); ?>"
                                 style="cursor: pointer; transition: all 0.3s ease;">
                                <img class="flex-shrink-0 img-fluid rounded food-image" 
                                     src="images/<?php echo htmlspecialchars($row['itemimage']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['itemname']); ?>" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                                <div class="w-100 d-flex flex-column text-start ps-4">
                                    <h5 class="d-flex justify-content-between border-bottom pb-2">
                                        <span><?php echo htmlspecialchars($row['itemname']); ?></span>
                                        <span class="text-primary">₹<?php echo htmlspecialchars($row['price']); ?></span>
                                    </h5>
                                    <small class="fst-italic"><?php echo htmlspecialchars($row['itemdescription']); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    echo "</div></div>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</div>
<!-- Menu End -->


        <!--Top 3 Picks of Customers-->
        <div class="container-xxl py-5">
            <div class="container text-center">
                <h1 >Top Picks of Our Customers</h1>
                <div class="row g-4">
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="service-item rounded pt-3">
                            <div class="p-4">
                                <img src="img/menufood/skewers.jpeg" alt="Skewers" class="mb-4" style="width: 60px;">
                                <h5>BBQ Chicken Skewers</h5>
                                <p>"The BBQ Chicken Skewers were absolutely divine, with tender, juicy chicken perfectly infused with a rich, smoky BBQ flavor that left me craving for more!"</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="service-item rounded pt-3">
                            <div class="p-4">
                                <img src="img/menufood/alfredo.jpeg" alt="alfredo" class="mb-4" style="width: 60px;">
                                <h5>Alfredo Pasta with Garlic Bread</h5>
                                <p>"The Pasta Alfredo with Garlic Bread was a decadent delight, with rich, creamy fettuccine perfectly paired with crispy, aromatic garlic bread that left me utterly satisfied!"</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="service-item rounded pt-3">
                            <div class="p-4">
                                <img src="img/menufood/tiramisu.jpeg" alt="tiramisu" class="mb-4" style="width: 60px;">
                                <h5>Tiramisu</h5>
                                <p>"The Tiramisu was a sublime delight, with perfectly balanced layers of espresso-soaked ladyfingers and creamy mascarpone cheese that melted in my mouth!"</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                        <div class="service-item rounded pt-3">
                            <div class="p-4">
                                <img src="img/menufood/lasagna.jpeg" alt="Enjoy meals" class="mb-4" style="width: 60px;">
                                <h5>Spinach and Corn Lasagna</h5>
                                <p>"The Spinach and Corn Lasagna was a flavorful masterpiece, with tender layers of pasta, sweet corn, and savory spinach in a rich, creamy sauce that exceeded my expectations!"</p>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <!--Top 3 picks ends-->

<!-- Footer Start -->
<div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-3 col-md-6">
                <h4 class="section-title ff-secondary text-start text-primary fw-normal mb-4">Company</h4>
                <a class="btn btn-link" href="about.html">About Us</a>
                <a class="btn btn-link" href="contact.html">Contact Us</a>
                <a class="btn btn-link" href="booking.html">Reservation</a>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4 class="section-title ff-secondary text-start text-primary fw-normal mb-4">Contact</h4>
                <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Maple Street, Madagascar, Africa</p>
                <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+457 98 475 987 25</p>
                <p class="mb-2"><i class="fa fa-envelope me-3"></i>aromiq@gmail.com</p>
                <div class="d-flex pt-2">
                    <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-youtube"></i></a>
                    <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4 class="section-title ff-secondary text-start text-primary fw-normal mb-4">Opening</h4>
                <h5 class="text-light fw-normal">Monday - Saturday</h5>
                <p>09AM - 09PM</p>
                <h5 class="text-light fw-normal">Sunday</h5>
                <p>10AM - 08PM</p>
                </div>
        </div>
    </div>
    <div class="container">
        <div class="copyright">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    &copy; <a class="border-bottom" href="#">Aromiq</a>, All Right Reserved. 
                    
                    <!--/*** This template is free as long as you keep the footer author's credit link/attribution link/backlink. If you'd like to use the template without the footer author's credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                    Designed By :Neeva Sunish Mathew<br><br>
                    Distributed By :Aurumé
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="footer-menu">
                        <a href="">Home</a>
                        <a href="">Cookies</a>
                        <a href="">Help</a>
                        <a href="">FQAs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
    let listCards = [];
    let total = 0;

    // Initialize shopping cart functionality
    document.querySelector('.tray-icon').addEventListener('click', () => {
        document.querySelector('.card').classList.add('active');
        document.body.classList.add('no-scroll');
    });

    document.querySelector('.closeShopping').addEventListener('click', () => {
        document.querySelector('.card').classList.remove('active');
        document.body.classList.remove('no-scroll');
    });

    function addToPlatter() {
    const name = document.getElementById('popupName').textContent;
    const price = parseFloat(document.getElementById('popupPrice').textContent.replace('₹', ''));
    const image = document.getElementById('popupImage').src.split('images/').pop();

    // Check if item already exists
    const existingItemIndex = listCards.findIndex(item => item.name === name);
    
    if (existingItemIndex !== -1) {
        // Update quantity if item exists
        changeQuantity(existingItemIndex, listCards[existingItemIndex].quantity + 1);
    } else {
        // Add new item if it doesn't exist
        const formData = new FormData();
        formData.append('food_name', name);
        formData.append('price', price);
        formData.append('quantity', 1);
        formData.append('image_path', image);

        fetch('save-to-cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                listCards.push({
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1
                });
                updateQuantityDisplay();
                reloadCard();
                closePopup();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding item to platter');
        });
    }
}

    function updateQuantityDisplay() {
        const totalQuantity = listCards.reduce((sum, item) => sum + item.quantity, 0);
        document.querySelector('.quantity').textContent = totalQuantity;
    }

    function reloadCard() {
        const listCard = document.querySelector('.listCard');
        listCard.innerHTML = '';
        total = 0;

        listCards.forEach((item, index) => {
            total += item.price * item.quantity;
            if (item.quantity > 0) {
                const newDiv = document.createElement('div');
                newDiv.innerHTML = `
                    <div class="food-item-card">
                        <img src="images/${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <div class="food-details">
                            <p>${item.name}</p>
                            <p>₹${item.price}</p>
                        </div>
                        <div class="quantity-controls">
                            <button onclick="changeQuantity(${index}, ${item.quantity - 1})">-</button>
                            <span>${item.quantity}</span>
                            <button onclick="changeQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                `;
                listCard.appendChild(newDiv);
            }
        });

        document.querySelector('.total-amount').textContent = `₹${total.toFixed(2)}`;
    }

    function changeQuantity(index, quantity) {
        const item = listCards[index];
        const formData = new FormData();
        formData.append('food_name', item.name);
        formData.append('quantity', quantity);
        formData.append('action', quantity <= 0 ? 'remove' : 'update');

        fetch('update-cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (quantity <= 0) {
                    listCards.splice(index, 1);
                } else {
                    listCards[index].quantity = quantity;
                }
                updateQuantityDisplay();
                reloadCard();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function showFoodDetails(itemId) {
    console.log('Showing details for item:', itemId);
    fetch(`get_food_details.php?id=${itemId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Update popup with data
            document.getElementById('popupImage').src = 'images/' + data.itemimage;
            document.getElementById('popupName').textContent = data.itemname;
            document.getElementById('popupPrice').textContent = '₹' + data.price;
            document.getElementById('popupDetailedDescription').textContent = data.itemdetailed ||data.itemdescription || 'No detailed description available.';
            
            const popup = document.getElementById('foodPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading food details. Please try again.');
        });
}

    function closePopup() {
        const popup = document.getElementById('foodPopup');
        popup.style.opacity = '0';
        setTimeout(() => {
            popup.style.display = 'none';
            popup.style.opacity = '1';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Close popup when clicking outside
    document.getElementById('foodPopup').addEventListener('click', function(e) {
        if (e.target === this) {
            closePopup();
        }
    });

    // Prevent popup from closing when clicking inside the content
    document.querySelector('.popup-content').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    function saveToDatabase(item) {
        fetch('save-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(item)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update session data
                updateCartSession();
            } else {
                console.error('Error saving to database');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateCartSession() {
        fetch('get-cart-items.php')
            .then(response => response.json())
            .then(data => {
                listCards = data;
                reloadCard();
                updateQuantityDisplay();
            })
            .catch(error => console.error('Error:', error));
    }

    // Add this to your existing DOMContentLoaded event
    document.addEventListener('DOMContentLoaded', function() {
        // Load cart items from session
        updateCartSession();
    });

    function proceedToCheckout() {
        if (listCards.length > 0) {
            // Save current cart state before redirecting
            saveToDatabase(listCards[listCards.length - 1])
                .then(() => {
                    window.location.href = 'checkout.php';
                });
        } else {
            alert('Please add items to your platter before checking out');
        }
    }

    function addToCart(foodItem) {
        const formData = new FormData();
        formData.append('food_name', foodItem.name);
        formData.append('price', foodItem.price);
        formData.append('quantity', 1);
        formData.append('image_path', foodItem.image);

        fetch('save-to-cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item added to platter!');
                console.log('Cart items:', data.cartItems);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding item to platter');
        });
    }

    function updateCartCounter() {
        // If you have a cart counter in your menu
        fetch('get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCounter = document.getElementById('cart-counter');
            if (cartCounter) {
                cartCounter.textContent = data.count;
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart with items from PHP
    listCards = <?php echo json_encode($cartItems); ?>.map(item => ({
        name: item.food_name,
        price: parseFloat(item.price),
        image: item.image_path,
        quantity: parseInt(item.quantity)
    }));
    
    // Update display
    reloadCard();
    updateQuantityDisplay();
    
    // Show cart if there are items
    if (listCards.length > 0) {
        document.querySelector('.quantity').textContent = listCards.reduce((sum, item) => sum + item.quantity, 0);
    }
});
    </script>
</body>

</html>