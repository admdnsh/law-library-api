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

$username = isset($_POST['username']) ? trim($_POST['username']) : '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit();
}

try {
    // Prevent deleting the last admin
    $checkStmt = $conn->prepare("SELECT Role FROM users WHERE Username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    if ($user['Role'] === 'admin') {
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE Role = 'admin'");
        $countStmt->execute();
        $adminCount = (int)$countStmt->fetchColumn();

        if ($adminCount <= 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete the last admin account']);
            exit();
        }
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE Username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
