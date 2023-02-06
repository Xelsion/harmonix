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
        if( App::$request->data->contains("login") && App::$request->data->contains("email") && App::$request->data->contains("password") ) {
            $session->login(App::$request->data->get("email"), App::$request->data->get("password"));
        }

        // do we have a logout attempt?
        if( App::$request->data->contains("logout") ) {
            $session->logout();
        }

        $actor = $session->getActor();

        // only developer can log in as any actor
        if( ActorModel::isDeveloper($actor->id) && ($session->as_actor > 0 || (App::$request->data->contains("login_as") && App::$request->data->contains("actor_id")))  ) {
            $session->as_actor = (int)App::$request->data->get("actor_id");
            $session->update();
            $session->writeCookie();
            $actor = App::getInstanceOf(ActorModel::class, null, ["id" => $session->as_actor]);
        }
        App::$curr_actor = $actor;
    }
}