<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit();
}

$username    = isset($_POST['username'])     ? trim($_POST['username'])     : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password']       : '';

if (empty($username) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and new password are required']);
    exit();
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

try {
    $stmt = $conn->prepare("UPDATE users SET Password = :password WHERE Username = :username");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
