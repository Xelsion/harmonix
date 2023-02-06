<?php
namespace lib\helper;

use lib\core\Request;

readonly class RequestHelper {

    public function __construct(private Request $request) {

    }

    public function getPaginationParams(): array {
        $params = array();
        $params['order'] = $this->request->data->get("order") ?? "";
        $params['direction'] = $this->request->data->get("direction") ?? "asc";
        $params['limit'] = ( $this->request->data->contains("limit") )
            ? (int) $this->request->data->get("limit")
            : 50;
        $params['page'] = ( $this->request->data->contains("page") )
            ? (int) $this->request->data->get("page")
            : 1;
        return $params;
    }

}
