<?php

namespace XmlProcessor\Services\Context;

use XmlProcessor\Services\Config\Config;

class MarkerContext extends Context {

    public function __construct() {
        $this->allowedFetchNumber = explode(",", Config::load()->get("MARKER_ALLOWED_FETCH_NUMBER", "20,200,2000"));
    }

    public function getOpts() : array {
        return array(
            "http" => array(
                "method" => "GET",
                "header" => "Content-Type: application/xml"
            ),
        );
    }

    public function addParams($context) {
        $total = (int)Config::load()->get("MARKER_ACTIVE_FETCH_NUMBER", "20");
        $this->checkAllowedFetchNumber($total);
        $params = array("total" => $total);
        stream_context_set_params($context, $params);

        return $context;
    }
}