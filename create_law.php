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

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$chapter          = isset($data['Chapter'])              ? $data['Chapter']              : '';
$category         = isset($data['Category'])             ? $data['Category']             : '';
$title            = isset($data['Title'])                ? $data['Title']                : '';
$description      = isset($data['Description'])          ? $data['Description']          : '';
$compoundFine     = isset($data['Compound_Fine'])        ? $data['Compound_Fine']        : '';
$secondFine       = isset($data['Second_Compound_Fine']) ? $data['Second_Compound_Fine'] : '';
$thirdFine        = isset($data['Third_Compound_Fine'])  ? $data['Third_Compound_Fine']  : '';
$fourthFine       = isset($data['Fourth_Compound_Fine']) ? $data['Fourth_Compound_Fine'] : '';
$fifthFine        = isset($data['Fifth_Compound_Fine'])  ? $data['Fifth_Compound_Fine']  : '';

if (empty($chapter) || empty($category) || empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chapter, Category, and Title are required']);
    exit();
}

try {
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM rta_cha_68 WHERE Chapter = :chapter");
    $checkStmt->bindParam(':chapter', $chapter);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'A law with this chapter already exists']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO rta_cha_68
        (Chapter, Category, Title, Description, Compound_Fine, Second_Compound_Fine, Third_Compound_Fine, Fourth_Compound_Fine, Fifth_Compound_Fine)
        VALUES (:chapter, :category, :title, :description, :compound_fine, :second_fine, :third_fine, :fourth_fine, :fifth_fine)");

    $stmt->bindParam(':chapter',      $chapter);
    $stmt->bindParam(':category',     $category);
    $stmt->bindParam(':title',        $title);
    $stmt->bindParam(':description',  $description);
    $stmt->bindParam(':compound_fine',$compoundFine);
    $stmt->bindParam(':second_fine',  $secondFine);
    $stmt->bindParam(':third_fine',   $thirdFine);
    $stmt->bindParam(':fourth_fine',  $fourthFine);
    $stmt->bindParam(':fifth_fine',   $fifthFine);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Law created successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
