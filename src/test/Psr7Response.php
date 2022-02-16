<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\dom\NodeList;
use Symfony\Component\DomCrawler\Crawler;
use yii\base\UnknownPropertyException;

class Response extends \GuzzleHttp\Psr7\Response
{
    /**
     * Magic property getter to turn Guzzle's getStatusCode style methods in to
     * expectable `->statusCode->toBe(200)` style properties.
     */
    function __get(string $key) {
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new UnknownPropertyException('Could not find ' . $key);
    }

    protected $_bodyContents = null;

    public function getBodyContents() {
        if ($this->_bodyContents !== null) {
            return $this->_bodyContents;
        }

        return $this->_bodyContents = $this->getBody()->getContents();
    }
}
