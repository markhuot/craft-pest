<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\Pest;

class TestCase extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void
    {
        $this->createApplication();

        if (method_exists($this, 'refreshDatabase')) {
            $this->refreshDatabase();
        }

        if (method_exists($this, 'beginTransaction')) {
            $this->beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        if (method_exists($this, 'endTransaction')) {
            $this->endTransaction();
        }
    }

    protected function createApplication()
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

        // Load dotenv?
        if (class_exists('Dotenv\Dotenv') && file_exists(CRAFT_BASE_PATH . '/.env')) {
            \Dotenv\Dotenv::create(CRAFT_BASE_PATH)->load();
        }

        // Define additional PHP constants
        // (see https://craftcms.com/docs/3.x/config/#php-constants)
        define('CRAFT_ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

        // Load and run Craft
        /** @var \craft\web\Application $app */
        $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';

        $app->setAliases(['@webroot' => CRAFT_BASE_PATH . '/web']);

        return $app;
    }

    /**
     * Passthrough for the HTTP service
     */
    function get(...$args): \markhuot\craftpest\test\Response
    {
        return Pest::getInstance()->http->get(...$args);
    }

}
