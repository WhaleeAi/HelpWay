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
$volunteerId = isset($data['volunteer_id']) ? (int)$data['volunteer_id'] : 0;
$answer = isset($data['answer']) ? trim($data['answer']) : 'Готов помочь';
$status = isset($data['status']) ? trim($data['status']) : 'pending';

if ($applicationId <= 0 || $volunteerId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Не хватает данных (application_id, volunteer_id)']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO application_volunteer (application_id, volunteer_id, answer, status, created_at) VALUES (:app, :vol, :answer, :status, NOW())');
    $stmt->execute([
        ':app' => $applicationId,
        ':vol' => $volunteerId,
        ':answer' => $answer,
        ':status' => $status
    ]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Не удалось сохранить отклик', 'detail' => $e->getMessage()]);
}
