<?php

class Pchat extends WebSocketComponent {
    public static $PROTOCOL = "pchat";

    private $clients;

    public function onLoad($ip, $port, $host) {
        $this->clients = array();
        $this->server->log->debug("SimpleEcho component loaded on $ip:$port for host $host");
    }

    public function onConnect($con) {
        $id = $con->getConnection()->id;
        $this->clients[$id] = $con;

        $con->send(json_encode(array(
            "event" => "welcome",
            "clients" => array_keys($this->clients),
            "me" => $id
        )));

        $jsonNewClient = json_encode(array(
            "event" => "clients_update",
            "clients" => array_keys($this->clients),
            "message" => "guest#" . $id . " connected"
        ));

        foreach ($this->clients as $client) {
            if ($client->getConnection()->id != $id) {
                $client->send($jsonNewClient);
            }
        }
    }

    public function onDisconnect($con) {
        $id = $con->getConnection()->id;

        unset($this->clients[$id]);

        $jsonNewClient = json_encode(array(
            "event" => "clients_update",
            "clients" => array_keys($this->clients),
            "message" => "guest#" . $id . " disconnected"
        ));

        foreach ($this->clients as $client) {
            $client->send($jsonNewClient);
        }
    }

    public function onMessage($con, $data, $type) {
        $id = $con->getConnection()->id;

        $json = json_encode(array(
            "event" => "new_message",
            "client" => $id,
            "message" => $data
        ));

        foreach ($this->clients as $client) {
            if ($client->getConnection()->id != $id) {
                $client->send($json);
            }
        }
    }
}
