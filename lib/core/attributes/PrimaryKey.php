<?php

namespace lib\core\attributes;

use Attribute;

/**
 * The Attribute class PrimaryKey.
 * This class defines the PrimaryKey of a database table.
 *
 * @see Route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey {

}