<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\models\EntryType;
use craft\models\Section;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;

class Entry extends Element {

    /** @var Section */
    protected $section;

    /** @var EntryType */
    protected $type;

    /** @var string */
    protected $sectionHandle;

    /**
     * Set the section
     *
     * @return self
     */
    function section(string $handle) {
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
            throw new \Exception("A section could not be inferred from this factory. Make sure you set a ::factory()->section(\"handle\") in your test. Tried to find `{$this->sectionHandle}");
        }

        return $section->id;
    }

    /**
     * Infer the type based on the class name
     */
    function inferTypeId($sectionid): int {
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
     * Get the element to be generated
     *
     * @return ElementInterface
     */
    function newElement() {
        return new \craft\elements\Entry();
    }

    function inferences(array $definition = []) {
        $sectionId = $this->inferSectionId();
        $typeId = $this->inferTypeId($sectionId);

        return array_merge($definition, [
            'sectionId' => $sectionId,
            'typeId' => $typeId,
        ]);
    }

}
