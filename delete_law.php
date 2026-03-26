<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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

$data    = json_decode(file_get_contents('php://input'), true);
$chapter = isset($data['Chapter']) ? $data['Chapter'] : '';

if (empty($chapter)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chapter is required']);
    exit();
}

try {
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM rta_cha_68 WHERE Chapter = :chapter");
    $checkStmt->bindParam(':chapter', $chapter);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Law not found']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM rta_cha_68 WHERE Chapter = :chapter");
    $stmt->bindParam(':chapter', $chapter);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Law deleted successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
