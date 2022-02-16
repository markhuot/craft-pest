<?php

namespace markhuot\craftpest\test;

use craft\helpers\ArrayHelper;
use markhuot\craftpest\Pest;
use markhuot\craftpest\web\Application;
use markhuot\craftpest\web\Request;
use markhuot\craftpest\web\Response;

class TestCase extends \PHPUnit\Framework\TestCase {

    /** @var Application */
    protected $craft;

    protected $original;

    static $craftConfig;

    protected function setUp(): void {
        $this->craft = $this->createApplication();

        if (method_exists($this, 'refreshDatabase')) {
            $this->refreshDatabase();
        }

        if (method_exists($this, 'beginTransaction')) {
            $this->beginTransaction();
        }
    }

    protected function tearDown(): void {
        if (method_exists($this, 'endTransaction')) {
            $this->endTransaction();
        }
    }

    protected function createApplication() {
        if ($this->needsRequireStatements()) {
            $this->requireCraft();
        }

        return \Craft::createObject(self::$craftConfig);
    }

    protected function needsRequireStatements() {
        return !defined('CRAFT_BASE_PATH');
    }

    protected function requireCraft() {
        // Define path constants
        define('CRAFT_BASE_PATH', getcwd());
        define('CRAFT_VENDOR_PATH', CRAFT_BASE_PATH . '/vendor');

        // Load Composer's autoloader
        // require_once CRAFT_VENDOR_PATH . '/autoload.php';

        // Load dotenv?
        if (class_exists('Dotenv\Dotenv') && file_exists(CRAFT_BASE_PATH . '/.env')) {
            \Dotenv\Dotenv::create(CRAFT_BASE_PATH)->load();
        }

        // Define additional PHP constants
        // (see https://craftcms.com/docs/3.x/config/#php-constants)
        define('CRAFT_ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

        // Load and run Craft
        /** @var craft\web\Application $app */
        require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';
        self::$craftConfig = $config;
    }

    protected function getCraftWebApplication() {
        $this->original = \Craft::$app;

        $cmsPath = CRAFT_VENDOR_PATH . DIRECTORY_SEPARATOR . 'craftcms' . DIRECTORY_SEPARATOR . 'cms';
        $srcPath = $cmsPath . DIRECTORY_SEPARATOR . 'src';
        $config = ArrayHelper::merge(
            [
                'vendorPath' => CRAFT_VENDOR_PATH,
                'env' => CRAFT_ENVIRONMENT,
                'components' => ['config' => \Craft::$app->config],
            ],
            require $srcPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php',
            require $srcPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . "app.web.php",
            \Craft::$app->config->getConfigFromFile('app'),
            \Craft::$app->config->getConfigFromFile("app.web")
        );

        $config['class'] = \markhuot\craftpest\web\Application::class;

        $config['components']['request'] = function() {
            $config = \craft\helpers\App::webRequestConfig();
            $config['class'] = Request::class;
            /** @var \craft\web\Request $request */
            $request = \Craft::createObject($config);
            $request->csrfCookie = \Craft::cookieConfig([], $request);
            return $request;
        };

        $config['components']['response'] = function () {
            $config = \craft\helpers\App::webResponseConfig();
            $config['class'] = Response::class;
            return \Craft::createObject($config);
        };

        /** @var Application $craft */
        $craft = \Craft::createObject($config);
        // $craft->setComponents(['db' => $this->original->db]);
        // \Craft::$app = $this->original;
        return $craft;
    }

    /**
     * Passthrough for the HTTP service
     */
    function get(...$args): \markhuot\craftpest\test\Response {
        return Pest::getInstance()->http->get(...$args);
    }

}
