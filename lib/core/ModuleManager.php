<?php

namespace lib\core;

use lib\App;
use lib\core\blueprints\AModule;
use lib\core\blueprints\AResponse;
use lib\core\classes\Configuration;
use lib\core\classes\Template;
use lib\core\enums\Subdomain;
use lib\core\exceptions\SystemException;
use ReflectionClass;
use ReflectionException;

/**
 * This class will handle all modules the calls the methods of all modules at the right stage of the App flow
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ModuleManager {

	protected Configuration $config;

	private array $modules = [];

	private array $events = [
		'start'           => [],
		'beforeRouting'   => [],
		'afterRouting'    => [],
		'afterController' => [],
		'beforeResponse'  => [],
	];

	/**
	 * The constructor of this class
	 *
	 * @param ClassManager $cm
	 * @param Configuration $config
	 */
	public function __construct(Configuration $config) {
		$this->config = $config;
	}

	/**
	 * Loads all Module.php file from the given director + subdirectories add registers the given module methods at the
	 * right flow points
	 *
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
				$hasOnAfterController = $reflect->getMethod('onAfterController')->class !== AModule::class;
				$hasOnBeforeResponse = $reflect->getMethod('onBeforeResponse')->class !== AModule::class;
				if( $hasOnStart || $hasOnBeforeRouting || $hasOnAfterRouting || $hasOnAfterController || $hasOnBeforeResponse ) {
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
					if( $hasOnAfterController ) {
						$this->events['afterController'][] = $module;
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
	 * The boo()t method calls all module->boot() methods
	 *
	 * @return void
	 * @throws exceptions\SystemException
	 */
	public function boot(): void {
		try {
			$router = App::getInstanceOf(Router::class);
			foreach( $this->modules as $module ) {
				// DI-Services
				$services = $module->registerServices();
				foreach( $services as $ns => $bind ) {
					App::set($ns, $bind);
				}

				// Controller-Verzeichnisse
				$controller = $module->controllerDirectories();
				foreach( $controller as $sub => $dir ) {
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
	/**
	 * Returns all Middleware classes from all modules
	 *
	 * @return array
	 */
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

	/**
	 * Returns the Config file by the given modul name
	 *
	 * @param string $moduleName
	 * @return array|null
	 */
	public function getModuleConfig(string $moduleName): ?array {
		foreach( $this->modules as $module ) {
			if( $module->moduleName === $moduleName ) {
				return $module->moduleConfig;
			}
		}
		return null;
	}

	/* ------------- module hooks ------------- */
	/**
	 * Calls the onStart() method of alle modules
	 *
	 * @return void
	 */
	public function runOnStart(): void {
		foreach( $this->events['start'] as $module ) {
			$module->onStart();
		}
	}

	/**
	 * Calls the onBeforeRouting() method of alle modules
	 *
	 * @return void
	 */
	public function runBeforeRouting(): void {
		foreach( $this->events['beforeRouting'] as $module ) {
			$module->onBeforeRouting();
		}
	}

	/**
	 * Calls the onAfterRouting() method of alle modules
	 *
	 * @param array $route
	 * @return void
	 */
	public function runAfterRouting(array $route): void {
		foreach( $this->events['afterRouting'] as $module ) {
			$module->onAfterRouting($route);
		}
	}

	/**
	 * Calls the onAfterController() method of alle modules
	 *
	 * @param array $route
	 * @return void
	 */
	public function runAfterController(?AResponse $response): void {
		foreach( $this->events['afterController'] as $module ) {
			$module->onAfterController($response);
		}
	}

	/**
	 * Calls the onBeforeResponse() method of alle modules
	 *
	 * @param Template|null $template
	 * @return void
	 */
	public function runBeforeResponse(?Template $template): void {
		foreach( $this->events['beforeResponse'] as $module ) {
			$module->onBeforeResponse($template);
		}
	}
}
