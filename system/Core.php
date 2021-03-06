<?php

namespace system;

use system\classes\Auth;
use system\classes\Configuration;
use system\classes\Logger;
use system\classes\Request;
use system\classes\Router;
use system\classes\tree\Menu;
use system\classes\tree\RoleTree;
use system\manager\ConnectionManager;
use models\Actor;
use models\ActorRole;

/**
 * The system class holds all important object
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

	// The Menu
	public static RoleTree $_role_tree;

	// The current actor
	public static Actor $_actor;

	// The current actor role
	public static ActorRole $_actor_role;

	// The router
	public static Router $_router;

    // The authentication class
    public static Auth $_auth;

}