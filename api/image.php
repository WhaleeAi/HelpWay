<?php
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

try {
    $stmt = $pdo->prepare('SELECT photo FROM images WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $photo = $stmt->fetchColumn();

    if ($photo === false) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }

    // Пытаемся угадать тип: пусть будет jpeg по умолчанию
    header('Content-Type: image/jpeg');
    echo $photo;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
