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
     * The faker definition
     *
     * @return array
     */
    protected function definition() {
        return [];
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
    function section($name) {
        if (is_numeric($name)) {
            $this->sectionId = $name;
        }

        $section = \Craft::$app->sections->getSectionByHandle($name);
        if (!$section) {
            throw new \Exception("Unknown section `{$name}`");
        }

        $this->section = $section;
        $this->type = $section->entryTypes[0];

        return $this;
    }

    function getSectionAndType() {
        if ($this->section === null) {
            $this->inferSection();
        }

        if ($this->type === null) {
            $this->inferType();
        }

        return [$this->section, $this->type];
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
     * @return $this
     */
    function inferSection() {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $sectionHandle = lcfirst($className);
        $this->section = \Craft::$app->sections->getSectionByHandle($sectionHandle);

        return $this;
    }

    /**
     * Infer the type based on the class name
     *
     * @return $this
     */
    function inferType() {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $typeHandle = lcfirst($className);
        $matches = array_filter($this->section->entryTypes, fn($e) => $e->handle === $typeHandle);
        if (count($matches) === 0) {
            $matches = $this->section->entryTypes;
        }
        $this->type = $matches[0];

        return $this;
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

        [$section, $type] = $this->getSectionAndType();
        $entry->sectionId = $section->id;
        $entry->typeId = $type->id;

        // array_merge to ensure we get a copy of the array and not a reference
        $attributes = array_merge($this->attributes);

        if ($this->hasDefinition()) {
            foreach ($this->definition() as $key => $value) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = ($value);
                }
            }
        }

        $modelFields = ['title' => null, 'slug' => null];
        $customFields = [];

        foreach ($attributes as $key => $value) {
            if (in_array($key, array_keys($modelFields))) {
                $modelFields[$key] = $value;
            }
            else {
                $customFields[$key] = $value;
            }
        }

        $entry->setAttributes(array_filter($modelFields));
        $entry->setFieldValues(array_filter($customFields));

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
