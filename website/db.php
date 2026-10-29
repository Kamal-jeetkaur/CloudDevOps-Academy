<?php
/**
 * Database connection using PDO.
 * Credentials come from environment variables set in docker-compose.yml.
 * Tables are created automatically on first connection.
 */

function getConnection(): PDO
{
    $host = getenv('DB_HOST') ?: 'db';
    $name = getenv('DB_NAME') ?: 'student_portal';
    $user = getenv('DB_USER') ?: 'portal_user';
    $pass = getenv('DB_PASS') ?: 'portal_pass123';

    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Retry a few times in case MySQL is still starting up
    $pdo = null;
    $lastError = null;
    for ($i = 0; $i < 10; $i++) {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            break;
        } catch (PDOException $e) {
            $lastError = $e;
            sleep(2);
        }
    }

    if ($pdo === null) {
        throw $lastError;
    }

    initSchema($pdo);
    return $pdo;
}

function initSchema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            track VARCHAR(50) NOT NULL,
            experience VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            rating TINYINT NOT NULL,
            comments TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}