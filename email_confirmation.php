<?php

use Models\EmailConfirmation;

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    require_once "{$class_name}.php";
});

$code   = $_GET['code'] ?? null;
$email  = $_GET['email'] ?? null;

if ($code && $email) {
    try {
        $res = EmailConfirmation::updateWhere(
            [
                'email' => $email,
                'code' => $code
            ],
            [
                'confirmation_at' => date('Y-m-d H:i:s', time()),
                'code' => null
            ]
        );

    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo $msg;
    }

    if ($res) {
        echo "успішно";
    } else {
        echo "помилка";
    }
} else {
    echo "Вже активовано";
}
