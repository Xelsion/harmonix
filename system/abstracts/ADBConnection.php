<?php

namespace system\abstracts;

abstract class ADBConnection {

    public string $host;
    public string $port;
    public string $dbname;
    public string $user;
    public string $pass;

    abstract public function getConnectionString(): string;

    abstract public function getConnectionOptions(): array;

}
