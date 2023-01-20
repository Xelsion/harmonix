<?php

namespace lib\middleware;

use JsonException;
use lib\abstracts\AMiddleware;
use lib\core\System;
use lib\exceptions\SystemException;
use lib\helper\StringHelper;
use models\ActorModel;
use models\SessionModel;

class SessionAuth extends AMiddleware {

    /**
     * @throws SystemException
     * @throws JsonException
     */
    public static function proceed() {
        $session = new SessionModel();

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
            $actor = new ActorModel($session->as_actor);
        }

        System::$Core->actor = $actor;
    }
}