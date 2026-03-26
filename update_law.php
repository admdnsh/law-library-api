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

$chapter         = isset($data['Chapter'])              ? $data['Chapter']              : '';
$originalChapter = isset($data['Original_Chapter'])     ? $data['Original_Chapter']     : $chapter;
$category        = isset($data['Category'])             ? $data['Category']             : '';
$title           = isset($data['Title'])                ? $data['Title']                : '';
$description     = isset($data['Description'])          ? $data['Description']          : '';
$compoundFine    = isset($data['Compound_Fine'])        ? $data['Compound_Fine']        : '';
$secondFine      = isset($data['Second_Compound_Fine']) ? $data['Second_Compound_Fine'] : '';
$thirdFine       = isset($data['Third_Compound_Fine'])  ? $data['Third_Compound_Fine']  : '';
$fourthFine      = isset($data['Fourth_Compound_Fine']) ? $data['Fourth_Compound_Fine'] : '';
$fifthFine       = isset($data['Fifth_Compound_Fine'])  ? $data['Fifth_Compound_Fine']  : '';

if (empty($chapter) || empty($category) || empty($title) || empty($originalChapter)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Original Chapter, Chapter, Category, and Title are required']);
    exit();
}

try {
    // If chapter is changing, ensure the new value is not taken
    if ($chapter !== $originalChapter) {
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM rta_cha_68 WHERE Chapter = :chapter");
        $checkStmt->bindParam(':chapter', $chapter);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'The new chapter value already exists']);
            exit();
        }
    }

    // Ensure original law exists
    $checkOriginal = $conn->prepare("SELECT COUNT(*) FROM rta_cha_68 WHERE Chapter = :originalChapter");
    $checkOriginal->bindParam(':originalChapter', $originalChapter);
    $checkOriginal->execute();
    if ($checkOriginal->fetchColumn() == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Law not found']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE rta_cha_68 SET
        Chapter = :chapter,
        Category = :category,
        Title = :title,
        Description = :description,
        Compound_Fine = :compound_fine,
        Second_Compound_Fine = :second_fine,
        Third_Compound_Fine = :third_fine,
        Fourth_Compound_Fine = :fourth_fine,
        Fifth_Compound_Fine = :fifth_fine,
        updated_at = CURRENT_TIMESTAMP()
        WHERE Chapter = :originalChapter");

    $stmt->bindParam(':chapter',         $chapter);
    $stmt->bindParam(':category',        $category);
    $stmt->bindParam(':title',           $title);
    $stmt->bindParam(':description',     $description);
    $stmt->bindParam(':compound_fine',   $compoundFine);
    $stmt->bindParam(':second_fine',     $secondFine);
    $stmt->bindParam(':third_fine',      $thirdFine);
    $stmt->bindParam(':fourth_fine',     $fourthFine);
    $stmt->bindParam(':fifth_fine',      $fifthFine);
    $stmt->bindParam(':originalChapter', $originalChapter);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Law updated successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
