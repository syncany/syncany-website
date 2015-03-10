<?php

/*
 * Syncany, www.syncany.org
 * Copyright (C) 2011-2015 Philipp C. Heckel <philipp.heckel@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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