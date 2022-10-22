<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\traits\DatabaseAssertions;
use markhuot\craftpest\web\TestableResponse;

class TestCase extends \PHPUnit\Framework\TestCase {

    use ActingAs, DatabaseAssertions;

    protected function setUp(): void
    {
        $this->createApplication();

        $this->callTraits('setUp');
    }

    protected function tearDown(): void
    {
        $this->callTraits('tearDown');
    }

    protected function callTraits($prefix)
    {
        $traits = [];

        $reflect = new \ReflectionClass($this);
        while ($reflect) {
            $traits = array_merge($traits, $reflect->getTraits());
            $reflect = $reflect->getParentClass();
        }

        foreach ($traits as $trait) {
            $method = $prefix . $trait->getShortName();
            if ($trait->hasMethod($method)) {
                $this->{$method}();
            }
        }
    }

    public function createApplication()
    {
        if ($this->needsRequireStatements()) {
            $this->requireCraft();
        }

        return \Craft::$app;
    }

    protected function needsRequireStatements()
    {
        return !defined('CRAFT_BASE_PATH');
    }

    protected function requireCraft()
    {
        require './src/bootstrap/bootstrap.php';
    }

    function get(...$args): TestableResponse
    {
        //return Pest::getInstance()->http->get(...$args);
        return (new RequestBuilder('get', ...$args))->send();
    }

    function post(...$args): TestableResponse
    {
        return (new RequestBuilder('post', ...$args))->send();
    }

    function http(string $method, string $uri): RequestBuilder
    {
        return new RequestBuilder($method, $uri);
    }

    public function factory(string $class)
    {
        return $class::factory();
    }

}
