<?php

namespace Syncany\Api\Exception\Http;

use Syncany\Api\Exception\ApiException;

abstract class HttpException extends ApiException {
    private $reason;

    public function __construct($code, $message, $reason) {
        parent::__construct($message, $code);
        $this->reason = $reason;
    }

    public function getHeaderLine()
    {
        return $this->getCode() . " " . $this->getMessage();
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function sendErrorHeadersAndExit()
    {
        header("HTTP/1.1 " . $this->getHeaderLine());
        header("X-Syncany-Error-Reason: " . $this->getReason());

        exit;
    }
}