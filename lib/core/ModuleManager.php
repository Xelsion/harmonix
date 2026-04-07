<?php

namespace lib\core;

use lib\App;
use lib\core\blueprints\AModule;
use lib\core\classes\Configuration;
use lib\core\classes\Template;
use lib\core\enums\Subdomain;
use lib\core\exceptions\SystemException;
use ReflectionClass;
use ReflectionException;

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

			$allowed = $module->allowedSubDomains();
			if( !is_array($allowed) || empty($allowed) ) {
				throw new SystemException(__FILE__, __LINE__, "Module '{$module->moduleName}' returned invalid subdomains");
			}

			foreach( $allowed as $sd ) {
				if( !$sd instanceof Subdomain ) {
					throw new SystemException(__FILE__, __LINE__, "Module '{$module->moduleName}' must return an array of Subdomain enums. eg. Subdomain::ANY");
				}
			}

			$allowedStrings = array_map(static fn(Subdomain $sd) => $sd->toString(), $allowed);
			if( !in_array("*", $allowedStrings, true) && !in_array(SUB_DOMAIN, $allowedStrings, true) ) {
				// Modul ist für diese Subdomain NICHT erlaubt → überspringen
				continue;
			}


			$this->modules[] = $module;
			try {
				$reflect = new ReflectionClass($module);
				$hasOnStart = $reflect->getMethod('onStart')->class !== AModule::class;
				$hasOnBeforeRouting = $reflect->getMethod('onBeforeRouting')->class !== AModule::class;
				$hasOnAfterRouting = $reflect->getMethod('onAfterRouting')->class !== AModule::class;
				$hasOnBeforeResponse = $reflect->getMethod('onBeforeResponse')->class !== AModule::class;
				if( $hasOnStart || $hasOnBeforeRouting || $hasOnAfterRouting || $hasOnBeforeResponse ) {
					// Events registrieren
					if( $hasOnStart ) {
						$this->events['start'][] = $module;
					}
					if( $hasOnBeforeRouting ) {
						$this->events['beforeRouting'][] = $module;
					}
					if( $hasOnAfterRouting ) {
						$this->events['afterRouting'][] = $module;
					}
					if( $hasOnBeforeResponse ) {
						$this->events['beforeResponse'][] = $module;
					}
				} else {
					throw new SystemException(__FILE__, __LINE__, "Module '{$module->moduleName}' has no events");
				}
			} catch( ReflectionException $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage());
			}
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

	/* ------------- module getter ------------- */
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

	/* ------------- module hooks ------------- */
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

	public function runAfterRouting(array $route): void {
		foreach( $this->events['afterRouting'] as $module ) {
			$module->onAfterRouting($route);
		}
	}

	public function runBeforeResponse(Template $template): void {
		foreach( $this->events['beforeResponse'] as $module ) {
			$module->onBeforeResponse($template);
		}
	}
}
