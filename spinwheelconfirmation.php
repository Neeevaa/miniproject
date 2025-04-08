<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Prize Confirmation - Aromiq by Aurumé</title>
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
    
    <style>
        .prize-form-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .prize-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 5px solid #FEA116;
        }
        
        .prize-info h4 {
            color: #FEA116;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            height: 50px;
            border-radius: 5px;
        }
        
        .btn-submit {
            background-color: #FEA116;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #e08c0b;
            transform: scale(1.05);
        }
        
        .confirmation-message {
            display: none;
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
        }
        
        .confirmation-message h4 {
            color: #155724;
            margin-bottom: 15px;
        }
        
        .btn-confirm {
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .btn-confirm:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Navbar & Hero Start -->
        <div class="container-xxl position-relative p-0">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
                <a href="" class="navbar-brand p-0">
                    <h1 class="text-primary m-0"><i class="fa fa-utensils me-3"></i>Aromiq</h1>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0 pe-4">
                        <a href="index.html" class="nav-item nav-link">Home</a>
                        <a href="about.html" class="nav-item nav-link">About</a>
                        <a href="events.html" class="nav-item nav-link active">Events</a>
                        <a href="menu.php" class="nav-item nav-link">Menu</a>
                        <a href="booking.html" class="nav-item nav-link">Booking</a>
                        <a href="team.html" class="nav-item nav-link">Our Team</a>
                        <a href="testimonial.html" class="nav-item nav-link">Testimonial</a>
                        <a href="contact.html" class="nav-item nav-link">Contact</a>
                    </div>
                    <a href="login.php" class="btn btn-primary py-2 px-4 nav-item nav-link">Login</a>
                </div>
            </nav>

            <div class="container-xxl py-5 bg-dark hero-header mb-5">
                <div class="container text-center my-5 pt-5 pb-4">
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Prize Confirmation</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                            <li class="breadcrumb-item"><a href="events.html">Events</a></li>
                            <li class="breadcrumb-item"><a href="spinthewheel.php">Spin The Wheel</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Prize Confirmation</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Navbar & Hero End -->

        <!-- Prize Confirmation Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Claim Your Prize</h5>
                    <h1 class="mb-5">Complete Your Information</h1>
                </div>
                
                <div class="prize-form-container wow fadeInUp" data-wow-delay="0.3s">
                    <div class="prize-info">
                        <h4>Your Prize Details</h4>
                        <p><strong>Prize:</strong> <span id="prize-display"></span></p>
                        <p><strong>Coupon Code:</strong> <span id="coupon-display"></span></p>
                        <p class="text-muted">Please fill out the form below to claim your prize.</p>
                    </div>
                    
                    <form id="prize-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-control" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact">Contact Number</label>
                                    <input type="tel" class="form-control" id="contact" name="contact" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn-submit">Submit Information</button>
                        </div>
                    </form>
                    
                    <div class="confirmation-message" id="confirmation-message">
                        <h4>Congratulations!</h4>
                        <p>Your prize has been registered successfully. Please visit our counter with your coupon code to collect your prize.</p>
                        <p><strong>Coupon Code:</strong> <span id="final-coupon-display"></span></p>
                        <p><strong>Prize:</strong> <span id="final-prize-display"></span></p>
                        <button class="btn-confirm" id="confirm-collection">Confirm Prize Collection</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Prize Confirmation End -->

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
                            
                            Designed By: Neeva Sunish Mathew<br><br>
                            Distributed By: Aurumé
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
    
    <!-- Prize Confirmation Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const prize = urlParams.get('prize');
            const coupon = urlParams.get('coupon');
            
            // Display prize and coupon information
            document.getElementById('prize-display').textContent = prize || 'No prize selected';
            document.getElementById('coupon-display').textContent = coupon || 'No coupon code';
            document.getElementById('final-prize-display').textContent = prize || 'No prize selected';
            document.getElementById('final-coupon-display').textContent = coupon || 'No coupon code';
            
            // If no prize or coupon, redirect back to spin wheel
            if (!prize || !coupon) {
                alert('No prize information found. Redirecting to Spin The Wheel.');
                window.location.href = 'spinthewheel.php';
            }
            
            // Form submission
            const prizeForm = document.getElementById('prize-form');
            const confirmationMessage = document.getElementById('confirmation-message');
            
            prizeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate form
                const name = document.getElementById('name').value;
                const gender = document.getElementById('gender').value;
                const contact = document.getElementById('contact').value;
                const email = document.getElementById('email').value;
                
                if (!name || !gender || !contact || !email) {
                    alert('Please fill in all fields');
                    return;
                }
                
                // Simple email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address');
                    return;
                }
                
                // Simple phone validation (at least 10 digits)
                const phoneRegex = /^\d{10,}$/;
                if (!phoneRegex.test(contact.replace(/[^0-9]/g, ''))) {
                    alert('Please enter a valid contact number (at least 10 digits)');
                    return;
                }
                
                // Save data to database using AJAX
                const formData = new FormData();
                formData.append('name', name);
                formData.append('gender', gender);
                formData.append('contact', contact);
                formData.append('email', email);
                formData.append('prize', prize);
                formData.append('coupon', coupon);
                
                fetch('save_prize.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide form and show confirmation message
                        prizeForm.style.display = 'none';
                        confirmationMessage.style.display = 'block';
                        
                        // Scroll to confirmation message
                        confirmationMessage.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Error saving your information: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving your information. Please try again.');
                });
            });
            
            // Confirm prize collection
            const confirmCollectionButton = document.getElementById('confirm-collection');
            
            confirmCollectionButton.addEventListener('click', function() {
                // Update collection status in database
                const formData = new FormData();
                formData.append('coupon', coupon);
                
                fetch('update_prize_collection.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Prize collection confirmed! Thank you for visiting Aromiq.');
                        window.location.href = 'index.html';
                    } else {
                        alert('Error updating collection status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while confirming collection. Please try again.');
                });
            });
        });
    </script>
</body>

</html>