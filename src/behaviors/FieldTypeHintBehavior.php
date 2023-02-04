<?php

namespace markhuot\craftpest\behaviors;

use craft\base\Field;
use craft\base\FieldInterface;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Color;
use craft\fields\Date;
use craft\fields\Dropdown;
use craft\fields\Entries;
use craft\fields\Lightswitch;
use craft\fields\PlainText;
use yii\base\Behavior;

/**
 * @property Field $owner
 */
class FieldTypeHintBehavior extends Behavior
{
    function getFactoryTypeHint()
    {
        $handle = $this->owner->handle;

        switch (get_class($this->owner)) {
            case Lightswitch::class:
                return 'boolean $'.$handle;

            case PlainText::class:
            case Color::class:
                return 'string $'.$handle;

            case Categories::class:
                return 'int[]|\craft\elements\Category[]|\markhuot\craftpest\Factories\Category|\markhuot\craftpest\Factories\Category[] $'.$handle;

            case Entries::class:
                return 'int[]|\craft\elements\Entry[]|\markhuot\craftpest\Factories\Entry|\markhuot\craftpest\Factories\Entry[] $'.$handle;

            case Assets::class:
                return 'int[]|\craft\elements\Asset[]|\markhuot\craftpest\Factories\Asset|\markhuot\craftpest\Factories\Asset[] $'.$handle;

            case Date::class:
                return 'int|string|\DateTime $'.$handle;

            case Dropdown::class:
                return 'string $'.$handle;
        }

        return 'mixed $'.$handle;
    }
}
