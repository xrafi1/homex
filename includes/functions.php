<?php
require_once 'config.php';
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    $db = new Database();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $db->escape($data);
}

function uploadFile($file, $targetDir) {
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;
    
    $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    
    // Check if file is an actual image
    $check = getimagesize($file['tmp_name']);
    if($check === false && !in_array($imageFileType, ['pdf'])) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File is too large.'];
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & PDF files are allowed.'];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'file_name' => $fileName];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

function getProperties($filters = []) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT p.*, u.name as owner_name FROM properties p JOIN users u ON p.owner_id = u.id WHERE 1=1";
    
    if (!empty($filters['type'])) {
        $type = $conn->real_escape_string($filters['type']);
        $sql .= " AND p.type = '$type'";
    }
    
    if (!empty($filters['city'])) {
        $city = $conn->real_escape_string($filters['city']);
        $sql .= " AND p.city LIKE '%$city%'";
    }
    
    if (!empty($filters['min_price'])) {
        $min_price = (float)$filters['min_price'];
        $sql .= " AND p.price >= $min_price";
    }
    
    if (!empty($filters['max_price'])) {
        $max_price = (float)$filters['max_price'];
        $sql .= " AND p.price <= $max_price";
    }
    
    if (!empty($filters['bedrooms'])) {
        $bedrooms = (int)$filters['bedrooms'];
        $sql .= " AND p.bedrooms = $bedrooms";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $result = $conn->query($sql);
    $properties = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['photos'] = json_decode($row['photos'], true);
            $properties[] = $row;
        }
    }
    
    return $properties;
}

function getPropertyById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $id = $conn->real_escape_string($id);
    $sql = "SELECT p.*, u.name as owner_name, u.phone as owner_phone FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = $id";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
        $property['photos'] = json_decode($property['photos'], true);
        return $property;
    }
    
    return null;
}
?>
