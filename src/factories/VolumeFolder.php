<?php

namespace markhuot\craftpest\factories;

use craft\base\VolumeInterface;
use craft\helpers\StringHelper;
use Illuminate\Support\Collection;

/**
 * @method \craft\models\VolumeFolder|Collection create()
 */
class VolumeFolder extends Factory {

    /** @var VolumeInterface */
    public $volume;

    /** @var \craft\models\VolumeFolder */
    public $parent;

    function volume(VolumeInterface $volume)
    {
        $this->volume = $volume;

        return $this;
    }

    function parent(\craft\models\VolumeFolder $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    function newElement()
    {
        return new \craft\models\VolumeFolder;
    }

    /**
     * The faker definition
     *
     * @return array
     */
    function definition(int $index = 0) {
        $name = $this->faker->words(2, true);
        $path = '/'.StringHelper::toCamelCase($name).'/';
        $parentId = $this->parent?->id ?: \Craft::$app->assets->getRootFolderByVolumeId($this->volume->id)->id;

        return [
            'name' => $name,
            'path' => $path,
            'volumeId' => $this->volume->id,
            'parentId' => $parentId,
        ];
    }

    /**
     * @param \craft\models\VolumeFolder $element
     */
    function store($element) {
        \Craft::$app->assets->createFolder($element);
    }

}
