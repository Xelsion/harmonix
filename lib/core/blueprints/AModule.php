<?php

namespace lib\core\blueprints;

use lib\core\classes\Configuration;

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

	/** Wird nach dem Laden ausgeführt */
	abstract public function boot(): void;

	/** Controller-Verzeichnisse als [subdomain, path] */
	public function controllerDirectories(): array {
		return [];
	}

	/** Registrierung von Services im DI */
	public function registerServices(): array {
		return [];
	}

	/** Registrierung von Middleware */
	public function registerMiddleware(): array {
		return [];
	}

	/** Optional: Event Hooks */
	public function onStart(): void {

	}

	public function onBeforeRouting(): void {

	}

	public function onAfterRouting(): void {

	}

	public function onBeforeResponse(string &$output): void {

	}

}
