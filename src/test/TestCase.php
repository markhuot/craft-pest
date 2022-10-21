<?php

namespace markhuot\craftpest\test;

use Composer\Plugin\PluginManager;
use craft\services\Plugins;
use craft\web\Application;
use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\traits\DatabaseAssertions;
use markhuot\craftpest\web\TestableResponse;
use yii\base\Event;

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
        // Get traits added to the base TestCase, this actual file
        $reflect = new \ReflectionClass($this);
        $traits = $reflect->getParentClass()->getTraits();

        // Get traits added via Pest's `uses()` logic
        $traits = array_merge($traits, $reflect->getTraits());

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

        // Setting this causes Yii to skip calls to `exit()` and instead throw a catchable exception
        // in place of `exit()`. Setting this allows us to handle the response from a `->get()` call
        // uniquely in testing. We don't actually want to write to the screen and `exit()` in test.
        // Instead we'll catch that exception and return the response to the tester. That's possible
        // because this ENV disables `exit()` and gives us that catchable exception.
        define('YII_ENV_TEST', true);

        // Since we're mostly making Http requests via the `\Craft::$app` we want to simulate
        // a web request in our bootstrap. Without these, the script filename will be used such
        // as ./vendor/bin/pest, which will mess up Craft's internal routing. Setting these
        // to dummy values allows the inital bootstrap to appear as if it's a web request.
        //
        // Note: these are, basically, ignored on subsequent `->get()` requests because we build
        // a whole new `Request` object manually within the `http\requests\WebRequest` class.
        // You probably don't want to change these. You should probably be looking in `WebRequest`
        // instead.
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';

        // Craft bootstraps the request _very_ early in the lifecycle so plugins, modules, etc can
        // all have guranteed access to things like $request->isConsoleRequest. Unfortunately the 
        // default functionality looks at `PHP_SAPI === 'cli'`, which will be true in our test 
        // environment. Because the default request looks like a console request any plugin bootstrap
        // will then register its console controller namespace. For _most_ prople this is not what we
        // want. Most people are typically testing web requests. So, for the majority we reset the
        // isConsoleRequest flag back to web. Eventually this should probably be configurable.

        /** @var \craft\web\Application $app */
        // Load and run Craft. We have two ways we can do this. The safe way or the flexible way.
        //
        // First, the safe way allows us to just lean on Craft and let it do its thing. The problem
        // here is that there are no hooks for us to get in to the bootstrapping lifecycle and
        // therefore no way for us to inject customizations. Specifically, we need some hooks in
        // to the Application boot. Normally Craft is bootstrapped from nothing on every request.
        // We don't do that in testing, because Yii uses too many singletons and completely dies
        // when you try to `createObject(Application)` multiple times. So, unfortunately, this
        // very nice, very clean, approach is out.
        // $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';
        //
        // Instead, we read in both bootstrapping files and run them through `eval` with one
        // important change. We add in a `configOverride($config)` function/hook that we can
        // call to adjust the initial config in test. Most importantly, we're using it to subclass
        // the web\Application to our own Application that we have more control over.
        //
        // This will break on _every_ single Craft version and need constant tweaking. Until
        // there's a better way to override the Application component, this is what we're left
        // with.
        //
        // Note: I'm only okay with this because we're doing it in Test. I would _never_ do this
        // in a production environment with code that serves content to users.
        $app = (function () {
            $configOverride = function ($config) {
                $config['class'] = \markhuot\craftpest\web\Application::class;

                return $config;
            };
            $override = '$config = $configOverride($config);'."\n";

            $webBootstrapSrc = file_get_contents(CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php');
            $webBootstrapSrc = preg_replace('/^<\?php/', '', $webBootstrapSrc);
            $webBootstrapSrc = preg_replace('/^return.*$/m', '', $webBootstrapSrc);
            $bootstrapSrc = file_get_contents(CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/bootstrap.php');
            $bootstrapSrc = preg_replace('/^<\?php/', '', $bootstrapSrc);
            $bootstrapSrc = preg_replace('/^(\$app = )/m', $override. '$1', $bootstrapSrc);
            eval($webBootstrapSrc . "\n" . $bootstrapSrc);

            // This comes out of the eval. PHPStan has no chance of knowing what's happening
            // here. I barely know what's happening here.
            return $app; // @phpstan-ignore-line,
        })();

        $app->projectConfig->writeYamlAutomatically = false;

        return $app;
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
