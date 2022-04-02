<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;

abstract class Element {

    /** @var \Faker\Generator */
    protected $faker;

    /** @var array */
    protected $attributes = [];

    /** @var array|null */
    protected $definition = null;

    /** @var int */
    protected $count = 1;

    /**
     * Insert deps
     */
    function __construct($faker=null) {
        $this->faker = $faker ?? Faker::create();
    }

    /**
     * Set custom fields
     *
     * @param string $method The method name
     * @param array $args Any args passed to the method
     */
    function __call($method, $args) {
        $value = $args[0];

        $setter = 'set' . ucfirst($method);
        if (method_exists($this, $setter)) {
            return $this->{$setter}($value);
        }

        $this->attributes[$method] = $args[0] ?? null;

        return $this;
    }

    /**
     * Get the element to be generated
     *
     * @return ElementInterface
     */
    abstract function newElement();

    /**
     * Whether an attribute has been set
     *
     * @param string $key
     *
     * @return bool
     */
    function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Create a new factory
     */
    static function factory() {
        return (new static);
    }

    /**
     * Set the number of entries to be created
     *
     * @param int $count
     *
     * @return $this
     */
    function count($count=1) {
        $this->count = $count;

        return $this;
    }

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

    function extendDefinition($extra = [], $index=0) {
        if (is_callable($extra)) {
            $extra = $extra($this->faker, $index);
        }

        return array_merge($extra, $this->definition($index));
    }

    /**
     * Instantiate an Entry
     *
     * @return \craft\elements\Entry
     */
    function make($definition=[]) {
        $elements = collect([])
            ->pad($this->count, null)
            ->map(fn ($_, $index) => $this->internalMake($definition, $index));

        if ($this->count === 1) {
            return $elements->first();
        }

        return $elements;
    }

    /**
     * Persist the entry to local
     *
     * @return Collection|\craft\elements\Entry
     */
    function create($definition=[]) {
        $elements = $this->make($definition);

        if (!is_a($elements, Collection::class)) {
            $elements = collect()->push($elements);
        }

        $elements = $elements->map(function ($element) {
            if (!\Craft::$app->elements->saveElement($element)) {
                throw new \Exception(implode(" ", $element->getErrorSummary(false)));
            }

            return $element;
        });

        if ($this->count === 1) {
            return $elements->first();
        }

        return $elements->reverse();
    }

    /**
     * Generate the element
     *
     * @param array $definition
     * @param int $index
     *
     * @return mixed
     */
    protected function internalMake($definition=[], $index=0) {
        $entry = $this->newElement();

        // array_merge to ensure we get a copy of the array and not a reference
        $attributes = array_merge($this->attributes);

        // Fill out our attributes with default/definition data if it's not already
        // set via an earlier call
        $definition = $this->extendDefinition($definition, $index);
        if (!empty($definition)) {
            foreach ($definition as $key => $value) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = $value;
                }
            }
        }

        $modelKeys = array_keys($entry->fields());
        foreach ($attributes as $key => $value) {
            if (in_array($key, $modelKeys)) {
                $entry->{$key} = $value;
            }
            else {
                $entry->setFieldValue($key, $value);
            }
        }

        return $entry;
    }

}
