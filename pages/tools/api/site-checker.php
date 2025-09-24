<?php
header('Content-Type: application/json');

if (!isset($_POST['url']) || empty($_POST['url'])) {
    echo json_encode(['error' => 'URL не вказано']);
    exit;
}

$url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Невірний формат URL']);
    exit;
}

$result = [
    'url' => $url,
    'status' => null,
    'response_time' => null,
    'ssl' => null
];

// Вимір часу
$start = microtime(true);
$headers = @get_headers($url);
$end = microtime(true);

if ($headers) {
    preg_match('{HTTP\/\S*\s(\d{3})}', $headers[0], $match);
    $result['status'] = isset($match[1]) ? (int)$match[1] : null;
    $result['response_time'] = round(($end - $start) * 1000, 2);
} else {
    $result['status'] = 'Недоступний';
    $result['response_time'] = null;
}

// SSL перевірка
$parsed = parse_url($url);
if (isset($parsed['scheme']) && $parsed['scheme'] === 'https') {
    $host = $parsed['host'];
    $ctx = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
    $client = @stream_socket_client("ssl://{$host}:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
    if ($client) {
        $cont = stream_context_get_params($client);
        $cert = openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);
        $validTo = date(DATE_RFC2822, $cert['validTo_time_t']);
        $daysLeft = floor(($cert['validTo_time_t'] - time()) / 86400);
        $result['ssl'] = [
            'valid' => $daysLeft > 0,
            'expires' => $validTo,
            'days_left' => $daysLeft
        ];
    }
}

echo json_encode($result);
