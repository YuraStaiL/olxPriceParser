<?php

namespace Models;

use PDO;

global $root_directory;
$root_directory = $_SERVER['DOCUMENT_ROOT'] ?: '/home/yura/work/parsingOlxPrice';

class Database {
    private static  ?self $instance = null;
    private string  $hostname;
    private string  $username;
    private string  $password;
    private string  $database;
    public  PDO     $connection;

    /**
     * @param array $params
     */
    private function __construct(array $params) {
        $this->hostname     = $params['hostname'];
        $this->username     = $params['username'];
        $this->password     = $params['password'];
        $this->database     = $params['database'];

        $this->connect();
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            require("{$GLOBALS['root_directory']}/database_config.php");
            /**
             * @var array $params
             */
            self::$instance = new self($params);
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public function connect(): void
    {
        $pdo = new PDO(
            "mysql:host=$this->hostname;dbname=$this->database",
            $this->username,
            $this->password
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $pdo;
    }
}