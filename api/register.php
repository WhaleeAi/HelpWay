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
    parse_str($raw, $parsed);
    $data = is_array($parsed) ? $parsed : $_POST; // fallback на form-data
}

$email    = isset($data['email']) ? trim($data['email']) : '';
$lName    = isset($data['l_name']) ? trim($data['l_name']) : '';
$fName    = isset($data['f_name']) ? trim($data['f_name']) : '';
$city     = isset($data['city']) ? trim($data['city']) : '';
$addressRaw = $data['address'] ?? $data['address'] ?? '';
$address  = trim((string)$addressRaw);
$password = isset($data['password']) ? (string)$data['password'] : '';
$role     = isset($data['role']) ? trim($data['role']) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Некорректный email']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(['error' => 'Пароль должен быть не короче 6 символов']);
    exit;
}

if ($lName === '' || $fName === '' || $city === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Заполните все поля']);
    exit;
}
if ($address === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Укажите адрес']);
    exit;
}

function geocodeAddress(string $address): ?array {
    $apiKey = '0ef553e7-67a5-434b-9c61-f75cca415b38';
    // API требует lang и format, используем v1 как в инструкции
    $query = urlencode($address);

    $fetch = function (string $url): ?string {
        $context = stream_context_create([
            'http' => [
                'timeout' => 6,
                'ignore_errors' => true,
            ],
            'https' => [
                'timeout' => 6,
                'ignore_errors' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $resp = @file_get_contents($url, false, $context);
        if ($resp === false) {
            return null;
        }
        return $resp;
    };

    $endpoints = [
        "https://geocode-maps.yandex.ru/v1/?apikey={$apiKey}&geocode={$query}&format=json&lang=ru_RU&results=1",
        "https://geocode-maps.yandex.ru/1.x/?apikey={$apiKey}&geocode={$query}&format=json&lang=ru_RU&results=1",
        "http://geocode-maps.yandex.ru/v1/?apikey={$apiKey}&geocode={$query}&format=json&lang=ru_RU&results=1",
        "http://geocode-maps.yandex.ru/1.x/?apikey={$apiKey}&geocode={$query}&format=json&lang=ru_RU&results=1",
    ];

    foreach ($endpoints as $url) {
        $resp = $fetch($url);
        if (!$resp) continue;
        $data = json_decode($resp, true);
        if (!is_array($data)) continue;
        $member = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'] ?? null;
        if (!$member) continue;
        [$lon, $lat] = array_map('trim', explode(' ', $member));
        return ['lat' => (float)$lat, 'lon' => (float)$lon];
    }

    return null;
}

try {
    // Проверка уникальности email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        http_response_code(409);
        echo json_encode(['error' => 'Пользователь с таким email уже существует']);
        exit;
    }

    $pdo->beginTransaction();

    // Вставляем пользователя с явным role (если роль не пришла, ставим client)
    $roleValue = $role !== '' ? $role : 'client';

    // Геокодирование адреса
    $fullAddress = stripos($address, $city) !== false ? $address : ($city . ', ' . $address);
    $coords = geocodeAddress($fullAddress);
    if (!$coords) {
        http_response_code(422);
        echo json_encode(['error' => 'Не удалось геокодировать адрес', 'detail' => $fullAddress]);
        exit;
    }

    $insertUser = $pdo->prepare('INSERT INTO users (email, l_name, f_name, city, address, role, lat, lon) VALUES (:email, :l_name, :f_name, :city, :address, :role, :lat, :lon)');
    $insertUser->execute([
        ':email' => $email,
        ':l_name' => $lName,
        ':f_name' => $fName,
        ':city' => $city,
        ':address' => $address,
        ':role' => $roleValue,
        ':lat' => $coords['lat'],
        ':lon' => $coords['lon'],
    ]);

    $userId = (int)$pdo->lastInsertId();
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $insertPass = $pdo->prepare("INSERT INTO password_hashes (user_id, password_hash) VALUES (:user_id, :password_hash)");
    $insertPass->execute([
        ':user_id' => $userId,
        ':password_hash' => $passwordHash,
    ]);

    $pdo->commit();

    echo json_encode(['ok' => true, 'user_id' => $userId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
}
