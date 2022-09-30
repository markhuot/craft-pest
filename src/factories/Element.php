<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\fields\Matrix;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Entries;
use Illuminate\Support\Collection;
use function markhuot\craftpest\helpers\base\collection_wrap;
use function markhuot\craftpest\helpers\base\array_wrap;

abstract class Element extends Factory
{
    /**
     * The faker definition
     *
     * @return array
     */
    function definition(int $index = 0) {
        return [
            'title' => $this->faker->sentence,
        ];
    }

    /**
     * Persist the entry to storage
     */
    function store($element) {
        if (!\Craft::$app->elements->saveElement($element)) {
            throw new \Exception(implode(" ", $element->getErrorSummary(false)));
        }
    }

    /**
     * Recursively resolve nested factories
     */
    function resolveFactories(array $values)
    {
        // for legacy reasons ->create can either return a model or a collection of models.
        // Because of this, when we resolve factories we could end up with nested arrays of
        // models. We'll keep track of our factory indexes here and, if they returned a
        // collection we'll go back after the fact and flatten them down.
        $flattenIndexes = [];

        // Resolve out any factories
        foreach ($values as $index => $value) {
            if (is_subclass_of($value, Factory::class)) {
                // This is unfortunately an artifact of the current Craft model structure. We can't
                // ->make() sub models because Craft doesn't know what to do with them on save and
                // doesn't provide us any native way to get back the made object so we can save it
                // ourselves. For example, setting an entries field to a bunch of un-saved entries
                // will go in to the model okay, but when you try to pull them back out to save them
                // you get an EntryQuery with no access to the raw array of unsaved entries.
                // Because of that we call ->create() here on all nested factories.
                $values[$index] = $value->create();
                $flattenIndexes[] = $index;
            }
        }

        // Now that the factories have been resolved we can flatten any factories that generated
        // multiple models via `->count(5)`, for example.
        $return = collect([]);
        foreach ($values as $index => $value) {
            if (in_array($index, $flattenIndexes) && is_a($value, Collection::class)) {
                $return = $return->concat($value);
            }
            else {
                $return->push($value);
            }
        }

        return $return;
    }

    protected function setAttributes($attributes, $element)
    {
        // Set the element native fields first (ignoring any custom fields)
        $modelKeys = array_keys($element->fields());
        foreach ($attributes as $key => $value) {
            if (in_array($key, $modelKeys)) {
                $element->{$key} = $value;
                unset($attributes[$key]);
            }
        }

        // render out any nested factories while setting the custom field values
        foreach ($attributes as $key => &$value) {
            $field = $element->fieldLayout->getFieldByHandle($key);

            if (in_array(get_class($field), [
                Entries::class,
                Assets::class,
                Categories::class,
            ])) {
                $value = $this->resolveFactories(array_wrap($value))->map(function ($element) {
                    if (is_numeric($element)) {
                        return $element;
                    }
                    if (is_object($element) && !empty($element->id)) {
                        return $element->id;
                    }

                    throw new \Exception('Could not determine the ID of the reference.');
                })->toArray();
            }

            if (is_a($field, Matrix::class)) {
                $value = $this->resolveFactories(array_wrap($value))
                    ->mapWithKeys(function ($item, $index) {
                        return ['new' . ($index + 1) => $item];
                    })->toArray();
            }

            // Set any custom fields
            $element->setFieldValue($key, $value);
        }

        return $element;
    }

}
