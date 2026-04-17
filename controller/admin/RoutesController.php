<?php

namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\LinqList;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\ErrorType;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;

#[Route("routes")]
class RoutesController extends AController {

	/**
	 * Get a list of all Routes
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "routes/index.html");
		$conflicts = $this->checkForConflicts();
		$linq_list = new LinqList($conflicts);

		TemplateData::set("routes_list", App::getInstanceOf(Router::class)->getSortedRoutes());
		TemplateData::set("conflicts", $linq_list);
		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse(), true);
		return new HtmlResponse($template);
	}

	/**
	 * Checks if Routes conflicts each other and returns them in an array
	 *
	 * @return array
	 * @throws SystemException
	 */
	private function checkForConflicts(): array {
		$router = App::getInstanceOf(Router::class);
		$routes = $router->getRoutes();
		$conflicts = array();
		foreach( $routes as $domain => $request_methods ) {
			foreach( $request_methods as $request_method => $paths ) {
				foreach( $paths as $path => $route ) {
					$regex = $route["regex"];
					// check all routes wich have the same subdomain and request method
					foreach( $paths as $check_path => $check_route ) {
						// the router would throw an error if 2 paths where the same in the same subdomain
						// so if the entry is the same as the entry to check we can skip it
						if( $check_path === $path ) {
							continue;
						}

						$matches = array();
						preg_match("/^" . $regex . "$/", $check_path, $matches);
						if( count($matches) > 0 ) {
							$conflicts[] = array(
								"error_type"              => ErrorType::WARNING,
								"domain"                  => $domain,
								"request_method"          => $request_method,
								"path"                    => $path,
								"route"                   => $route,
								"conflict_domain"         => $domain,
								"conflict_request_method" => $request_method,
								"conflict_path"           => $check_path,
								"conflict_route"          => $check_route
							);
						}
					}

					// if the current request method to check is not ANY we need to check them as well
					if( $request_method !== RequestMethod::ANY->toString() ) {
						foreach( $request_methods[RequestMethod::ANY->toString()] as $check_path => $check_route ) {
							if( $check_path === $path ) {
								$conflicts[] = array(
									"error_type"              => ErrorType::CRITICAL,
									"domain"                  => $domain,
									"request_method"          => $request_method,
									"path"                    => $path,
									"route"                   => $route,
									"conflict_domain"         => $domain,
									"conflict_request_method" => RequestMethod::ANY->toString(),
									"conflict_path"           => $check_path,
									"conflict_route"          => $check_route
								);
							} else {
								$matches = array();
								preg_match("/^" . $regex . "$/", $check_path, $matches);
								if( count($matches) > 0 ) {
									$conflicts[] = array(
										"error_type"              => ErrorType::WARNING,
										"domain"                  => $domain,
										"request_method"          => $request_method,
										"path"                    => $path,
										"route"                   => $route,
										"conflict_domain"         => $domain,
										"conflict_request_method" => RequestMethod::ANY->toString(),
										"conflict_path"           => $check_path,
										"conflict_route"          => $check_route
									);
								}
							}
						}
					}
				}
			}
		}
		return $conflicts;
	}

}
