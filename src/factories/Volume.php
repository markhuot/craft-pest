<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\base\VolumeInterface;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\models\Section_SiteSettings;
use craft\volumes\Local;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use markhuot\craftpest\test\RefreshesDatabase;
use yii\base\Event;
use function markhuot\craftpest\helpers\base\array_wrap;
use function markhuot\craftpest\helpers\base\collection_wrap;

class Volume extends Factory {

    /**
     * Get the element to be generated
     *
     * @return VolumeInterface
     */
    function newElement()
    {
        return new \craft\volumes\Local;
    }

    /**
     * The faker definition
     *
     * @return array
     */
    function definition(int $index = 0) {
        $name = $this->faker->words(2, true);
        $handle = StringHelper::toCamelCase($name);

        return [
            'name' => $name,
            'handle' => $handle,
            'path' => \Craft::getAlias('@storage') . '/volumes/' . $handle . '/',
        ];
    }

    /**
     * Persist the entry to local
     *
     * @param VolumeInterface $element
     */
    function store($element) {
        \Craft::$app->getVolumes()->saveVolume($element);
    }

    /**
     * @return VolumeInterface|Collection
     */
    function create(array $definition=[])
    {
        $volumes = parent::create($definition);

        Event::on(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', function () use ($volumes) {
            foreach (collection_wrap($volumes) as $volume) {
                if (is_a($volume, Local::class)) {
                    // FileHelper::removeDirectory($volume->getRootPath());
                }
            }
        });

        return $volumes;
    }

}
