<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Recipe Submission - Aromiq by Aurumé</title>
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
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Recipe Submission</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                            <li class="breadcrumb-item"><a href="events.html">Events</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Recipe Submission</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Navbar & Hero End -->

        <!-- Recipe Form Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Share Your Recipe</h5>
                    <h1 class="mb-5">Submit Your Signature Dish</h1>
                </div>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="wow fadeInUp" data-wow-delay="0.2s">
                            <?php
                            // Check if form is submitted
                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                // Database configuration
                                $servername = "localhost";
                                $username = "admin";
                                $password = "1234";
                                $dbname = "aromiq";

                                // Create connection
                                $conn = new mysqli($servername, $username, $password, $dbname);

                                // Check connection
                                if ($conn->connect_error) {
                                    die("Connection failed: " . $conn->connect_error);
                                }

                                // Get form data
                                $name = $_POST['name'];
                                $email = $_POST['email'];
                                $recipe_name = $_POST['recipe_name'];
                                $ingredients = $_POST['ingredients'];
                                $instructions = $_POST['instructions'];
                                $image_path = "";

                                // Handle file upload
                                if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
                                    $target_dir = "uploads/recipes/";
                                    
                                    // Create directory if it doesn't exist
                                    if (!file_exists($target_dir)) {
                                        mkdir($target_dir, 0777, true);
                                    }
                                    
                                    $target_file = $target_dir . basename($_FILES["recipe_image"]["name"]);
                                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                                    
                                    // Generate unique filename
                                    $new_filename = uniqid() . '.' . $imageFileType;
                                    $target_file = $target_dir . $new_filename;
                                    
                                    // Check if image file is an actual image
                                    $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
                                    if($check !== false) {
                                        // Upload file
                                        if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
                                            $image_path = $target_file;
                                        }
                                    }
                                }

                                // Prepare and bind
                                $stmt = $conn->prepare("INSERT INTO tbl_recipes (name, email, recipe_name, ingredients, instructions, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt->bind_param("ssssss", $name, $email, $recipe_name, $ingredients, $instructions, $image_path);

                                // Execute the statement
                                if ($stmt->execute()) {
                                    echo '<div class="alert alert-success">Thank you for submitting your recipe! Our team will review it shortly.</div>';
                                } else {
                                    echo '<div class="alert alert-danger">Sorry, there was an error submitting your recipe. Please try again.</div>';
                                }

                                // Close statement and connection
                                $stmt->close();
                                $conn->close();
                            }
                            ?>
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                                            <label for="name">Your Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required>
                                            <label for="email">Your Email</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="recipe_name" name="recipe_name" placeholder="Recipe Name" required>
                                            <label for="recipe_name">Recipe Name</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" placeholder="Ingredients" id="ingredients" name="ingredients" style="height: 150px" required></textarea>
                                            <label for="ingredients">Ingredients (One per line)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" placeholder="Instructions" id="instructions" name="instructions" style="height: 200px" required></textarea>
                                            <label for="instructions">Cooking Instructions</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="file" class="form-control" id="recipe_image" name="recipe_image" accept="image/*">
                                            <label for="recipe_image">Upload Recipe Image</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100 py-3" type="submit">Submit Recipe</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Recipe Form End -->

        <!-- Footer Start -->
        <div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
            <!-- Footer content same as other pages -->
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
</body>

</html>