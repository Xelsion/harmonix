<?php

namespace system\abstracts;
use system\interfaces\IDBConnection;

abstract class ADBConnection implements IDBConnection {

    public string $_host;
    public string $_port;
    public string $_dbname;
    public string $_user;
    public string $_pass;

}
