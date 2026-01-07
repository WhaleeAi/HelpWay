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
    $data = $_POST;
}

$userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$type = isset($data['type']) ? trim($data['type']) : '';
$comment = isset($data['comment']) ? trim($data['comment']) : '';
$startAddress = isset($data['start_address']) ? trim($data['start_address']) : '';
$endId = isset($data['end_id']) ? (int)$data['end_id'] : 0;
$endType = isset($data['end_type']) ? trim($data['end_type']) : '';
$goDate = isset($data['go_date']) ? trim($data['go_date']) : '';

if ($userId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректный пользователь']);
    exit;
}

if ($type === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Укажите тип ограничений']);
    exit;
}

if ($startAddress === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Укажите адрес клиента']);
    exit;
}

if ($endId <= 0 || !in_array($endType, ['mfc', 'polyclinic', 'uprava'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректное учреждение']);
    exit;
}

if ($goDate === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Укажите дату визита']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO application (user_id, type, comment, start_address, end_id, go_date, end_type) VALUES (:user_id, :type, :comment, :start_address, :end_id, :go_date, :end_type)');
    $stmt->execute([
        ':user_id' => $userId,
        ':type' => $type,
        ':comment' => $comment !== '' ? $comment : null,
        ':start_address' => $startAddress,
        ':end_id' => $endId,
        ':go_date' => $goDate,
        ':end_type' => $endType,
    ]);

    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}
