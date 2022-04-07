<?php

class Chat {
    private $address = '0.0.0.0';   // 0.0.0.0 means all available interfaces
    private $port = 33379;          // the TCP port that should be used
    private $maxClients = 10;

    private $clients;
    private $socket;

    public function __construct() {
        // Set time limit to indefinite execution
        set_time_limit(0);
        error_reporting(E_ALL ^ E_NOTICE);
    }

    public function start() {
        // Create a TCP Stream socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        // Bind the socket to an address/port
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->address, $this->port);
        // Start listening for connections
        socket_listen($this->socket, $this->maxClients);


        $this->clients = array('0' => array('socket' => $this->socket));

        while (true) {
            // Setup clients listen socket for reading
            $read[0] = $this->socket;
            for($i=1; $i<count($this->clients)+1; ++$i) {
                if($this->clients[$i] != NULL) {
                    $read[$i+1] = $this->clients[$i]['socket'];
                }
            }

            // Set up a blocking call to socket_select()
            $write = NULL;
            $except = NULL;
            $tv_sec = NULL;
            $ready = socket_select($read, $write, $except, $tv_sec);

            /* if a new connection is being made add it to the client array */
            if( in_array($this->socket, $read, TRUE) ) {
                for($i=1; $i < $this->maxClients+1; ++$i) {
                    if( !isset($this->clients[$i]) ) {
                        $this->clients[$i]['socket'] = socket_accept($this->socket);
                        socket_getpeername($this->clients[$i]['socket'], $ip);
                        $this->clients[$i]['ipaddy'] = $ip;

                        socket_write($this->clients[$i]['socket'], 'Welcome to my Custom Socket Server'."\r\n");
                        socket_write($this->clients[$i]['socket'], 'There are '.(count($this->clients) - 1).' client(s) connected to this server.'."\r\n");

                        $this->log("New client #$i connected: " . $this->clients[$i]['ipaddy']);
                        break;
                    }

                    if( $i === $this->maxClients - 1 ) {
                        $this->log('Too many Clients connected!');
                    }

                    if($ready < 1) {
                        continue;
                    }
                }
            }

            // If a client is trying to write - handle it now
            for($i=1; $i<$this->maxClients+1; ++$i) {
                if( in_array($this->clients[$i]['socket'], $read, TRUE) ) {
                    $data = @socket_read($this->clients[$i]['socket'], 1024, PHP_NORMAL_READ);

                    if($data === FALSE) {
                        unset($this->clients[$i]);
                        $this->log('Client disconnected!');
                        continue;
                    }

                    $data = trim($data);

                    if(!empty($data)) {
                        switch ($data) {
                            case 'exit':
                            case 'quit':
                                socket_write($this->clients[$i]['socket'], "Thanks for trying my Custom Socket Server, Goodbye.\r\n");
                                $this->log("Client #$i is exiting");
                                unset($this->clients[$i]);
                                continue 2;
                            case 'term':
                                // first write a message to all connected clients
                                foreach( $this->clients as $client ) {
                                    if( isset($client["socket"]) && $client["socket"] !== $this->socket ) {
                                        socket_write($client["socket"], "Server will be shut down now...\r\n");
                                    }
                                }
                                // Close the master sockets, server termination requested
                                socket_close($this->socket);
                                $this->log("Terminated server (requested by client #$i)");
                                exit;
                            default:
                                foreach( $this->clients as $client ) {
                                    if( isset($client["socket"]) && $client["socket"] !== $this->socket ) {
                                        $this->log($client['ipaddy'] . ' is sending a message to ' . $client['ipaddy'] . '!');
                                        socket_write($client['socket'], '[' . $client['ipaddy'] . '] says: ' . $data . "\r\n");
                                    }
                                }
                                break(2);
                        }
                    }
                }
            }
        } // end while
    }

    private function log($msg) {
        // instead of echoing to console we could write this to a database or a textfile
        echo "[".date('Y-m-d H:i:s')."] " . $msg . "\r\n";
    }
}
