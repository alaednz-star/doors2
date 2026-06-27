<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $c = Config::get('database');

        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Managed MySQL hosts (Aiven, etc.) require TLS. Enable it when
        // DB_SSL is set; point DB_SSL_CA at a CA bundle to verify, or leave
        // it unset to connect over TLS without local CA verification.
        if (getenv('DB_SSL')) {
            $ca = getenv('DB_SSL_CA');
            if ($ca) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            } else {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
        }

        try {
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], $options);
        } catch (PDOException $e) {
            error_log('[DB] ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Service unavailable.']);
            exit;
        }

        return self::$pdo;
    }
}
