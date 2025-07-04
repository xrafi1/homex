<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('properties.php');
}

$property_id = $_GET['id'];
$property = getPropertyById($property_id);

if (!$property) {
    redirect('properties.php');
}

// Handle booking form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_property'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $start_date = sanitizeInput($_POST['start_date']);
    $end_date = !empty($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : NULL;
    
    // Calculate amount (simple calculation - in real app you'd have more complex logic)
    $days = $end_date ? (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) : 30;
    $amount = $property['price'] * ($days / 30); // Pro-rated amount
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("INSERT INTO bookings (property_id, user_id, start_date, end_date, amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissd", $property_id, $user_id, $start_date, $end_date, $amount);
    
    if ($stmt->execute()) {
        $success = "Booking request submitted successfully! The property owner will contact you soon.";
    } else {
        $error = "Failed to submit booking request. Please try again.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">Rent<span>Rooms</span></a>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="register.php" class="btn btn-primary">Register</a></li>
                    <?php endif; ?>
                </ul>
                <div class="mobile-menu">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Property Details Section -->
    <section class="property-details">
        <div class="container">
            <div class="property-header">
                <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['address']); ?>, <?php echo htmlspecialchars($property['city']); ?>, <?php echo htmlspecialchars($property['state']); ?> - <?php echo htmlspecialchars($property['pincode']); ?></p>
                <div class="property-price">
                    <span>₹<?php echo number_format($property['price']); ?>/month</span>
                    <?php if ($property['deposit']): ?>
                        <span>Deposit: ₹<?php echo number_format($property['deposit']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="property-status <?php echo $property['is_available'] ? 'available' : 'rented'; ?>">
                    <?php echo $property['is_available'] ? 'Available' : 'Rented'; ?>
                </div>
            </div>
            
            <div class="property-content">
                <div class="property-gallery">
                    <div class="main-image">
                        <img src="<?php echo UPLOAD_URL . $property['photos'][0]; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                    </div>
                    <div class="thumbnail-images">
                        <?php foreach ($property['photos'] as $photo): ?>
                            <img src="<?php echo UPLOAD_URL . $photo; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="property-info">
                    <div class="property-description">
                        <h2>Description</h2>
                        <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                    
                    <div class="property-features">
                        <h2>Features</h2>
                        <div class="features-grid">
                            <div class="feature">
                                <i class="fas fa-home"></i>
                                <span>Type: <?php echo $property['type']; ?></span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-bed"></i>
                                <span><?php echo $property['bedrooms']; ?> Bedrooms</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-bath"></i>
                                <span><?php echo $property['bathrooms']; ?> Bathrooms</span>
                            </div>
                            <?php if (!empty($property['amenities'])): 
                                $amenities = explode(',', $property['amenities']);
                                foreach ($amenities as $amenity): ?>
                                    <div class="feature">
                                        <i class="fas fa-check-circle"></i>
                                        <span><?php echo trim($amenity); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="property-contact">
                        <h2>Contact Owner</h2>
                        <div class="contact-card">
                            <div class="owner-info">
                                <h3><?php echo htmlspecialchars($property['owner_name']); ?></h3>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($property['owner_phone']); ?></p>
                            </div>
                            <?php if (isLoggedIn()): ?>
                                <a href="tel:<?php echo htmlspecialchars($property['owner_phone']); ?>" class="btn btn-primary"><i class="fas fa-phone"></i> Call Now</a>
                            <?php else: ?>
                                <p class="login-notice">Please <a href="login.php">login</a> to contact the owner</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($property['is_available']): ?>
            <div class="property-booking">
                <h2>Book This Property</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                    <?php if (isLoggedIn()): ?>
                        <form action="property-details.php?id=<?php echo $property_id; ?>" method="post">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="start_date">Move-in Date</label>
                                    <input type="date" id="start_date" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="end_date">Move-out Date (Optional)</label>
                                    <input type="date" id="end_date" name="end_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="book_property" class="btn btn-primary">Request Booking</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to book this property.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="property-map">
                <h2>Location</h2>
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>RentRooms is a platform connecting property owners with tenants looking for rooms, PGs, and apartments across India.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="properties.php">Properties</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter for the latest updates.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your Email">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> RentRooms. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
    <script>
        // Initialize Google Map
        function initMap() {
            // For demo purposes, we'll use a default location
            // In a real app, you would geocode the property address
            const location = { lat: 12.9716, lng: 77.5946 }; // Bangalore coordinates
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: location,
            });
            new google.maps.Marker({
                position: location,
                map: map,
                title: "<?php echo addslashes($property['title']); ?>",
            });
        }
        
        // Thumbnail image click handler
        const thumbnails = document.querySelectorAll('.thumbnail-images img');
        const mainImage = document.querySelector('.main-image img');
        
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImage.src = thumb.src;
            });
        });
    </script>
</body>
</html>
