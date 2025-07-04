<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $aadhar_number = sanitizeInput($_POST['aadhar_number']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($aadhar_number)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Check if Aadhar number already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE aadhar_number = ?");
            $stmt->bind_param("s", $aadhar_number);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Aadhar number already registered.";
            } else {
                // Handle file uploads
                $aadhar_photo = '';
                $user_photo = '';
                
                // Upload Aadhar photo
                if (isset($_FILES['aadhar_photo']) && $_FILES['aadhar_photo']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['aadhar_photo'], UPLOAD_PATH);
                    if ($upload['success']) {
                        $aadhar_photo = $upload['file_name'];
                    } else {
                        $error = $upload['message'];
                    }
                } else {
                    $error = "Aadhar photo is required.";
                }
                
                // Upload user photo (optional)
                if (empty($error) {
                    if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] === UPLOAD_ERR_OK) {
                        $upload = uploadFile($_FILES['user_photo'], UPLOAD_PATH);
                        if ($upload['success']) {
                            $user_photo = $upload['file_name'];
                        } else {
                            $error = $upload['message'];
                        }
                    }
                }
                
                if (empty($error)) {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user into database
                    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, aadhar_number, aadhar_photo, user_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $name, $email, $phone, $hashed_password, $aadhar_number, $aadhar_photo, $user_photo);
                    
                    if ($stmt->execute()) {
                        $success = "Registration successful! Please login.";
                        // In a real application, you would send a verification email here
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
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

    <!-- Register Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-form">
                <h2>Create an Account</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                <form action="register.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (min 8 characters)</label>
                        <input type="password" id="password" name="password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label for="aadhar_number">Aadhar Card Number</label>
                        <input type="text" id="aadhar_number" name="aadhar_number" required>
                    </div>
                    <div class="form-group">
                        <label for="aadhar_photo">Aadhar Card Photo (Front)</label>
                        <input type="file" id="aadhar_photo" name="aadhar_photo" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="user_photo">Your Photo (Optional)</label>
                        <input type="file" id="user_photo" name="user_photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                    <div class="form-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
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
