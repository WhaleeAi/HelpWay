<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    parse_str($raw, $data);
}

$applicationId = isset($data['application_id']) ? (int)$data['application_id'] : 0;
if ($applicationId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Не передан application_id']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE application SET status = :status WHERE id = :id');
    $stmt->execute([
        ':status' => 'closed',
        ':id' => $applicationId
    ]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Не удалось закрыть заявку', 'detail' => $e->getMessage()]);
}
