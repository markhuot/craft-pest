<?php

namespace markhuot\craftpest\factories;

use craft\models\EntryType;
use craft\models\Section;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;

class Entry {

    /** @var Section */
    protected $section;

    /** @var EntryType */
    protected $type;

    /** @var \Faker\Generator */
    protected $faker;

    /** @var int */
    protected $count = 1;

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $definition = [];

    /** @var string */
    protected $sectionHandle;

    /**
     * Insert deps
     */
    function __construct($faker=null) {
        $this->faker = $faker ?? Faker::create();
    }

    /**
     * Create a new factory
     */
    static function factory() {
        return (new static);
    }

    /**
     * Set a faker definition at run time
     *
     * @param array|callable $definition
     */
    public function define($definition) {
        if (is_callable($definition)) {
            $this->definition = $definition($this->faker);
        }
        else {
            $this->definition = $definition;
        }

        return $this;
    }

    /**
     * The faker definition
     *
     * @return array
     */
    protected function definition() {
        return $this->definition;
    }

    /**
     * Whether a faker definition is provided
     *
     * @return bool
     */
    protected function hasDefinition() {
        return !empty($this->definition());
    }

    /**
     * Set the section
     *
     * @param int $name The name of the section
     *
     * @return self
     */
    function section($handle) {
        $this->sectionHandle = $handle;
        return $this;
    }

    /**
     * Set the entry type
     *
     * @param string $handle The entry type handle
     */
    function type($handle) {

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
     * Infer the section based on the class name
     *
     * @return int
     */
    function inferSectionId() {
        if (empty($this->sectionHandle)) {
            $reflector = new \ReflectionClass($this);
            $className = $reflector->getShortName();
            $this->sectionHandle = lcfirst($className);
        }

        $section = \Craft::$app->sections->getSectionByHandle($this->sectionHandle);

        if (empty($section)) {
            throw new \Exception('A section could not be inferred from this factory. Make sure you set a ::factory()->section("handle") in your test.');
        }

        return $section->id;
    }

    /**
     * Infer the type based on the class name
     *
     * @return $this
     */
    function inferTypeId($sectionid) {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $typeHandle = lcfirst($className);
        $section = \Craft::$app->sections->getSectionById($sectionid);
        $matches = array_filter($section->entryTypes, fn($e) => $e->handle === $typeHandle);
        if (count($matches) === 0) {
            $matches = $section->entryTypes;
        }
        return $matches[0]->id;
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

    function __isset($key) {
        return isset($this->attributes[$key]);
    }

    protected function internalMake() {
        $entry = new \craft\elements\Entry();

        // array_merge to ensure we get a copy of the array and not a reference
        $attributes = array_merge($this->attributes);

        if ($this->hasDefinition()) {
            foreach ($this->definition() as $key => $value) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = $value;
                }
            }
        }

        if (!isset($attributes['sectionId'])) {
            $attributes['sectionId'] = $this->inferSectionId();
        }

        if (!isset($attributes['typeId'])) {
            $attributes['typeId'] = $this->inferTypeId($attributes['sectionId']);
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

    /**
     * Instantiate an Entry
     *
     * @return \craft\elements\Entry
     */
    function make() {
        $entries = collect([])
            ->pad($this->count, null)
            ->map(fn () => $this->internalMake());

        if ($this->count === 1) {
            return $entries->first();
        }

        return $entries;
    }

    /**
     * Persist the entry to our storage
     *
     * @return Collection|\craft\elements\Entry
     */
    function create() {
        $entries = $this->make();

        if (!is_a($entries, Collection::class)) {
            $entries = collect()->push($entries);
        }

        $entries = $entries->map(function ($entry) {
            if (!\Craft::$app->elements->saveElement($entry)) {
                throw new \Exception(implode(" ", $entry->getErrorSummary(false)));
            }

            return $entry;
        });

        if ($this->count === 1) {
            return $entries->first();
        }

        return $entries->reverse();
    }

}
