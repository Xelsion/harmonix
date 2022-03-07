<?php

namespace core;

use core\classes\Configuration;
use core\classes\Logger;
use core\classes\Request;
use core\classes\Router;
use core\classes\tree\Menu;
use core\manager\ConnectionManager;
use models\Actor;
use models\ActorRole;

/**
 * The core class holds all important object
 * accessible from anywhere
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Core {

	// The application configuration
	public static Configuration $_configuration;

	// The database connection Manager
	public static ConnectionManager $_connection_manager;

	// The debug logger
	public static Logger $_debugger;

	// The request obj
	public static Request $_request;

	// The Menu
	public static Menu $_menu;

	// The current actor
	public static Actor $_actor;

	// The current actor role
	public static ActorRole $_actor_role;

	// The router
	public static Router $_router;
}