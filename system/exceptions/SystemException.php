<?php

namespace system\exceptions;

use Throwable;
use system\abstracts\ALoggableException;

class SystemException extends ALoggableException {

    /**
     * @inheritDoc
     */
    public function __construct( string $file, int $line, $message, int $code = 0, Throwable $previous = null ) {
        parent::__construct($file, $line, $message, $code, $previous);
    }

    /**
     * @inheritDoc
     */
    public function log() : void {
        $this->logger->log($this->file, $this->line, $this->message, $this->getTrace());
    }
}