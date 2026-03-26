<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_config.php';

$chapter = isset($_GET['chapter']) ? $_GET['chapter'] : '';

if (empty($chapter)) {
    http_response_code(400);
    echo json_encode(['error' => 'Chapter parameter is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM rta_cha_68 WHERE Chapter = :chapter");
    $stmt->bindParam(':chapter', $chapter);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Law not found']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
