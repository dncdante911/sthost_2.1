<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        echo json_encode(["success" => false, "message" => "Всі поля обов’язкові"]);
        exit;
    }

    $to = "support@sthost.pro";
    $subject = "Нове повідомлення з форми контактів";
    $body = "Ім’я: {$name}\nEmail: {$email}\n\nПовідомлення:\n{$message}";
    $headers = "From: no-reply@sthost.pro\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8";

    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Не вдалося надіслати повідомлення"]);
    }
}
