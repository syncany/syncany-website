<?php

namespace Syncany\Api\Exception\Http;

class NotFoundHttpException extends HttpException {
    public function __construct($message) {
        parent::__construct(404, "Not Found", $message);
    }
}