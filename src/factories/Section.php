<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\helpers\StringHelper;
use craft\models\Section_SiteSettings;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use function markhuot\craftpest\helpers\base\collectOrCollection;
use function markhuot\craftpest\helpers\base\array_wrap;

class Section extends Factory {

    use Fieldable;

    /**
     * Get the element to be generated
     *
     * @return ElementInterface
     */
    function newElement()
    {
        return new \craft\models\Section;
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
            'type' => 'channel',
            'siteSettings' => collect(\Craft::$app->sites->getAllSites())->mapWithkeys(function ($site) use ($handle) {
                $settings = new Section_SiteSettings();
                $settings->siteId = $site->id;
                $settings->hasUrls = true;
                $settings->uriFormat = '{slug}';
                $settings->enabledByDefault = true;
                $settings->template = '_' . $handle . '/entry';

                return [$site->id => $settings];
            })->toArray(),
        ];
    }

    /**
     * Persist the entry to local
     *
     * @param \craft\models\Section $element
     * @return Collection|\craft\elements\Entry
     */
    function store($element) {
        \Craft::$app->sections->saveSection($element);
        $this->storeFields($element->entryTypes[0]->fieldLayout);
    }

}
