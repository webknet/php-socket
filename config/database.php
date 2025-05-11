<?php
class Database {
    private $pdo;

    public function __construct() {
        $host = '127.0.0.1';
        $dbname = 'todo_app';
        $port = '3306';
        $user = 'root'; // Altere para seu usuário
        $pass = '1234';     // Altere para sua senha

        try {
            $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>
