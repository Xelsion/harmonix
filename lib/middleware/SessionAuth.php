<?php
namespace lib\middleware;

use lib\App;
use lib\abstracts\AMiddleware;
use lib\helper\StringHelper;
use models\ActorModel;
use models\SessionModel;

use lib\exceptions\SystemException;

/**
 * Class SessionAuth
 */
class SessionAuth extends AMiddleware {

    /**
     * @throws SystemException
     */
    public static function proceed() {
        $session = App::getInstance(SessionModel::class);

        if( isset($_COOKIE[$session->cookie_name]) ) {
            $session_id = ($session->encryption)
                ? StringHelper::decrypt($_COOKIE[$session->cookie_name])
                : $_COOKIE[$session->cookie_name];
            $session->init($session_id);
        }

        // do we have a login attempt?
        if( isset($_POST["login"], $_POST["email"], $_POST["password"]) ) {
            $session->login($_POST["email"], $_POST["password"]);
        }

        // do we have a logout attempt?
        if( isset($_POST["logout"]) ) {
            $session->logout();
        }

        $actor = $session->getActor();

        // only developer can log in as any actor
        if( ($session->as_actor > 0 || isset($_POST["login-as"], $_POST["actor_id"])) && ActorModel::isDeveloper($actor->id) ) {
            if( isset($_POST["actor_id"]) ) {
                $session->as_actor = (int)$_POST["actor_id"];
                $session->update();
                $session->writeCookie();
            }
            $actor = App::getInstance(ActorModel::class, null, ["id" => $session->as_actor]);
        }
        App::$curr_actor = $actor;
    }
}