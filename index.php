<?php
header("Content-Type: application/json");
echo json_encode([
    'status'  => 'ok',
    'message' => 'Law Library API is running',
    'version' => '2.0'
]);
