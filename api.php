<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class TodoServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new SplObjectStorage;
        echo "Servidor WebSocket iniciado!\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $conn->clientId = null;
        $this->clients->attach($conn);
       
        echo "Nova conexão! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {        
        $data = json_decode($msg, true);

        if (isset($data['action']) && $data['action'] === 'register') {
            $from->clientId = $data['clientId'] ?? null;
            return;
        }

        if ($msg != 'ping') {            
            $action = $data['action'] ?? '';
            $task = $data['task'] ?? [];

            $exclude = $data['clientId'] ?? null;
            
            //Propaga a mensagem para todos os clientes
            foreach ($this->clients as $client) {
                if ($client->clientId !== $exclude) {
                    $client->send(json_encode($data));//['action' => $action, 'task' => $task]));
                }
            }
        } else {
           // echo "Ping Received from client \n";
        }
        
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexão fechada! ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}
?>
