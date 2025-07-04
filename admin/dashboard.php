<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = new Database();
$conn = $db->getConnection();

// Get stats for dashboard
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$properties_count = $conn->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'];
$bookings_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(amount) as total FROM bookings WHERE payment_status = 'Paid'")->fetch_assoc()['total'];

// Get recent bookings
$recent_bookings = [];
$result = $conn->query("SELECT b.*, u.name as user_name, p.title as property_title FROM bookings b JOIN users u ON b.user_id = u.id JOIN properties p ON b.property_id = p.id ORDER BY b.created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_bookings[] = $row;
}

// Get recent users
$recent_users = [];
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-logo">
            <a href="dashboard.php">Rent<span>Rooms</span> Admin</a>
        </div>
        <ul class="admin-menu">
            <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="properties.php"><i class="fas fa-home"></i> Properties</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="admin-user">
                    <img src="<?php echo !empty($_SESSION['user_photo']) ? '../' . UPLOAD_URL . $_SESSION['user_photo'] : '../assets/images/default-avatar.jpg'; ?>" alt="Admin">
                    <span><?php echo $_SESSION['user_name']; ?></span>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $users_count; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon properties">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $properties_count; ?></h3>
                        <p>Total Properties</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bookings">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $bookings_count; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($revenue ? $revenue : 0); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-row">
                <div class="dashboard-col">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Bookings</h3>
                            <a href="bookings.php" class="btn btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Property</th>
                                            <th>Dates</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['property_title']); ?></td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($booking['start_date'])); ?>
                                                <?php if ($booking['end_date']): ?>
                                                    - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>₹<?php echo number_format($booking['amount']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($booking['status']); ?>">
                                                    <?php echo $booking['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-col">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Users</h3>
                            <a href="users.php" class="btn btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Verified</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td>
                                                <?php if ($user['is_verified']): ?>
                                                    <span class="status-badge verified">Yes</span>
                                                <?php else: ?>
                                                    <span class="status-badge not-verified">No</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
