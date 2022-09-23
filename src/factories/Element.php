<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\fields\Matrix;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Entries;
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
    function resolveFactories(\craft\base\Field $field, array $values)
    {
        // Resolve out any factories
        foreach ($values as &$value) {
            if (is_subclass_of($value, Factory::class)) {
                // This is unfortunately an artifact of the current Craft model structure. We can't
                // ->make() sub models because Craft doesn't know what to do with them on save and
                // doesn't provide us any native way to get back the made object so we can save it
                // ourselves. For example, setting an entries field to a bunch of un-saved entries
                // will go in to the model okay, but when you try to pull them back out to save them
                // you get an EntryQuery with no access to the raw array of unsaved entries.
                // Because of that we call ->create() here on all nested factories.
                $result = $value->create();

                if (in_array(get_class($field), [Entries::class, Categories::class, Assets::class])) {
                    $value = $result->id;
                }
                else {
                    $value = $result;
                }
            }
        }

        // @TODO this definitely doesn't belong here in the generic element factory
        if (is_a($field, Matrix::class)) {
            $values = collect($values)->mapWithKeys(function ($item, $index) {
                return ['new' . ($index + 1) => $item];
            })->toArray();
        }

        return $values;
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

            if ($this->isElementReference($field)) {
                $value = $this->resolveFactories($field, array_wrap($value));
            }

            // Set any custom fields
            $element->setFieldValue($key, $value);
        }

        return $element;
    }

    protected function isElementReference($field)
    {
        return in_array(get_class($field), [
            Entries::class,
            Assets::class,
            Categories::class,
            Matrix::class,
        ]);
    }

}
