<?php

namespace markhuot\craftpest;

use craft\base\Field;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\services\Plugins;
use markhuot\craftpest\actions\RenderCompiledClasses;
use markhuot\craftpest\behaviors\ExpectableBehavior;
use markhuot\craftpest\behaviors\FieldTypeHintBehavior;
use markhuot\craftpest\behaviors\TestableElementBehavior;
use yii\base\Event;

/**
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init()
    {
        $this->controllerNamespace = 'markhuot\\craftpest\\controllers';

        if (\Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'markhuot\\craftpest\\console';
        }

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

        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = ExpectableBehavior::class;
                $event->behaviors[] = TestableElementBehavior::class;
            }
        );

        Event::on(
            Field::class,
            Field::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = FieldTypeHintBehavior::class;
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_AFTER_SAVE_FIELD,
            function () {
                (new RenderCompiledClasses)->handle();
            }
        );
    }
}
