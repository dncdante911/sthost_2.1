<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Метод не підтримується']);
    exit;
}

$input = trim($_POST['ip'] ?? '');

if (empty($input)) {
    echo json_encode(['error' => 'Введіть IP або домен']);
    exit;
}

// Определяем тип
$isIp = filter_var($input, FILTER_VALIDATE_IP) ? true : false;

// Здесь тестовые данные, позже можно заменить на API
if ($isIp) {
    $result = [
        'type' => 'IP',
        'ip' => $input,
        'country' => 'Ukraine',
        'city' => 'Dnipro',
        'provider' => 'Kyivstar',
        'asn' => 'AS12345'
    ];
} else {
    $ip = gethostbyname($input);
    $result = [
        'type' => 'Domain',
        'domain' => $input,
        'resolved_ip' => $ip,
        'country' => 'Ukraine',
        'server' => 'nginx',
        'ssl' => 'Valid'
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
