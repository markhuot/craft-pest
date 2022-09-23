<?php

namespace markhuot\craftpest\factories;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use yii\base\BaseObject;
use function markhuot\craftpest\helpers\base\collection_wrap;

abstract class Factory {

    /**
     * A null placeholder to signify that a field should not be set during the make flow
     *
     * @var string
     */
    const NULL = '__NULL__';

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
    final function __construct($faker=null) {
        $this->faker = $faker ?? Faker::create();
    }

    /**
     * Set custom fields
     */
    function __call(string $method, array $args) {
        $setter = 'set' . ucfirst($method);
        if (method_exists($this, $setter)) {
            return $this->{$setter}(...$args);
        }

        if (count($args) > 1) {
            $this->attributes[$method] = array_merge($this->attributes[$method] ?? [], $args);
        }
        else {
            $this->attributes[$method] = $args[0] ?? null;;
        }

        return $this;
    }

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
     * Get the element to be generated.
     *
     * @return mixed
     */
    abstract function newElement();

    /**
     * Set the number of entries to be created
     */
    function count(int $count=1) {
        $this->count = $count;

        return $this;
    }

    /**
     * The faker definition
     */
    function definition(int $index = 0) {
        return [];
    }

    /**
     * Definitions are complex beasts so simplify all the logic around resolving a definition
     * to an actionable array here.
     *
     * @param $definition
     *
     * @return array
     */
    function resolveDefinition($definition) {
        if (is_callable($definition)) {
            $definition = $definition($this->faker);
        }

        if (!is_array($definition)) {
            $definition = [];
        }

        // run two passes so all the "static"/non-callable definitions
        // are reesolved first and then do the callables
        foreach ($definition as $key => &$value) {
            if (is_callable($value)) {
                continue;
            }

            if  (method_exists($this, $key)) {
                $this->{$key}($value);
                unset($definition[$key]);
            }
        }

        // now that all the "static"/non-callables have been resolved
        // we can run the callables and pass in the existing
        // values for reference
        foreach ($definition as $key => &$value) {
            if (!is_callable($value)) {
                continue;
            }

            $value = $value($this->faker, $definition);
        }

        return $definition;
    }

    function inferences(array $definition=[]) {
        return $definition;
    }

    /**
     * Instantiate an Entry
     *
     * @return \craft\elements\Entry|Collection
     */
    function make($definition=[]) {
        $elements = collect([])
            ->pad($this->count, null)
            ->map(fn () => $this->internalMake($definition));

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
    function create(array $definition=[]) {
        $elements = collection_wrap($this->make($definition));

        $elements = $elements->map(function ($element) {
            $this->store($element);
            if (!empty($element->errors)) {
                throw new \Exception(json_encode($element->errors));
            }

            return $element;
        });

        if ($this->count === 1) {
            return $elements->first();
        }

        return $elements->reverse();
    }

    abstract function store($element);

    protected function getAttributes($definition=[])
    {
        $attributes = array_merge(
            $this->resolveDefinition($this->definition()),
            $this->resolveDefinition($this->attributes),
            $this->resolveDefinition($definition),
        );

        // Once we have all the attributes from the definition give consumers
        // one final chance to update the attributes. This is where we'll usually
        // take defined names and turn them in to handles or take handles and
        // turn them in to IDs
        $attributes = array_merge($attributes, $this->inferences($attributes));

        // final pass to clean up resolved fields
        foreach ($attributes as $key => $value) {

            // filter out any null values
            if ($value === self::NULL) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    protected function setAttributes($attributes, $element)
    {
        foreach ($attributes as $key => $value) {
            $element->{$key} = $value;
        }

        return $element;
    }

    /**
     * Generate the element
     */
    protected function internalMake(array $definition=[])
    {
        $element = $this->newElement();

        $attributes = $this->getAttributes($definition);

        $element = $this->setAttributes($attributes, $element);

        return $element;
    }

}
