<?php
if (!function_exists('env')) {
    function load_env_file(string $path): void {
        if (!is_file($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if ($key !== '' && getenv($key) === false) putenv($key . '=' . $value);
        }
    }
    function env(string $key, $default = null) {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}
load_env_file(__DIR__ . '/../../.env');
load_env_file(__DIR__ . '/../../../.env');

class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;

    public function __construct() {
        $this->host = (string)env('DB_HOST', '127.0.0.1');
        $this->db_name = (string)env('DB_DATABASE', 'goresidentgo');
        $this->username = (string)env('DB_USERNAME', 'root');
        $this->password = (string)env('DB_PASSWORD', '');
    }

    public function connect(): PDO {
        static $conn = null;
        if ($conn instanceof PDO) return $conn;
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
        $conn = new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $conn;
    }
}