<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function userHasColumn(PDO $pdo, string $column): bool {
    static $cache = [];
    if (array_key_exists($column, $cache)) {
        return $cache[$column];
    }
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE :col");
    $stmt->execute([':col' => $column]);
    $cache[$column] = (bool)$stmt->fetchColumn();
    return $cache[$column];
}

if ($method === 'GET') {
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($userId <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Некорректный пользователь']);
        exit;
    }
    try {
        $selectCols = ['id', 'email', 'l_name', 'f_name', 'city', 'role'];
        if (userHasColumn($pdo, 'address')) {
            $selectCols[] = 'address';
        }
        $sql = 'SELECT ' . implode(', ', $selectCols) . ' FROM users WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Пользователь не найден']);
            exit;
        }
        echo json_encode($row);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $email = isset($data['email']) ? trim($data['email']) : '';
    $lName = isset($data['l_name']) ? trim($data['l_name']) : '';
    $fName = isset($data['f_name']) ? trim($data['f_name']) : '';
    $city = isset($data['city']) ? trim($data['city']) : null;
    $role = isset($data['role']) ? trim($data['role']) : null;
    $address = isset($data['address']) ? trim($data['address']) : null;

    if ($userId <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Некорректный пользователь']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['error' => 'Некорректный email']);
        exit;
    }
    if ($lName === '' || $fName === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Имя и фамилия обязательны']);
        exit;
    }
    if ($role !== null && !in_array($role, ['client', 'volunteer'], true)) {
        http_response_code(422);
        echo json_encode(['error' => 'Некорректная роль']);
        exit;
    }

    try {
        // Проверка уникальности email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute([':email' => $email, ':id' => $userId]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Пользователь с таким email уже существует']);
            exit;
        }

        $fields = [
            'email' => $email,
            'l_name' => $lName,
            'f_name' => $fName,
            'city' => $city !== '' ? $city : null,
        ];
        if (userHasColumn($pdo, 'address')) {
            $fields['address'] = $address !== '' ? $address : null;
        }
        if ($role !== null) {
            $fields['role'] = $role;
        }

        $setParts = [];
        $params = [];
        foreach ($fields as $col => $val) {
            $setParts[] = "{$col} = :{$col}";
            $params[":{$col}"] = $val;
        }
        $params[':id'] = $userId;

        $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Вернуть обновленные данные
        $selectCols = ['id', 'email', 'l_name', 'f_name', 'city', 'role'];
        if (userHasColumn($pdo, 'address')) {
            $selectCols[] = 'address';
        }
        $stmt = $pdo->prepare('SELECT ' . implode(', ', $selectCols) . ' FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();

        echo json_encode($row);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка сервера', 'detail' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
