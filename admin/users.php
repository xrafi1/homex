<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = new Database();
$conn = $db->getConnection();

// Handle user actions (verify/delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'verify') {
        $conn->query("UPDATE users SET is_verified = 1 WHERE id = $id");
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM users WHERE id = $id");
    }
    
    // Redirect to avoid form resubmission
    redirect('users.php');
}

// Get all users
$users = [];
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
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
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="active"><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
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
                <h1>Manage Users</h1>
            </div>
            <div class="header-right">
                <div class="admin-user">
                    <img src="<?php echo !empty($_SESSION['user_photo']) ? '../' . UPLOAD_URL . $_SESSION['user_photo'] : '../assets/images/default-avatar.jpg'; ?>" alt="Admin">
                    <span><?php echo $_SESSION['user_name']; ?></span>
                </div>
            </div>
        </header>

        <!-- Users Content -->
        <div class="content-panel">
            <div class="panel-header">
                <h2>All Users</h2>
                <div class="search-box">
                    <input type="text" placeholder="Search users...">
                    <button><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Aadhar</th>
                                <th>Verified</th>
                                <th>Admin</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="user-info">
                                        <img src="<?php echo !empty($user['user_photo']) ? '../' . UPLOAD_URL . $user['user_photo'] : '../assets/images/default-avatar.jpg'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo !empty($user['aadhar_number']) ? htmlspecialchars($user['aadhar_number']) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($user['is_verified']): ?>
                                        <span class="status-badge verified">Yes</span>
                                    <?php else: ?>
                                        <span class="status-badge not-verified">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="status-badge admin">Admin</span>
                                    <?php else: ?>
                                        <span class="status-badge user">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if (!$user['is_verified']): ?>
                                            <a href="users.php?action=verify&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success" title="Verify"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
                                        <?php if (!$user['is_admin']): ?>
                                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-footer">
                <div class="table-info">
                    Showing <?php echo count($users); ?> of <?php echo count($users); ?> users
                </div>
                <div class="pagination">
                    <button disabled><i class="fas fa-chevron-left"></i></button>
                    <span>1</span>
                    <button disabled><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
