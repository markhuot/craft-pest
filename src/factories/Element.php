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
     * Persist the entry to local
     *
     * @return \craft\base\Element
     */
    function store($element) {
        if (!\Craft::$app->elements->saveElement($element)) {
            throw new \Exception(implode(" ", $element->getErrorSummary(false)));
        }
    }

    function resolveFactories($values)
    {
        foreach ($values as &$value) {
            // Resolve out any factories
            if (is_subclass_of($value, Factory::class)) {
                $value = collection_wrap($value->create())->pluck('id')->toArray();
            }

            // Resolve out any elements
            if (is_a($value, ElementInterface::class)) {
                $value = $value->id;
            }
        }

        // @todo ->flatten is wrong here, it will flatten actual arrays sent to
        // a matrix field, for example
        return collection_wrap($values)->flatten()->toArray();
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

        // @todo this is a weird place for this. maybe look in to abstracting this out
        // final pass to clean up resolved fields
        foreach ($attributes as $key => &$value) {
            // make sure element references are always arrays and run any nested
            // factories before continuing
            if ($this->isElementReference($element->fieldLayout->getFieldByHandle($key))) {
                $value = $this->resolveFactories(array_merge(
                    array_wrap($value),
                    $this->extraAttributes[$key] ?? [],
                ));
            }
        }

        // Set any custom fields
        foreach ($attributes as $key => $value) {
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
            //Matrix::class,
        ]);
    }

}
