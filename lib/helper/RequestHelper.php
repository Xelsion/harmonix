<?php
namespace lib\helper;

use lib\core\Request;

readonly class RequestHelper {

    public function __construct(private Request $request) {

    }

    public function getPaginationParams(): array {
        $params = array();
        $params['order'] = $this->request->get("order") ?? "";
        $params['direction'] = $this->request->get("direction") ?? "asc";
        $params['limit'] = ( $this->request->get("limit") !== null )
            ? (int) $this->request->get("limit")
            : 50;
        $params['page'] = ( $this->request->get("page") !== null )
            ? (int) $this->request->get("page")
            : 1;
        return $params;
    }

}
