<?php

namespace markhuot\craftpest;

use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\services\Plugins;
use yii\base\Event;

/**
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init()
    {
        $this->controllerNamespace = 'markhuot\\craftpest\\console';

        parent::init();

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if (is_a($event->plugin, Pest::class)) {
                    if (!is_dir(CRAFT_BASE_PATH . '/tests')) {
                        mkdir(CRAFT_BASE_PATH . '/tests');
                    }
                    if (!file_exists(CRAFT_BASE_PATH . '/tests/Pest.php')) {
                        copy(__DIR__ . '/../stubs/init/ExampleTest.php', CRAFT_BASE_PATH . '/tests/ExampleTest.php');
                        copy(__DIR__ . '/../stubs/init/Pest.php', CRAFT_BASE_PATH . '/tests/Pest.php');
                    }
                    if (!file_exists(CRAFT_BASE_PATH . '/phpunit.xml')) {
                        copy(__DIR__ . '/../stubs/init/phpunit.xml', CRAFT_BASE_PATH . '/phpunit.xml');
                    }
                }
            }
        );
    }
}
