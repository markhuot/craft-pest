<?php

namespace markhuot\craftpest\behaviors;

use craft\base\FieldInterface;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Color;
use craft\fields\Date;
use craft\fields\Lightswitch;
use craft\fields\PlainText;
use yii\base\Behavior;

/**
 * @property FieldInterface $owner
 */
class FieldTypeHintBehavior extends Behavior
{
    function getTypeHintFactory()
    {
        $handle = $this->owner->handle;

        switch (get_class($this->owner)) {
            case Lightswitch::class:
                return 'boolean $'.$handle;

            case PlainText::class:
            case Color::class:
                return 'string $'.$handle;

            case Categories::class:
                return 'int[]|\craft\elements\Category[] $'.$handle;

            case Assets::class:
                return 'int[]|\craft\elements\Asset[] $'.$handle;

            case Date::class:
                return 'int|string|\DateTime $'.$handle;
        }

        return 'mixed $'.$handle;
    }
}
