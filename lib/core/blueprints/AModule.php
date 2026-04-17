<?php

namespace lib\core\blueprints;

use lib\core\classes\Configuration;
use lib\core\classes\Template;


/**
 * A Module is an extension for the framework or the output.
 * It is integrated int different stage of the App flow
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AModule {

	protected Configuration $config;
	public array $moduleConfig = [];
	public string $modulePath = "";
	public string $moduleName = "";

	public function __construct(Configuration $cfg, string $path, string $name) {
		$this->config = $cfg;
		$this->modulePath = $path;
		$this->moduleName = $name;

		$configFile = $path . "/module.ini";
		if( file_exists($configFile) ) {
			$this->moduleConfig = parse_ini_file($configFile, true);
		}
	}

	abstract public function allowedSubDomains(): array;

	/**
	 * The boot method is called when the Modul is initiated
	 */
	abstract public function boot(): void;

	/**
	 * Can return a collection of Controllers which will be added be the Routing System
	 * @return array
	 */
	public function controllerDirectories(): array {
		return [];
	}

	/**
	 * Can return a collection of classes which will be added the to class list of the App
	 * @return array
	 */
	public function registerServices(): array {
		return [];
	}

	/**
	 * Can return a collection of Middleware which will be added to the middleware of the App
	 * @return array
	 */
	public function registerMiddleware(): array {
		return [];
	}

	/**
	 * onStant() will be called at the most early point in the flow of the App
	 * @return void
	 */
	public function onStart(): void {

	}

	/**
	 * onBeforeRouting() will be called before the Router process the request
	 * @return void
	 */
	public function onBeforeRouting(): void {

	}

	/**
	 * onAfterRouting() will be called after the Router has process the request and
	 * contains the route array of the processed request.
	 *
	 * @param array $route
	 * @return void
	 */
	public function onAfterRouting(array $route): void {

	}

	/**
	 * onAfterConroller() will be called after the Controller has returned his AResponse object
	 *
	 * @param AResponse|null $response
	 * @return void
	 */
	public function onAfterController(?AResponse $response): void {

	}

	/**
	 * onBeforeResponse() will be called right before the framework give the response to the caller
	 * abd it has the current Template of the response in the parameters
	 *
	 * @param Template|null $template
	 * @return void
	 */
	public function onBeforeResponse(?Template $template): void {

	}

}
