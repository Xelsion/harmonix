<?php

namespace system;

/**
 * The System class
 * Is a global accessible class that contains the Core class
 * and the Storage class
 *
 * Core class:
 * Can be used at any point of the application get class objects
 *
 * Storage class:
 * Can be used to set or get values at any point of the application
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class System {

    // The Core class witch will make all registered classes accessible
    public static Core $Core;

    // The Storage class witch makes its stored key => value pairs accessible
    public static Storage $Storage;

}
