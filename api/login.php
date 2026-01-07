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

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? (string)$data['password'] : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Неверный логин или пароль']);
    exit;
}

try {
$stmt = $pdo->prepare('SELECT id, role FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$row = $stmt->fetch();

$userId = $row['id'] ?? null;
$role = $row['role'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Неверный логин или пароль']);
    exit;
    }

// Определяем колонку с хешем (password_hash или hash)
$hashCol = $pdo->query("SHOW COLUMNS FROM password_hashes LIKE 'password_hash'")->fetchColumn() ? 'password_hash' : null;
if (!$hashCol && $pdo->query("SHOW COLUMNS FROM password_hashes LIKE 'hash'")->fetchColumn()) {
    $hashCol = 'hash';
}
if (!$hashCol) {
    http_response_code(500);
    echo json_encode(['error' => 'В таблице password_hashes нет колонки password_hash или hash']);
    exit;
}

$stmt = $pdo->prepare("SELECT {$hashCol} FROM password_hashes WHERE user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $userId]);
$hash = $stmt->fetchColumn();

if (!$hash || !password_verify($password, $hash)) {
    http_response_code(401);
    echo json_encode(['error' => 'Неверный логин или пароль']);
    exit;
}

echo json_encode(['ok' => true, 'user_id' => (int)$userId, 'role' => $role]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}
