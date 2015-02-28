<?php

namespace Syncany\Api\Exception\Http;

class UnauthorizedHttpException extends HttpException {
    public function __construct($message) {
        parent::__construct(401, "Unauthorized", $message);
    }
}