<?php

namespace system\helper;

use system\Core;

class RequestHelper {

    public static function getPaginationParams(): array {
        $params = array();
        $params['order'] = Core::$_request->get("order") ?? "";
        $params['direction'] = Core::$_request->get("direction") ?? "asc";
        $params['limit'] = ( Core::$_request->get("limit") !== null )
            ? (int) Core::$_request->get("limit")
            : 20;
        $params['page'] = ( Core::$_request->get("page") !== null )
            ? (int) Core::$_request->get("page")
            : 1;
        return $params;
    }

}
