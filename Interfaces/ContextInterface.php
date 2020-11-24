<?php


namespace XmlProcessor\Interfaces;

interface ContextInterface {
    public function getOpts() : array;

    public function addParams($context);
}