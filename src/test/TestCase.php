<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\actions\RenderCompiledClasses;
use markhuot\craftpest\traits\Benchmark;
use markhuot\craftpest\traits\CookieState;
use markhuot\craftpest\traits\DatabaseAssertions;
use markhuot\craftpest\traits\Playwright;
use markhuot\craftpest\traits\RequestBuilders;
use markhuot\craftpest\traits\Serve;

class TestCase extends \PHPUnit\Framework\TestCase {

    use ActingAs,
        DatabaseAssertions,
        RequestBuilders,
        Benchmark,
        CookieState,
        Serve,
        Playwright;

    protected function setUp(): void
    {
        $this->createApplication();
        $this->renderCompiledClasses();

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

    public function renderCompiledClasses()
    {
        (new RenderCompiledClasses)->handle();
    }

    protected function needsRequireStatements()
    {
        return !defined('CRAFT_BASE_PATH');
    }

    protected function requireCraft()
    {
        require __DIR__ . '/../bootstrap/bootstrap.php';
    }

    /**
     * @template TClass
     * @param class-string<TClass> $class
     * @return TClass
     */
    public function factory(string $class)
    {
        return $class::factory();
    }

}
