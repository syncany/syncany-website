<?php

namespace Syncany\Api\Exception\Http;

class BadRequestHttpException extends HttpException {
    public function __construct($message) {
        parent::__construct(400, "Bad Request", $message);
    }
}