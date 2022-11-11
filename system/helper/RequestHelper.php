<?php

namespace system\helper;

use system\System;

class RequestHelper {

    public static function getPaginationParams(): array {
        $params = array();
        $params['order'] = System::$Core->request->get("order") ?? "";
        $params['direction'] = System::$Core->request->get("direction") ?? "asc";
        $params['limit'] = ( System::$Core->request->get("limit") !== null )
            ? (int) System::$Core->request->get("limit")
            : 50;
        $params['page'] = ( System::$Core->request->get("page") !== null )
            ? (int) System::$Core->request->get("page")
            : 1;
        return $params;
    }

}
