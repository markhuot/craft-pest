<?php

namespace markhuot\craftpestplugin;

use craft\base\Field;
use craft\base\Plugin;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\services\Plugins;
use markhuot\craftpest\actions\RenderCompiledClasses;
use markhuot\craftpest\behaviors\ExpectableBehavior;
use markhuot\craftpest\behaviors\FieldTypeHintBehavior;
use markhuot\craftpest\behaviors\TestableElementBehavior;
use markhuot\craftpest\behaviors\TestableElementQueryBehavior;
use yii\base\Event;

/**
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init()
    {
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
