<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\models\EntryType;
use craft\models\Section;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use markhuot\craftpest\factories\Section as FactoriesSection;

/**
 * Entry Factory
 * 
 * You can easily build entries using the Entry factory.
 * 
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
     * If you do not pass a section, one will be created automatically.
     */
    function section($identifier) {
        $this->sectionIdentifier = $identifier;

        return $this;
    }

    /**
     * Set the entry type
     */
    function type($handle) {

    }

    /**
     * Set the post date by passing a `DateTime`, a string representing the date like
     * "2022-04-25 04:00:00", or a unix timestamp as an integer.
     */
    function postDate(\DateTime|string|int $value)
    {
        $this->setDateField('postDate', $value);
        
        return $this;
    }
    
    /**
     * Set the expiration date by passing a `DateTime`, a string representing the date like
     * "2022-04-25 04:00:00", or a unix timestamp as an integer.
     */
    function expiryDate(\DateTime|string|int $value)
    {
        $this->setDateField('expiryDate', $value);

        return $this;
    }

    /**
     * Date fields in Craft require a `DateTime` object.  You can use `->setDateField` to pass
     * in other representations such as a timestamp or a string.
     * 
     * ```php
     * Entry::factory()->setDateField('approvedOn', '2022-04-18 -04:00:00');
     * Entry::factory()->setDateField('approvedOn', 1665864918);
     * ```
     */
    function setDateField($key, $value)
    {
        if (is_numeric($value)) {
            $value = new \DateTime('@' . $value);
        }
        else if (is_string($value)) {
            $value = new \DateTime($value);
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Set the author of the entry. You may pass a full user object, a user ID,
     * a username, email, or a user ID.
     */
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
     * @internal
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

        if (empty($section))
        {
            $section = FactoriesSection::factory()->create();
        }

        return $section->id;
    }

    /**
     * Infer the type based on the class name
     * @internal
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
     * @internal
     */
    function newElement() {
        return new \craft\elements\Entry();
    }

    /**
     * @internal
     */
    function inferences(array $definition = []) {
        $sectionId = $this->inferSectionId();
        $typeId = $this->inferTypeId($sectionId);

        return array_merge($definition, [
            'sectionId' => $sectionId,
            'typeId' => $typeId,
        ]);
    }

}
