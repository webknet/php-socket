<?php
require 'vendor/autoload.php';
require 'config/database.php';

use WebSocket\Client;
use WebSocket\Message\Text;
use Phrity\Net\Uri;

$db = (new Database())->getPdo();

if (!class_exists('WebSocket\Client')) {
    die('Classe Phrity\WebSocket\Client não encontrada. Verifique a instalação do pacote.');
};

try {
   
    $result = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $sqlQuery = '';

        $result['action'] = $input['action'];
        $result['clientId'] = $input['clientId'];
        
        switch ($input['action']) {
            case "insert":
                $sqlQuery = "INSERT INTO tasks (text) VALUES (:text)";                    
                $dbQuery = $db->prepare($sqlQuery);
                $dbQuery->execute(['text' => $input['text']]);
               
                $task = [
                    'id' => $db->lastInsertId(),
                    'text' => $input['text'],
                    'completed' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $result['data'] = $task;
                
                // Notificar via WebSocket
                notifyWebSocket($result);

                break;
            case "update":
                $sqlQuery = "UPDATE tasks SET completed= :completed WHERE id= :id";
                $dbQuery = $db->prepare($sqlQuery);
                $dbQuery->execute(['completed' => $input['completed'], 'id' => $input['id']]);
                
                $task = ['id' => $input['id'], 'completed' => $input['completed']];

                $result['data'] = $task;
                
                // Notificar via WebSocket
                notifyWebSocket($result);
                break;
            case "delete":
                $sqlQuery = "DELETE FROM tasks WHERE id= :id";
                $dbQuery = $db->prepare($sqlQuery);
                $dbQuery->execute(['id' => $input['id']]);
                
                $task = ['id' => $input['id']];

                $result['data'] = $task;
                
                // Notificar via WebSocket
                notifyWebSocket($result);
                break;
            case "get":
                $sqlQuery = "SELECT * FROM tasks";
                $res = $db->query($sqlQuery);
                $result['data'] = $res->fetchAll(PDO::FETCH_ASSOC);

                break;
            case "query":
                $result["status"] = "error";
                $result["message"] = $e->getMessage();        
                $result['message'] = 'Suspended action!';
                break;
            default:

        }
        $result['status'] = 'Ok';            

    } else {
        $result["status"] = "error";
        $result["message"] = "Method not allowed. Use POST";
    }
} catch (Exception $e) {
    $result["status"] = "error";
    $result["message"] = $e->getMessage();
}

header('Content-Type: application/json; charset=utf-8');

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$db = null;


function notifyWebSocket($result) {
    try {
        $ws = new Client(new Uri('ws://localhost:8080'));
        $ws->connect();
        
        $message = new Text(json_encode($result));
        $ws->send($message);
        
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    //$ws->close();
}
?>
