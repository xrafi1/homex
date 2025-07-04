<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$properties = getProperties(['type' => 'PG', 'limit' => 6]);
$roomProperties = getProperties(['type' => 'Room', 'limit' => 6]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Find Your Perfect Stay</title>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Find Your Perfect <span>Room or PG</span></h1>
                <p>Discover comfortable and affordable living spaces across the city</p>
                <a href="properties.php" class="btn btn-primary">Browse Properties</a>
            </div>
            <div class="search-box">
                <form action="properties.php" method="get">
                    <div class="form-group">
                        <select name="type" id="type">
                            <option value="">All Types</option>
                            <option value="Room">Room</option>
                            <option value="PG">PG</option>
                            <option value="Apartment">Apartment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="city" placeholder="City">
                    </div>
                    <div class="form-group">
                        <input type="number" name="min_price" placeholder="Min Price">
                    </div>
                    <div class="form-group">
                        <input type="number" name="max_price" placeholder="Max Price">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="featured-properties">
        <div class="container">
            <h2>Featured PGs</h2>
            <div class="properties-grid">
                <?php foreach ($properties as $property): ?>
                <div class="property-card">
                    <div class="property-image">
                        <img src="<?php echo UPLOAD_URL . $property['photos'][0]; ?>" alt="<?php echo $property['title']; ?>">
                        <div class="price">₹<?php echo number_format($property['price']); ?>/mo</div>
                    </div>
                    <div class="property-details">
                        <h3><a href="property-details.php?id=<?php echo $property['id']; ?>"><?php echo $property['title']; ?></a></h3>
                        <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo $property['city']; ?>, <?php echo $property['state']; ?></p>
                        <div class="features">
                            <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                            <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                        </div>
                        <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <h2>Featured Rooms</h2>
            <div class="properties-grid">
                <?php foreach ($roomProperties as $property): ?>
                <div class="property-card">
                    <div class="property-image">
                        <img src="<?php echo UPLOAD_URL . $property['photos'][0]; ?>" alt="<?php echo $property['title']; ?>">
                        <div class="price">₹<?php echo number_format($property['price']); ?>/mo</div>
                    </div>
                    <div class="property-details">
                        <h3><a href="property-details.php?id=<?php echo $property['id']; ?>"><?php echo $property['title']; ?></a></h3>
                        <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo $property['city']; ?>, <?php echo $property['state']; ?></p>
                        <div class="features">
                            <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                            <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                        </div>
                        <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="properties.php" class="btn btn-primary">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Search</h3>
                    <p>Find properties that match your preferences and budget</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>View</h3>
                    <p>Check property details, photos, and contact the owner</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Book</h3>
                    <p>Book your stay and make payment securely</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3>Move In</h3>
                    <p>Complete the process and move into your new home</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="testimonial-slider">
                <div class="testimonial">
                    <div class="quote">
                        <i class="fas fa-quote-left"></i>
                        <p>Found a great PG near my college within my budget. The process was so easy!</p>
                    </div>
                    <div class="author">
                        <img src="assets/images/user1.jpg" alt="Rahul Sharma">
                        <h4>Rahul Sharma</h4>
                        <p>Student</p>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="quote">
                        <i class="fas fa-quote-left"></i>
                        <p>As a working professional, I appreciate the verified listings. Saved me a lot of time.</p>
                    </div>
                    <div class="author">
                        <img src="assets/images/user2.jpg" alt="Priya Patel">
                        <h4>Priya Patel</h4>
                        <p>IT Professional</p>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="quote">
                        <i class="fas fa-quote-left"></i>
                        <p>Renting out my extra room was never this easy. Got a tenant within 2 days!</p>
                    </div>
                    <div class="author">
                        <img src="assets/images/user3.jpg" alt="Vikram Singh">
                        <h4>Vikram Singh</h4>
                        <p>Property Owner</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Get In Touch</h3>
                    <p><i class="fas fa-envelope"></i> info@rentrooms.com</p>
                    <p><i class="fas fa-phone"></i> +91 9876543210</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 MG Road, Bangalore, Karnataka - 560001</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <form action="#" method="post">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="subject" placeholder="Subject" required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
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
</body>
</html>
