<?php
namespace lib\abstracts;

use Exception;
use Throwable;
use lib\App;
use lib\classes\Logger;

use lib\exceptions\SystemException;

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
     * @throws SystemException
     */
    public function __construct( string $file, int $line, string $message, mixed $code = 0, Throwable $previous = null) {
        parent::__construct($message, intval($code), $previous);
        $this->file = $file;
        $this->line = $line;
        $this->logger = App::getInstance(Logger::class, null, ["log_type", "exception"]);
    }

    /**
     * @throws SystemException
     */
    abstract public function log() : void;
}
