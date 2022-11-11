<?php

namespace system\abstracts;

use Exception;
use JsonException;
use system\classes\Logger;
use system\exceptions\SystemException;
use Throwable;

abstract class ALoggableException extends Exception {

    // The exception logger
    protected Logger $logger;

    /**
     * The constructor
     *
     * @param string $file - the filename where the exception was thrown
     * @param int $line - the line in where the exception was thrown
     * @param string $message - the exception message
     * @param int $code - the exception code (optional)
     * @param Throwable|null $previous - the previous throwable object (optional)
     */
    public function __construct( string $file, int $line, string $message, int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->file = $file;
        $this->line = $line;
        $this->logger = new Logger("exception");
    }

    /**
     * @throws SystemException
     * @throws JsonException
     */
    abstract public function log() : void;
}
