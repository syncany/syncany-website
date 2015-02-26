<?php

namespace Syncany\Api\Exception;

class ConfigException extends ApiException {
    public function __construct($message) {
        parent::__construct($message);
    }
}