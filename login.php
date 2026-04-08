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
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE Username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Support bcrypt hashes and plain-text (legacy)
        $passwordValid = false;
        if (isset($user['Password'])) {
            if (strlen($user['Password']) >= 60 && $user['Password'][0] === '$') {
                $passwordValid = password_verify($password, $user['Password']);
            } else {
                $passwordValid = ($user['Password'] === $password);
            }
        }

        if ($passwordValid) {
            $role = isset($user['Role']) ? $user['Role'] : (isset($user['role']) ? $user['role'] : 'officer');
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user'    => [
                    'Username' => $user['Username'],
                    'role'     => $role,
                    'isAdmin'  => ($role === 'admin') ? 1 : 0,
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
