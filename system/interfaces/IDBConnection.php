<?php
namespace system\interfaces;

interface IDBConnection {

    public function getConnectionString(): string;

    public function getConnectionOptions(): array;

}
