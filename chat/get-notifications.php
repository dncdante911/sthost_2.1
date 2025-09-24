<?php
header('Content-Type: application/json');

// Отладочная информация
$debug = [];
$debug[] = 'Скрипт запущен: ' . date('Y-m-d H:i:s');

$log_file = __DIR__ . '/chat_log.txt';
$debug[] = 'Путь к логу: ' . $log_file;
$debug[] = 'Файл существует: ' . (file_exists($log_file) ? 'да' : 'нет');

$messages = [];

// Добавляем тестовое сообщение
$messages[] = [
    'time' => date('Y-m-d H:i:s'),
    'urgent' => false,
    'message' => 'Тестовое сообщение - API работает!',
    'debug' => $debug
];

// Если есть лог файл, читаем его
if (file_exists($log_file)) {
    $content = file_get_contents($log_file);
    $debug[] = 'Размер лог файла: ' . strlen($content) . ' байт';
    
    if (!empty($content)) {
        $lines = explode("\n", $content);
        $debug[] = 'Строк в логе: ' . count($lines);
        
        foreach (array_reverse($lines) as $line) {
            if (trim($line) && strpos($line, 'Message:') !== false) {
                $messages[] = [
                    'time' => date('Y-m-d H:i:s'),
                    'urgent' => strpos($line, 'URGENT') !== false,
                    'message' => 'Из лога: ' . substr($line, 0, 100)
                ];
            }
        }
    }
}

echo json_encode([
    'messages' => $messages,
    'debug' => $debug
]);
?>