<?php

use Exceptions\EmailConfirmationError;
use Models\EmailConfirmation;
use Models\Product;
use Models\Subscription;
use Parsing\Api\OlxApi;
use Parsing\Helpers\Parser;

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    require_once "{$class_name}.php";
});

try {
    $mockLink = 'https://www.olx.ua/d/uk/obyavlenie/srochno-prodam-nestandartnuyu-3-h-komnatnuyu-kvartiru-v-pridneprovske-IDSGm6I.html?reason=hp%7Cpromoted';
    $mockEmail = 't@t.com';
    $link = $_POST['url'] ?? $mockLink;
    $email = $_POST['email'] ?? $mockEmail;
    $email = preg_replace('/\?.*$/', '', $email);
    $parser = new Parser($link);

    $emailConfirmation = (new EmailConfirmation())->getRecord($email);

    if (!$emailConfirmation) {
        $confirmationCode = md5(rand());
        $confirmationLink = "http://localhost:8080/email_confirmation.php?code=$confirmationCode&email=$email";

        $created = EmailConfirmation::create([
            'email'     => $email,
            'code'      => $confirmationCode,
        ]);
        if ($created) {
            mail(
                $email,
                "Olx price checker - confirmation your email",
                "go to the link: $confirmationLink"
            );

            echo "Підтвердіть свою пошту. На Вашу електрону пошту було вислано лист-підтверження";
        } else {
            throw new EmailConfirmationError('запис не створено');
        }

        return ['status' => 200];
    } else if (!$emailConfirmation->confirmation_at) {
        echo "Підтвердіть свою пошту. На Вашу електрону пошту вже було вислано лист-підтверження";
        return ['status' => 200];
    }

    $html = $parser->parse();
    $price = $html->getPrice();
    $productId = $html->getProductId();
    $title = $html->getTitle();

    $product = new Product();
    $productRecord = $product->getRecord($productId);

    $olxApi = new OlxApi($productId);
    if (!$price) {
        if ($productId) {
            $price = $olxApi->getPrice();
        }
    }

    if (!$title) {
        if ($productId) {
            $title = $olxApi->getTitle();
        }
    }

    if (!$productRecord) {
        $data = [
            'olx_product' => $productId,
            'link' => $link,
            'title' => $title,
            'price' => $price
        ];
        $productRecord = Product::create($data);
    }

    if ($productRecord) {
        $subscription = new Subscription();
        $subscription = $subscription->getRecord($productRecord->olx_product, $email);
        if (!$subscription) {
            $data = [
                'product'   => $productRecord->olx_product,
                'email'     => $email,
            ];

            Subscription::create($data);
            echo "Підписка успішно оформлена:<br>";
            echo "Посилання на оголошення: $link<br>";
            echo "Email для повідомлень: $email<br>";
        } else {
            echo "Ви вже підписались на цей товар<br>";
        }
    }

    return ['status' => 200, 'data' => 'success'];
} catch (Exception $exception) {
    $msg = $exception->getMessage();
    $trace = print_r($exception->getTrace(), true);
    echo "Сталася помилка: $msg <br>";
    echo "trace: $trace <br>";
}