<?php

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            // ENV-Werte bevorzugen (phpdotenv); Fallback auf legacy config/database.php (CI)
            $host    = env('DB_HOST')   ?: null;
            $port    = env('DB_PORT')   ?: null;
            $dbname  = env('DB_NAME')   ?: null;
            $user    = env('DB_USER')   ?: null;
            $pass    = env('DB_PASS')   ?: null;

            if ($host === null && file_exists(ROOT . '/config/database.php')) {
                $cfg    = require ROOT . '/config/database.php';
                $host   = $cfg['host'];
                $port   = $cfg['port'];
                $dbname = $cfg['dbname'];
                $user   = $cfg['username'];
                $pass   = $cfg['password'];
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
