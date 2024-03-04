<?php

use Models\Database;
use Models\Product;
use Models\Subscription;
use Parsing\Api\OlxApi;

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    require_once "{$class_name}.php";
});
require_once "email_message.php";

$subscription = new Subscription(Database::getInstance());
$subscriptionByProduct = $subscription->groupByProduct();
$emailHeaders = [
    'MIME-Version' => 'MIME-Version: 1.0',
    'Content-type' => 'text/html; charset=iso-8859-1'
];
/**
 * @var Subscription $subscription
 */
$products = (new Product())->all();
foreach ($subscriptionByProduct as $productId => $subscriptions) {

    /**
     * @var Product $product
     */
    $product = $products[$productId];
    $api = new OlxApi($productId);
    $newPrice = $api->getPrice();
    $title = $api->getTitle();

    if (
        ($oldPrice = $product->price) !== $newPrice
    ) {
        $message = sprintf(
            $emailMessage,
            $title,
            $title,
            $product->link,
            $oldPrice,
            $newPrice
        );

        $product->update([
            'price' => $newPrice
        ]);
        $notifyEmails = [];
        foreach ($subscriptions as $subscription) {
            $notifyEmails[] = $subscription->email;
        }

        if ($notifyEmails) {
            $notifyEmails = implode(',', $notifyEmails);
            mail(
                $notifyEmails,
                "Ціна оголошення була змінена: $title",
                $message,
                $emailHeaders
            );
        }
    }
}
//echo print_r($productsByEmail, true) . '<br>';