<?php

namespace XmlProcessor\Services\Context;

use Exception;
use XmlProcessor\Interfaces\ContextInterface;

class Context implements ContextInterface {

    protected array $allowedFetchNumber = array();

    /**
     * @return resource
     * @throws Exception
     */
    public function buildFetchContext() {
        $opts = $this->getOpts();
        $context = $this->addParams(stream_context_create($opts));

        return $context;
    }

    public function getOpts() : array {
        return array();
    }

    public function addParams($context) {
        return $context;
    }

    protected function checkAllowedFetchNumber(int $total) {
        if(!in_array($total, $this->allowedFetchNumber)) {
            throw new Exception("Invalid fetch number, aborting.");
        }
    }
}