<?php

namespace XmlProcessor\ValueObjects;

use XmlProcessor\Services\Context\MarkerContext;

class FetchUrls {
    public function getUrlData() {
        return array(
            array(
                "url" => "http://markerdev.info/backend/data2.xml",
                "context_class" => MarkerContext::class,
            ),
        );
    }
}