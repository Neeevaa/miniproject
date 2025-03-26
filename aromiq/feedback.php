<?php
session_start();
include 'connect.php';

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: menu.php");
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details and items
$order_query = "SELECT * FROM tbl_orders WHERE order_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("s", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: menu.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT * FROM tbl_order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("s", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];

while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}

// Handle form submission
$feedback_submitted = false;
$feedback_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (isset($_POST['rating']) && isset($_POST['comments'])) {
        $rating = intval($_POST['rating']);
        $comments = trim($_POST['comments']);
        
        if ($rating < 1 || $rating > 5) {
            $feedback_error = "Please provide a rating between 1 and 5.";
        } else {
            // Insert feedback into database
            $insert_query = "INSERT INTO tbl_feedback (orderid, rating, comments) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sis", $order_id, $rating, $comments);
            
            if ($insert_stmt->execute()) {
                $feedback_submitted = true;
            } else {
                $feedback_error = "Failed to submit feedback. Please try again.";
            }
        }
    } else {
        $feedback_error = "Please provide both rating and comments.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback - Aromiq</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }
        
        .feedback-container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .feedback-header {
            background: linear-gradient(135deg, #fea116, #e08e0b);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .chat-container {
            padding: 20px;
            height: 500px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .chat-bubble {
            max-width: 80%;
            padding: 15px;
            border-radius: 18px;
            margin-bottom: 15px;
            position: relative;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .chat-bot {
            align-self: flex-start;
            background: #f1f1f1;
            border-bottom-left-radius: 5px;
        }
        
        .chat-user {
            align-self: flex-end;
            background: #fea116;
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .rating-container {
            text-align: center;
            padding: 15px;
            margin: 15px 0;
        }
        
        .star-rating {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
        }
        
        .star-rating .fas {
            color: #FFD700;
        }
        
        .message-input {
            display: flex;
            padding: 15px;
            border-top: 1px solid #eee;
            background: white;
        }
        
        .message-input textarea {
            flex-grow: 1;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 10px 15px;
            margin-right: 10px;
            resize: none;
        }
        
        .send-btn {
            background: #fea116;
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .send-btn:hover {
            background: #e08e0b;
        }
        
        .order-items {
            background: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .success-message {
            text-align: center;
            padding: 50px 20px;
        }
        
        .success-icon {
            font-size: 70px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .back-btn {
            background: #fea116;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #e08e0b;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="feedback-container">
            <div class="feedback-header">
                <h3>We'd Love Your Feedback!</h3>
                <p>Order #<?php echo htmlspecialchars($order_id); ?></p>
            </div>
            
            <?php if ($feedback_submitted): ?>
                <!-- Success Message After Submission -->
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4>Thank You for Your Feedback!</h4>
                    <p>We appreciate you taking the time to share your thoughts with us.</p>
                    <p>Your feedback helps us improve our service.</p>
                    <a href="menu.php" class="back-btn">Return to Menu</a>
                </div>
            <?php else: ?>
                <!-- Feedback Form -->
                <form method="post" id="feedbackForm">
                    <div class="chat-container" id="chatContainer">
                        <!-- Initial Bot Messages -->
                        <div class="chat-bubble chat-bot">
                            <p>Hello! Thanks for dining with us at Aromiq. We'd love to hear about your experience.</p>
                        </div>
                        
                        <div class="chat-bubble chat-bot">
                            <p>Here's what you ordered:</p>
                            <div class="order-items">
                                <?php foreach ($order_items as $item): ?>
                                <div class="order-item">
                                    <span><?php echo htmlspecialchars($item['food_name']); ?> x <?php echo $item['quantity']; ?></span>
                                    <span>₹<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="chat-bubble chat-bot">
                            <p>How would you rate your overall experience?</p>
                            <div class="rating-container">
                                <div class="star-rating" id="starRating">
                                    <i class="far fa-star" data-rating="1"></i>
                                    <i class="far fa-star" data-rating="2"></i>
                                    <i class="far fa-star" data-rating="3"></i>
                                    <i class="far fa-star" data-rating="4"></i>
                                    <i class="far fa-star" data-rating="5"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-bubble chat-bot">
                            <p>Would you like to share any specific feedback about the food or service?</p>
                        </div>
                        
                        <?php if ($feedback_error): ?>
                        <div class="chat-bubble chat-bot" style="background-color: #f8d7da; color: #721c24;">
                            <p><?php echo htmlspecialchars($feedback_error); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="rating" id="ratingInput" value="0">
                    
                    <div class="message-input">
                        <textarea name="comments" placeholder="Type your feedback here..." required></textarea>
                        <button type="submit" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const starRating = document.getElementById('starRating');
            const ratingInput = document.getElementById('ratingInput');
            const stars = starRating.querySelectorAll('i');
            
            // Star rating functionality
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');
                    highlightStars(rating);
                });
                
                star.addEventListener('mouseout', function() {
                    const currentRating = ratingInput.value;
                    highlightStars(currentRating);
                });
                
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    highlightStars(rating);
                    
                    // Add user's rating as a chat bubble
                    const chatContainer = document.getElementById('chatContainer');
                    const userRatingBubble = document.createElement('div');
                    userRatingBubble.className = 'chat-bubble chat-user';
                    userRatingBubble.innerHTML = `<p>I'd rate my experience ${rating} out of 5 stars.</p>`;
                    chatContainer.appendChild(userRatingBubble);
                    
                    // Auto scroll to bottom
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                });
            });
            
            function highlightStars(rating) {
                stars.forEach(star => {
                    const starRating = star.getAttribute('data-rating');
                    if (starRating <= rating) {
                        star.className = 'fas fa-star';
                    } else {
                        star.className = 'far fa-star';
                    }
                });
            }
            
            // Form submission validation
            document.getElementById('feedbackForm').addEventListener('submit', function(e) {
                if (ratingInput.value == 0) {
                    e.preventDefault();
                    const chatContainer = document.getElementById('chatContainer');
                    const errorBubble = document.createElement('div');
                    errorBubble.className = 'chat-bubble chat-bot';
                    errorBubble.style.backgroundColor = '#f8d7da';
                    errorBubble.style.color = '#721c24';
                    errorBubble.innerHTML = '<p>Please provide a star rating before submitting.</p>';
                    chatContainer.appendChild(errorBubble);
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
            
            // Auto scroll to bottom on page load
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    </script>
</body>
</html> 