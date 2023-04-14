<?php
namespace lib\middleware;

use lib\App;
use lib\core\blueprints\AMiddleware;
use lib\helper\StringHelper;
use models\ActorModel;
use models\SessionModel;

/**
 * Class SessionAuth
 */
class SessionAuth extends AMiddleware {

    /**
     * @throws \lib\core\exceptions\SystemException
     */
    public function invoke(): void {
        $session = App::getInstanceOf(SessionModel::class);

        if( isset($_COOKIE[$session->cookie_name]) ) {
            $session_id = ($session->encryption)
                ? StringHelper::decrypt($_COOKIE[$session->cookie_name])
                : $_COOKIE[$session->cookie_name];
            $session->init($session_id);
        }

        // do we have a login attempt?
        if( App::$request->contains("login") && App::$request->contains("email") && App::$request->contains("password") ) {
            $session->login(App::$request->get("email"), App::$request->get("password"));
        }

        // do we have a logout attempt?
        if( App::$request->contains("logout") ) {
            $session->logout();
        }

        $actor = $session->getActor();

        // only developer can log in as any actor
        if( $actor->isDeveloper() && ($session->as_actor > 0 || (App::$request->contains("login_as") && App::$request->contains("actor_id")))  ) {
            $session->as_actor = ( App::$request->contains("login_as") )
                ? (int)App::$request->get("actor_id")
                : $session->as_actor
            ;
            $session->update();
            $session->writeCookie();
            $actor = App::getInstanceOf(ActorModel::class, null, ["id" => $session->as_actor]);
        }
        App::$curr_actor = $actor;
    }
}