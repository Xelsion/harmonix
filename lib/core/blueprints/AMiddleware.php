<?php
namespace lib\core\blueprints;

abstract class AMiddleware {

    abstract public function invoke(): void;

}