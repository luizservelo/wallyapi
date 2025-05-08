<?php

namespace App\Core;

use PDO;
use PDOException;

final class Connect
{
    private static ?Connect $instance = null;
    private ?PDO $connection = null;
    private array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $config = require __DIR__ . '/../../config/database.php';
                $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                $this->connection = new PDO($dsn, $config['username'], $config['password'], $this->options);
            } catch (PDOException $exception) {
                http_response_code(500);
                die(json_encode([
                    'status' => 'error',
                    'message' => 'Erro ao conectar com o banco de dados'
                ]));
            }
        }
        return $this->connection;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
} 