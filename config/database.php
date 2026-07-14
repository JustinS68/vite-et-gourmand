<?php

class Database
{
    private string $host = 'localhost';
    private string $dbname = 'vite_gourmand';
    private string $username = 'root';
    private string $password = '';
    private PDO $pdo;

    public function __construct()
    {
    $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $this->dbname = $_ENV['DB_DATABASE'] ?? $_ENV['DB_NAME'] ?? getenv('DB_DATABASE') ?? getenv('DB_NAME') ?? 'vite_gourmand';
        $this->username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
        $this->password = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
        
        $url= getenv('MYSQL_URL') ?: getenv('DATABASE_URL')  ?: (_ENV['MYSQL_URL'] ?? $_ENV['DATABASE_URL'] ?? null);
        
        if ($url) {
            $parts = parse_url($url);
            $this->host = $parts['host'] ?? $this->host;
            $this->dbname = ltrim($parts['path'] ?? '', '/') ?: $this->dbname;
            $this->username = $parts['user'] ?? $this->username;
            $this->password = $parts['pass'] ?? $this->password;
        }
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur de connexion : ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}

$database = new Database();
$pdo = $database->getConnection();
