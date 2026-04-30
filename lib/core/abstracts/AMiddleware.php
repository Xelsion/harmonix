<?php

namespace lib\core\abstracts;

/**
 * A Middleware class is used to do operations before the Controller is called.
 * Mostly each repository handles a single source (like a table in a database) but in some cases it could be more than
 * a single source.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AMiddleware {

	abstract public function invoke(): void;

}