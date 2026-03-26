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

$page     = isset($_GET['page'])     ? intval($_GET['page'])     : 1;
$limit    = isset($_GET['limit'])    ? intval($_GET['limit'])    : 10;
$offset   = ($page - 1) * $limit;
$search   = isset($_GET['search'])   ? $_GET['search']           : '';
$category = isset($_GET['category']) ? $_GET['category']         : '';

try {
    $query  = "SELECT * FROM rta_cha_68 WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (Chapter LIKE :search OR Title LIKE :search OR Description LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if (!empty($category)) {
        $query .= " AND Category = :category";
        $params[':category'] = $category;
    }

    $query .= " ORDER BY Chapter LIMIT :limit OFFSET :offset";
    $params[':limit']  = $limit;
    $params[':offset'] = $offset;

    $stmt = $conn->prepare($query);

    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
