<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get filters from query parameters
$filters = [];
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $filters['type'] = $_GET['type'];
}
if (isset($_GET['city']) && !empty($_GET['city'])) {
    $filters['city'] = $_GET['city'];
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = $_GET['min_price'];
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = $_GET['max_price'];
}
if (isset($_GET['bedrooms']) && !empty($_GET['bedrooms'])) {
    $filters['bedrooms'] = $_GET['bedrooms'];
}

// Get properties based on filters
$properties = getProperties($filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - <?php echo SITE_NAME; ?></title>
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

    <!-- Properties Section -->
    <section class="properties-page">
        <div class="container">
            <div class="properties-header">
                <h1>Available Properties</h1>
                <?php if (isLoggedIn()): ?>
                    <a href="add-property.php" class="btn btn-primary">List Your Property</a>
                <?php endif; ?>
            </div>
            
            <div class="properties-filter">
                <form action="properties.php" method="get">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="type">Property Type</label>
                            <select name="type" id="type">
                                <option value="">All Types</option>
                                <option value="Room" <?php echo isset($filters['type']) && $filters['type'] == 'Room' ? 'selected' : ''; ?>>Room</option>
                                <option value="PG" <?php echo isset($filters['type']) && $filters['type'] == 'PG' ? 'selected' : ''; ?>>PG</option>
                                <option value="Apartment" <?php echo isset($filters['type']) && $filters['type'] == 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="city">Location</label>
                            <input type="text" name="city" id="city" placeholder="City" value="<?php echo isset($filters['city']) ? htmlspecialchars($filters['city']) : ''; ?>">
                        </div>
                        <div class="filter-group">
                            <label for="bedrooms">Bedrooms</label>
                            <select name="bedrooms" id="bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo isset($filters['bedrooms']) && $filters['bedrooms'] == 1 ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?php echo isset($filters['bedrooms']) && $filters['bedrooms'] == 2 ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?php echo isset($filters['bedrooms']) && $filters['bedrooms'] == 3 ? 'selected' : ''; ?>>3+</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="min_price">Min Price (₹)</label>
                            <input type="number" name="min_price" id="min_price" placeholder="Min" value="<?php echo isset($filters['min_price']) ? htmlspecialchars($filters['min_price']) : ''; ?>">
                        </div>
                        <div class="filter-group">
                            <label for="max_price">Max Price (₹)</label>
                            <input type="number" name="max_price" id="max_price" placeholder="Max" value="<?php echo isset($filters['max_price']) ? htmlspecialchars($filters['max_price']) : ''; ?>">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="properties.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="properties-grid">
                <?php if (!empty($properties)): ?>
                    <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <div class="property-image">
                            <img src="<?php echo UPLOAD_URL . $property['photos'][0]; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="price">₹<?php echo number_format($property['price']); ?>/mo</div>
                            <div class="status <?php echo $property['is_available'] ? 'available' : 'rented'; ?>">
                                <?php echo $property['is_available'] ? 'Available' : 'Rented'; ?>
                            </div>
                        </div>
                        <div class="property-details">
                            <h3><a href="property-details.php?id=<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['title']); ?></a></h3>
                            <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['city']); ?>, <?php echo htmlspecialchars($property['state']); ?></p>
                            <div class="features">
                                <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                                <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                                <span><i class="fas fa-home"></i> <?php echo $property['type']; ?></span>
                            </div>
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No properties found matching your criteria</h3>
                        <p>Try adjusting your search filters or <a href="properties.php">view all properties</a>.</p>
                    </div>
                <?php endif; ?>
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
