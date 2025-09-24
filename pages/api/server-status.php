<?php
header('Content-Type: application/json');

$location = $_GET['location'] ?? '';

$statuses = [
    'dnipro' => (rand(0, 1) ? 'online' : 'offline'),
    'kyiv'   => (rand(0, 1) ? 'online' : 'offline')
];

if (isset($statuses[$location])) {
    echo json_encode(['status' => $statuses[$location]]);
} else {
    echo json_encode(['status' => 'unknown']);
}
