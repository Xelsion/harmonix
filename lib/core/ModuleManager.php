<?php

namespace lib\core;

use lib\App;
use lib\core\classes\Configuration;
use lib\core\exceptions\SystemException;

class ModuleManager {

	protected ClassManager $container;
	protected Configuration $config;

	private array $modules = [];

	private array $events = [
		'start'          => [],
		'beforeRouting'  => [],
		'afterRouting'   => [],
		'beforeResponse' => [],
	];

	public function __construct(ClassManager $cm, Configuration $config) {
		$this->container = $cm;
		$this->config = $config;
	}

	/**
	 * @param string $path
	 * @return void
	 * @throws SystemException
	 */
	public function loadModules(string $path): void {
		foreach( glob($path . "/*/Module.php") as $file ) {
			$modulePath = dirname($file);
			$name = basename($modulePath);
			$ns = Path2Namespace($file);
			$module = App::getInstanceOf($ns, null, [
				"cfg"  => $this->config,
				"path" => $modulePath,
				"name" => $name
			]);
			$this->modules[] = $module;

			// Events registrieren
			$this->events['start'][] = $module;
			$this->events['beforeRouting'][] = $module;
			$this->events['afterRouting'][] = $module;
			$this->events['beforeResponse'][] = $module;
		}
	}

	/**
	 * @return void
	 * @throws exceptions\SystemException
	 */
	public function boot(): void {
		try {
			$router = Router::getInstance();
			foreach( $this->modules as $module ) {
				// DI-Services
				foreach( $module->registerServices() as $ns => $bind ) {
					App::set($ns, $bind);
				}

				// Controller-Verzeichnisse
				foreach( $module->controllerDirectories() as [$sub, $dir] ) {
					$router->registerController($sub, $dir);
				}

				// Modul booten
				$module->boot();
			}
		} catch( \Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage());
		}
	}

	public function runOnStart(): void {
		foreach( $this->events['start'] as $module ) {
			$module->onStart();
		}
	}

	public function runBeforeRouting(): void {
		foreach( $this->events['beforeRouting'] as $module ) {
			$module->onBeforeRouting();
		}
	}

	public function runAfterRouting(): void {
		foreach( $this->events['afterRouting'] as $module ) {
			$module->onAfterRouting();
		}
	}

	public function runBeforeResponse(string &$output): void {
		foreach( $this->events['beforeResponse'] as $module ) {
			$module->onBeforeResponse($output);
		}
	}


	public function runShutdown(): void {
		foreach( $this->events['shutdown'] as $module ) {
			$module->onShutdown();
		}
	}

	public function getMiddleware(): array {
		$mw = [];
		foreach( $this->modules as $module ) {
			$middlewares = $module->registerMiddleware();
			foreach( $middlewares as $middleware ) {
				$mw[] = $middleware;
			}
		}
		return $mw;
	}

	public function getModuleConfig(string $moduleName): ?array {
		foreach( $this->modules as $module ) {
			if( $module->moduleName === $moduleName ) {
				return $module->moduleConfig;
			}
		}
		return null;
	}

}
