<?php

namespace Syncany\Api\Controller;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\NotFoundHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\ApplicationLink;
use Syncany\Api\Persistence\Database;

class LinksController extends Controller
{
    public function get(array $methodArgs, array $requestArgs)
    {
        // Check params
        $link = $this->getAndCheckShortLink($methodArgs, $requestArgs);

        // Get long link
        $statement = Database::prepareStatementFromResource("links-read", __NAMESPACE__, "links.select-link.sql");
        $statement->bindValue(':link', $link, \PDO::PARAM_STR);

        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        if (!$statement->execute()) {
            throw new ServerErrorHttpException("Cannot retrieve links from database.");
        }

        $applicationLinkArray = $statement->fetch();

        if (!$applicationLinkArray) {
            throw new NotFoundHttpException("Link does not exist.");
        }

        // Redirect to long link
        $applicationLink = ApplicationLink::fromArray($applicationLinkArray);

        header("Location: {$applicationLink->getLongLink()}");
        exit;
    }

    public function post(array $methodArgs, array $requestArgs)
    {
        $this->postAdd($methodArgs, $requestArgs);
    }

    public function postAdd(array $methodArgs, array $requestArgs)
    {
        // Check params
        $longLink = $this->getAndCheckLongLink($methodArgs);

        // Generate and insert short link
        $shortLink = $this->generateRandomString(7);

        $statement = Database::prepareStatementFromResource("links-write", __NAMESPACE__, "links.insert-link.sql");
        $statement->bindValue(':shortLink', $shortLink, \PDO::PARAM_STR);
        $statement->bindValue(':longLink', $longLink, \PDO::PARAM_STR);

        if (!$statement->execute()) {
            throw new ServerErrorHttpException("Cannot insert link to database.");
        }

        // Print XML
        $this->printResponseXml($shortLink);
        exit;
    }

    private function getAndCheckShortLink($methodArgs, $requestArgs)
    {
        if (!isset($methodArgs['l']) && !isset($requestArgs[0])) {
            throw new BadRequestHttpException("No link provided");
        }

        $shortLink = (isset($methodArgs['l'])) ? $methodArgs['l'] : $requestArgs[0];

        if (!preg_match('/^[a-z0-9]+$/i', $shortLink)) {
            throw new BadRequestHttpException("Invalid link format");
        }

        return $shortLink;
    }

    private function getAndCheckLongLink($methodArgs)
    {
        if (!isset($methodArgs['l'])) {
            throw new BadRequestHttpException("No link provided");
        }

        if (!preg_match('/^syncany:\/\//', $methodArgs['l'])) {
            throw new BadRequestHttpException("Invalid link format");
        }

        if (preg_match('/not-encrypted/', $methodArgs['l'])) {
            throw new BadRequestHttpException("Unencrypted links not allowed");
        }

        if (strlen($methodArgs['l']) > 4096) {
            throw new BadRequestHttpException("Link too long");
        }

        return $methodArgs['l'];
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    private function printResponseXml($shortLink)
    {
        header("Content-Type: application/xml");

        echo "<?xml version=\"1.0\"?>\n";
        echo "<applicationLinkShortenerResponse xmlns=\"http://syncany.org/links/2/add\">\n";
        echo "	<shortLinkId>$shortLink</shortLinkId>\n";
        echo "</applicationLinkShortenerResponse>\n";
    }
}