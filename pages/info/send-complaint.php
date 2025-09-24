<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$type || !$subject || !$message) {
        echo json_encode(["success" => false, "message" => "Всі поля обов’язкові"]);
        exit;
    }

    $to = "complaint@sthost.pro";
    $fullSubject = "[{$type}] {$subject}";
    $body = "Ім’я: {$name}\nEmail: {$email}\nТип: {$type}\n\nПовідомлення:\n{$message}";
    $headers = "From: no-reply@sthost.pro\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8";

    if (mail($to, $fullSubject, $body, $headers)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Не вдалося надіслати повідомлення"]);
    }
}
