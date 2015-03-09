<?php

namespace Syncany\Api\Exception\Http;

class ServerErrorHttpException extends HttpException {
    public function __construct($message) {
        parent::__construct(500, "Server Error", $message);
    }
}