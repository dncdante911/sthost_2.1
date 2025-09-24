<?php
if ($_POST['message']) {
    $message = strip_tags($_POST['message']);
    $page = strip_tags($_POST['page'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $time = date('d.m.Y H:i:s');
    
    $email_body = "
Новое сообщение с сайта:
Время: $time
Страница: $page
IP: $ip
Сообщение: $message
";
    
    mail('support@sthost.pro', 'Сообщение с чата - sthost.pro', $email_body, 'From: noreply@sthost.pro');
}
?>