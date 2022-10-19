<?php

namespace system\abstracts;

abstract class ADBConnection {

    public string $_host;
    public string $_port;
    public string $_dbname;
    public string $_user;
    public string $_pass;

    abstract public function getConnectionString(): string;

    abstract public function getConnectionOptions(): array;

}
