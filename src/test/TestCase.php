<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\Pest;

class TestCase extends \PHPUnit\Framework\TestCase {

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
        $reflect = new \ReflectionClass($this);
        $traits = $reflect->getTraits();
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
        // Define path constants
        define('CRAFT_BASE_PATH', getcwd());
        define('CRAFT_VENDOR_PATH', CRAFT_BASE_PATH . '/vendor');
        define('YII_ENABLE_ERROR_HANDLER', false);
        define('YII_DEBUG', true);

        // Load dotenv? 5.x vs 3.x vs 2.x
        if (file_exists(CRAFT_BASE_PATH . '/.env')) {
            if (method_exists('\Dotenv\Dotenv', 'createUnsafeImmutable')) {
                /** @phpstan-ignore-next-line */
                \Dotenv\Dotenv::createUnsafeImmutable(CRAFT_BASE_PATH)->safeLoad();
            } elseif (method_exists('\Dotenv\Dotenv', 'create')) {
                /** @phpstan-ignore-next-line */
                \Dotenv\Dotenv::create(CRAFT_BASE_PATH)->load();
            } else {
                /** @phpstan-ignore-next-line */
                (new \Dotenv\Dotenv(CRAFT_BASE_PATH))->load();
            }
        }

        // Define additional PHP constants
        // (see https://craftcms.com/docs/3.x/config/#php-constants)
        define('CRAFT_ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

        // Load and run Craft
        /** @var \craft\web\Application $app */
        $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';

        $app->setAliases(['@webroot' => CRAFT_BASE_PATH . '/web']);
        $app->projectConfig->writeYamlAutomatically = false;

        return $app;
    }

    /**
     * Passthrough for the HTTP service
     */
    function get(...$args): \craft\web\Response
    {
        return Pest::getInstance()->http->get(...$args);
    }

}
