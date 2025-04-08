<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Spin The Wheel - Aromiq by Aurumé</title>
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
        .wheel-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 50px 0;
            position: relative;
        }
        
        .wheel {
            width: 400px;
            height: 400px;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            border: 8px solid #FEA116;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            transition: transform 5s ease-out;
            margin-top: 0; /* Removed margin since pointer is gone */
        }
        
        /* Removed pointer styles */
        
        .wheel-section {
            position: absolute;
            width: 50%;
            height: 50%;
            transform-origin: bottom right;
            clip-path: polygon(0 0, 100% 0, 100% 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            text-align: center;
            padding-left: 60px;
            padding-bottom: 60px;
            font-size: 14px;
        }
        
        .spin-button, .avail-prize-button {
            margin-top: 30px;
            padding: 15px 40px;
            font-size: 18px;
            background-color: #FEA116;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .spin-button:hover, .avail-prize-button:hover {
            background-color: #e08c0b;
            transform: scale(1.05);
        }
        
        .avail-prize-button {
            display: none;
            background-color: #28a745;
            margin-top: 15px;
        }
        
        .avail-prize-button:hover {
            background-color: #218838;
        }
        
        .result {
            margin-top: 30px;
            font-size: 24px;
            font-weight: bold;
            color: #FEA116;
            text-align: center;
            min-height: 50px;
        }
        
        /* Removed duplicate pointer style */
        
        /* Add canvas for confetti */
        #confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <!-- Add confetti canvas -->
    <canvas id="confetti-canvas"></canvas>
    
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
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Spin The Wheel</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                            <li class="breadcrumb-item"><a href="events.html">Events</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Spin The Wheel</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Navbar & Hero End -->

        <!-- Wheel Content Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Try Your Luck</h5>
                    <h1 class="mb-5">Spin The Wheel For Amazing Rewards</h1>
                </div>
                
                <div class="wheel-container wow fadeInUp" data-wow-delay="0.3s">
                    <div class="wheel" id="wheel">
                        <div class="wheel-section" style="transform: rotate(0deg); background-color:red;">10% OFF</div>
                        <div class="wheel-section" style="transform: rotate(45deg); background-color:#fd7e14;">Free Dessert</div>
                        <div class="wheel-section" style="transform: rotate(90deg); background-color:yellow;">15% OFF</div>
                        <div class="wheel-section" style="transform: rotate(135deg); background-color:green;">Free Drink</div>
                        <div class="wheel-section" style="transform: rotate(180deg); background-color:blue;">20% OFF</div>
                        <div class="wheel-section" style="transform: rotate(225deg); background-color:violet;">Free Appetizer</div>
                        <div class="wheel-section" style="transform: rotate(270deg); background-color:purple;">25% OFF</div>
                        <div class="wheel-section" style="transform: rotate(315deg); background-color:brown">Better Luck Next Time</div>
                    </div>
                    <!-- Removed pointer div -->
                    <button class="spin-button" id="spin-button">SPIN</button>
                    <div class="result" id="result"></div>
                    <a href="#" class="avail-prize-button" id="avail-prize-button">AVAIL PRIZE</a>
                </div>
            </div>
        </div>
        <!-- Wheel Content End -->

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

    <!-- Add confetti.js library -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    
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
    
    <!-- Wheel Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wheel = document.getElementById('wheel');
            const spinButton = document.getElementById('spin-button');
            const result = document.getElementById('result');
            const availPrizeButton = document.getElementById('avail-prize-button');
            const canvas = document.getElementById('confetti-canvas');
            const myConfetti = confetti.create(canvas, { resize: true });
            
            const prizes = [
                "10% OFF on your bill",
                "Free Dessert of your choice",
                "15% OFF on your bill",
                "Free Drink of your choice",
                "20% OFF on your bill",
                "Free Appetizer of your choice",
                "25% OFF on your bill",
                "Better Luck Next Time"
            ];
            
            let canSpin = true;
            let currentPrize = "";
            let currentCouponCode = "";
            
            spinButton.addEventListener('click', function() {
                if (!canSpin) return;
                
                // Reset result and hide avail prize button
                result.textContent = "";
                availPrizeButton.style.display = "none";
                
                // Disable button during spin
                canSpin = false;
                spinButton.disabled = true;
                
                // Random number of rotations (between 5 and 10)
                const rotations = 5 + Math.random() * 5;
                
                // Random prize (0-7)
                const prizeIndex = Math.floor(Math.random() * 8);
                
                // Calculate the final rotation angle
                // Each section is 45 degrees, so we multiply the index by 45
                const finalAngle = (rotations * 360) + (prizeIndex * 45) + Math.random() * 20;
                
                // Apply the rotation
                wheel.style.transform = `rotate(${finalAngle}deg)`;
                
                // Show result after the wheel stops spinning
                setTimeout(function() {
                    // Check if the prize is "Better Luck Next Time"
                    if (prizeIndex === 7) {
                        result.textContent = "Better Luck Next Time!";
                        currentPrize = "";
                        currentCouponCode = "";
                    } else {
                        currentPrize = prizes[prizeIndex];
                        currentCouponCode = generateCouponCode();
                        
                        result.textContent = `Congratulations! You won: ${currentPrize}`;
                        result.innerHTML += `<br><span style="font-size: 18px; color: #333;">Your coupon code: <strong>${currentCouponCode}</strong></span>`;
                        
                        // Show avail prize button
                        availPrizeButton.style.display = "inline-block";
                        
                        // Set href with prize and coupon code as parameters
                        availPrizeButton.href = `spinwheelconfirmation.php?prize=${encodeURIComponent(currentPrize)}&coupon=${encodeURIComponent(currentCouponCode)}`;
                        
                        // Trigger confetti celebration for winners
                        triggerConfetti();
                    }
                    
                    // Enable button after spin
                    canSpin = true;
                    spinButton.disabled = false;
                }, 5000); // 5 seconds for the wheel to stop
            });
            
            // Generate a random coupon code
            function generateCouponCode() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let code = 'AROMIQ-';
                for (let i = 0; i < 6; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return code;
            }
            
            // Function to trigger confetti
            function triggerConfetti() {
                // Fire confetti from the center
                myConfetti({
                    particleCount: 200,
                    spread: 160,
                    origin: { y: 0.6 }
                });
                
                // Fire more confetti from different angles
                setTimeout(() => {
                    myConfetti({
                        particleCount: 100,
                        angle: 60,
                        spread: 70,
                        origin: { x: 0 }
                    });
                }, 250);
                
                setTimeout(() => {
                    myConfetti({
                        particleCount: 100,
                        angle: 120,
                        spread: 70,
                        origin: { x: 1 }
                    });
                }, 400);
            }
        });
    </script>
</body>

</html>