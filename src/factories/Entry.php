<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\models\EntryType;
use craft\models\Section;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;

/**
 * @TODO a lot of these should be copied up to the element factory
 * @method title(string $title)
 * @method slug(string $slug)
 * @method uri(string $uri)
 * @method enabled(bool $enabled)
 * @method parent(\craft\elements\Entry|Entry|string|int $parent)
 */
class Entry extends Element
{
    /** @var EntryType */
    protected $type;

    /** @var string|\craft\models\Section */
    protected $sectionIdentifier;

    /**
     * Set the section for the entry to be created. You may pass a section
     * in three ways,
     * 
     * 1. a section object (typically after creating one via the `Section` factory)
     * 2. a section id
     * 3. a section handle
     *
     * @param \craft\models\Section|string $identifier
     * @return self
     */
    function section($identifier) {
        $this->sectionIdentifier = $identifier;

        return $this;
    }

    /**
     * Set the entry type
     *
     * @param string $handle The entry type handle
     */
    function type($handle) {

    }

    function postDate(\DateTime|string|int $value)
    {
        $this->setDateField('postDate', $value);

        return $this;
    }

    function expiryDate(\DateTime|string|int $value)
    {
        $this->setDateField('expiryDate', $value);

        return $this;
    }

    function setDateField($key, $value)
    {
        if (is_string($value)) {
            $value = new \DateTime($value);
        }

        $this->attributes[$key] = $value;
    }

    function author(\craft\web\User|string|int $user)
    {
        if (is_numeric($user)) {
            $user = \Craft::$app->users->getUserById($user);
        }
        else if (is_string($user)) {
            $user = \Craft::$app->users->getUserByUsernameOrEmail($user);
        }

        if (!is_a($user, \craft\elements\User::class)) {
            throw new \Exception('You must pass a User object or a valid user ID or username to the `author()` method.');
        }

        $this->attributes['authorId'] = $user->id;

        return $this;
    }

    /**
     * Infer the section based on the class name
     *
     * @return int
     */
    function inferSectionId() {
        if (is_a($this->sectionIdentifier, \craft\models\Section::class)) {
            $section = $this->sectionIdentifier;
        }
        else if (is_numeric($this->sectionIdentifier)) {
            $section = \Craft::$app->sections->getSectionById($this->sectionIdentifier);
        }
        else if (is_string($this->sectionIdentifier)) {
            $section = \Craft::$app->sections->getSectionByHandle($this->sectionIdentifier);
        }
        else {
            $reflector = new \ReflectionClass($this);
            $className = $reflector->getShortName();
            $sectionHandle = lcfirst($className);
            $section = \Craft::$app->sections->getSectionByHandle($sectionHandle);
        }


        if (empty($section)) {
            throw new \Exception("A section could not be inferred from this factory. Make sure you set a ::factory()->section(\"handle\") in your test. Tried to find `{$this->sectionIdentifier}");
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
