<?php

namespace markhuot\craftpest\factories;

use craft\models\EntryType;
use craft\models\Section;
use Faker\Factory;

/**
 * @method self title(string $name) Set the title
 */
class Entry {

    static $namespace = 'factories';

    /** @var Section */
    protected $section;

    /** @var EntryType */
    protected $type;

    /** @var string */
    protected $title;

    /** @var \Faker\Generator */
    protected $faker;

    /**
     * Insert deps
     */
    function __construct($faker=null) {
        $this->faker = $faker ?? Factory::create();
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

        if (property_exists($this, $method)) {
            $this->{$method} = $value;
        }

        if (false) {
            // $this->customFields[$method] = $value;
        }

        return $this;
    }

    /**
     * Persist the entry to our storage
     *
     * @param int $count How many entries to create
     *
     * @return array|\craft\elements\Entry
     */
    function create($count=1) {
        $entry = new \craft\elements\Entry();

        if ($count > 1) {
            $result = [];
            for ($i=0; $i<$count; $i++) {
                $result[] = $this->create();
            }
            return $result;
        }

        [$section, $type] = $this->getSectionAndType();
        $entry->sectionId = $section->id;
        $entry->typeId = $type->id;

        if ($this->hasDefinition()) {
            foreach ($this->definition() as $key => $value) {
                $this->{$key}($value);
            }
        }

        if ($this->title !== null) {
            $entry->title = $this->title;
        }

        if (!\Craft::$app->elements->saveElement($entry)) {
            throw new \Exception(implode(" ", $entry->getErrorSummary(false)));
        }

        return $entry;
    }

}
