<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = new Database();
$conn = $db->getConnection();

// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone']);
    $aadhar_number = sanitizeInput($_POST['aadhar_number']);
    
    // Handle file uploads
    $aadhar_photo = $user['aadhar_photo'];
    $user_photo = $user['user_photo'];
    
    if (isset($_FILES['aadhar_photo']) && $_FILES['aadhar_photo']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['aadhar_photo'], UPLOAD_PATH);
        if ($upload['success']) {
            $aadhar_photo = $upload['file_name'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (empty($error) && isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['user_photo'], UPLOAD_PATH);
        if ($upload['success']) {
            $user_photo = $upload['file_name'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, aadhar_number = ?, aadhar_photo = ?, user_photo = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $phone, $aadhar_number, $aadhar_photo, $user_photo, $user_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Update session
            $_SESSION['user_name'] = $name;
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// Get user's properties
$properties = [];
$stmt = $conn->prepare("SELECT * FROM properties WHERE owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['photos'] = json_decode($row['photos'], true);
    $properties[] = $row;
}

// Get user's bookings
$bookings = [];
$stmt = $conn->prepare("SELECT b.*, p.title as property_title, p.photos as property_photos FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['property_photos'] = json_decode($row['property_photos'], true);
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
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

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php echo !empty($user['user_photo']) ? UPLOAD_URL . $user['user_photo'] : 'assets/images/default-avatar.jpg'; ?>" alt="Profile Photo">
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><i class="fas fa-id-card"></i> Aadhar: <?php echo htmlspecialchars($user['aadhar_number']); ?></p>
                    <p class="verification-status">
                        <?php if ($user['is_verified']): ?>
                            <span class="verified"><i class="fas fa-check-circle"></i> Verified</span>
                        <?php else: ?>
                            <span class="not-verified"><i class="fas fa-times-circle"></i> Not Verified</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="profile-tabs">
                <button class="tab-btn active" data-tab="edit-profile">Edit Profile</button>
                <button class="tab-btn" data-tab="my-properties">My Properties</button>
                <button class="tab-btn" data-tab="my-bookings">My Bookings</button>
            </div>

            <div class="tab-content active" id="edit-profile">
                <h2>Edit Profile</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form action="profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="aadhar_number">Aadhar Card Number</label>
                            <input type="text" id="aadhar_number" name="aadhar_number" value="<?php echo htmlspecialchars($user['aadhar_number']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Current Aadhar Photo</label>
                            <div class="current-photo">
                                <img src="<?php echo UPLOAD_URL . $user['aadhar_photo']; ?>" alt="Aadhar Photo">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="aadhar_photo">Update Aadhar Photo</label>
                            <input type="file" id="aadhar_photo" name="aadhar_photo" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="user_photo">Update Profile Photo</label>
                            <input type="file" id="user_photo" name="user_photo" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <div class="tab-content" id="my-properties">
                <h2>My Properties</h2>
                <a href="add-property.php" class="btn btn-primary">Add New Property</a>
                <div class="properties-grid">
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
                            </div>
                            <div class="property-actions">
                                <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-secondary">Edit</a>
                                <a href="delete-property.php?id=<?php echo $property['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($properties)): ?>
                        <p>You haven't listed any properties yet. <a href="add-property.php">Add your first property</a>.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-content" id="my-bookings">
                <h2>My Bookings</h2>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-image">
                            <img src="<?php echo UPLOAD_URL . $booking['property_photos'][0]; ?>" alt="<?php echo htmlspecialchars($booking['property_title']); ?>">
                        </div>
                        <div class="booking-details">
                            <h3><?php echo htmlspecialchars($booking['property_title']); ?></h3>
                            <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                            <p><strong>Dates:</strong> <?php echo date('M j, Y', strtotime($booking['start_date'])); ?>
                                <?php if (!empty($booking['end_date'])): ?>
                                    to <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                                <?php else: ?>
                                    (No end date)
                                <?php endif; ?>
                            </p>
                            <p><strong>Amount:</strong> ₹<?php echo number_format($booking['amount']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-badge <?php echo strtolower($booking['status']); ?>">
                                    <?php echo $booking['status']; ?>
                                </span>
                            </p>
                            <p><strong>Payment Status:</strong> 
                                <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                                    <?php echo $booking['payment_status']; ?>
                                </span>
                            </p>
                            <?php if ($booking['status'] == 'Pending'): ?>
                                <div class="booking-actions">
                                    <a href="confirm-booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">Confirm Payment</a>
                                    <a href="cancel-booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger">Cancel</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                        <p>You haven't made any bookings yet. <a href="properties.php">Browse properties</a> to get started.</p>
                    <?php endif; ?>
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
    <script>
        // Tab functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.getAttribute('data-tab');
                
                // Remove active class from all buttons and contents
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                btn.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
