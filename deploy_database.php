<?php
spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    require_once "{$class_name}.php";
});
$products = "CREATE TABLE IF NOT EXISTS products (
    olx_product int NOT NULL PRIMARY KEY,
    link VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    price int NOT NULL,
    UNIQUE(olx_product)
)";

$subscriptions = "CREATE TABLE IF NOT EXISTS subscriptions (
    product int NOT NULL,
    email VARCHAR(255) NOT NULL,
    CONSTRAINT PRODUCT_EMAIL PRIMARY KEY (product,email),
    FOREIGN KEY (product)
        REFERENCES products(olx_product)
        ON DELETE CASCADE
    )";

$emailConfirmation = "CREATE TABLE IF NOT EXISTS email_confirmation (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    code VARCHAR(100),
    confirmation_at TIMESTAMP
    )";
/**
 * @var PDO $pdo
 */
try {
    $pdo = \Models\Database::getInstance()->connection;
    $pdo->exec($products);
    $pdo->exec($subscriptions);
    $pdo->exec($emailConfirmation);
    echo "\nSuccess\n";
} catch (PDOException $exception) {
    echo "Error: " . $exception->getMessage();
}