<?php

namespace lib\middleware;

use lib\App;
use lib\core\blueprints\AMiddleware;
use lib\core\classes\Configuration;
use lib\helper\StringHelper;
use models\ActorModel;
use models\SessionModel;
use repositories\MVCRepository;

/**
 * Class SessionAuth
 */
class SessionAuth extends AMiddleware {

	private array $config;

	public function __construct(Configuration $config) {
		$this->config = $config->getSection("session");
	}

	/**
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function invoke(): void {
		$redirect_to_home = false;
		$session = App::getInstanceOf(SessionModel::class);
		$use_cookies = (bool)$this->config['use_cookies'];

		if( $use_cookies ) {
			if( isset($_COOKIE[$session->cookie_name]) ) {
				$session_id = ($session->encryption) ? StringHelper::decrypt($_COOKIE[$session->cookie_name], session_id()) : $_COOKIE[$session->cookie_name];
				$session->init($session_id);
			}
		} else if( isset($_SESSION[$session->cookie_name]) ) {
			$session_id = ($session->encryption) ? StringHelper::decrypt($_SESSION[$session->cookie_name], session_id()) : $_SESSION[$session->cookie_name];
			$session->init($session_id);
		}

		// do we have a login attempt?
		if( App::$request->contains("login") && App::$request->contains("email") && App::$request->contains("password") ) {
			$session->login(App::$request->get("email"), App::$request->get("password"));
			$redirect_to_home = true;
		}

		// do we have a logout attempt?
		if( App::$request->contains("logout") ) {
			$session->logout();
			$redirect_to_home = true;
		}

		$actor = $session->getActor();

		// only developer can log in as any actor
		if( $actor->isDeveloper() && ($session->as_actor > 0 || (App::$request->contains("login_as") && App::$request->contains("actor_id"))) ) {
			$session->as_actor = (App::$request->contains("login_as")) ? (int)App::$request->get("actor_id") : $session->as_actor;

			$repository = App::getInstanceOf(MVCRepository::class);
			$repository->updateSession($session);
			if( $use_cookies ) {
				$session->writeCookie();
			} else {
				$session->writeSession();
			}
			$actor = App::getInstanceOf(ActorModel::class, null, ["id" => $session->as_actor]);
		}
		App::$curr_actor = $actor;

		if( $redirect_to_home ) {
			redirect("/");
		}
	}

}