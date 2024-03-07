<?php

namespace system;
require_once 'loadEnv.php';

enum RequestType: string {
    case GET = 'GET';
    case POST = 'POST';
}
const POST_REQUEST = 'POST';

class Database
{
    private $pdo;
    private $params;

    public function __construct()
    {
        loadEnv();
        $this->connect();
        $this->allowCORS();
        $this->getGetParams();
    }

    private function connect()
    {
        $host = $_ENV['DB_HOST'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Failed to connect to DATABASE: " . $e->getMessage());
        }
    }

    public function query(string $sql, ?array $replacements = array(), ?bool $debug = false): array
    {
        if ($debug) {
            echo $sql;
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($replacements as $key => $data) {
            $value = $data['value'];
            $type = $data['type'];
            $stmt->bindValue(":$key", $value, $type);
        }

        if (!$stmt->execute()) {
            throw new \Exception('PDO statement execution failed: ' . $stmt->errorInfo()[2]);
        }

        if ($stmt->columnCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return ['affected_rows' => $stmt->rowCount()];
        }
    }

    public static function arrify(array $ids): string
    {
        return "(" . implode(", ", $ids) . ")";
    }

    public function getIntParam(string $id, mixed $default = null)
    {
        $value = $this->params[$id];
        if (is_null($value)) {
            return $default;
        }
        return intval($value);
    }

    private static function getRequestParams(RequestType $requestType): array {
        return match($requestType) {
            RequestType::GET => $_GET,
            RequestType::POST => $_POST,
        };
    }

    public function getGetParams(): array {
        return $this->params = self::getRequestParams(RequestType::GET);
    }
    public function getPostParams(): array {
        return $this->params = self::getRequestParams(RequestType::POST);
    }


    public function getStringParam(string $id, mixed $default = null)
    {
        $value = $this->params[$id];
        if (is_null($value)) {
            return $default;
        }
        return urldecode($value);
    }

    public static function allowCORS(): void
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    }

    private function execute(?callable $function, string $errorMessage): string {
        if (is_callable($function)) {
            return $function($this);
        } else {
            return $errorMessage;
        }
    }

    function handleRequest(?callable $getFunction = null, ?callable $postFunction = null, ?callable $putFunction = null, ?callable $deleteFunction = null): string {
        $method = $_SERVER['REQUEST_METHOD'];
        $errorMessage = json_encode(["status" => "Error", "message" => "Unsupported request method"]);
        return match ($method) {
            'GET' => $this->execute($getFunction, $errorMessage),
            'POST' => $this->execute($postFunction, $errorMessage),
            'PUT' => $this->execute($putFunction, $errorMessage),
            'DELETE' => $this->execute($deleteFunction, $errorMessage),
            default => $errorMessage,
        };
    }
}
