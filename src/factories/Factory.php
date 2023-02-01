<?php

namespace markhuot\craftpest\factories;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use markhuot\craftpest\events\FactoryStoreEvent;
use markhuot\craftpest\exceptions\ModelStoreException;
use yii\base\BaseObject;
use yii\base\Event;
use function markhuot\craftpest\helpers\base\collection_wrap;

/**
 * # Factories
 *
 * Elements (and some models) within Craft can be generated at test time utilizing
 * Craft Pest's built in Factory methods. Factories abstract away the boilerplate
 * of creating elements within the Craft database and allow you to concentrate
 * more on the action of the test than the act of setting up the environment to
 * be tested.
 *
 * For example, you could create a new section, with specific fields. Then, create
 * an entry in that new section and, finally, test that a template renders the
 * detail view of the entry correctly.
 *
 * ```php
 * it('renders detail views', function () {
 *   $plainTextField = Field::factory()
 *     ->type(\craft\fields\PlainText::class)
 *     ->create();
 * 
 *   $section = Section::factory()
 *     ->template('_test/entry')
 *     ->fields([$plainTextField])
 *     ->create();
 *    
 *   $text = 'plain text value';
 *   $entry = Entry::factory()
 *     ->section($section->handle)
 *     ->{$plainTextField->handle}($text)
 *     ->create(); 
 *   
 *   get($entry->uri)
 *     ->assertOk()
 *     ->assertSee($text)
 * });
 * ```
 *
 * That example uses most of the common factory methods.
 */
abstract class Factory {

    /**
     * A null placeholder to signify that a field should not be set during the make flow
     */
    const NULL = '__NULL__';

    /**
     * An event fired before a model is stored to the persistent storage
     */
    const EVENT_BEFORE_STORE = 'beforeStore';

    /**
     * An event fired after a model is stored to the persistent storage
     */
    const EVENT_AFTER_STORE = 'afterStore';

    /** @var \Faker\Generator */
    protected $faker;

    /** @var array */
    protected $attributes = [];

    /** @var array|null */
    protected $definition = null;

    /** @var int */
    protected $count = 1;

    /** @var bool */
    protected $muted = false;

    /**
     * Any models this factory eventually ends up making. Stored in the factory
     * so you can pull them back out if you only have reference to the factory
     * and the ->make/->create happens deeper because of nesting
     *
     * @var Collection
     */
    protected $models;

    /**
     * Attributes that should be set before any others so that they may inform
     * future processing. For example, set the `sectionId` before you set any
     * custom fields so that you can get the field layout for the section.
     *
     * @var array<int, string>
     */
    protected $priorityAttributes = [];

    /**
     * Insert deps
     * @internal
     */
    final function __construct($faker=null)
    {
        $this->faker = $faker ?? Faker::create();
    }

    /**
     * Set custom fields
     * @internal
     */
    function __call(string $method, array $args)
    {
        $reflect = new \ReflectionClass($this);
        $traits = $reflect->getTraits();
        while($reflect=$reflect->getParentClass()) {
            $traits = array_merge($traits, $reflect->getTraits());
        }
        foreach ($traits as $trait) {
            $handlesMethodName = 'handlesMagic' . $trait->getShortName() . 'Call';
            $callsMethodName = 'callMagic' . $trait->getShortName() . 'Call';
            if ($trait->hasMethod($handlesMethodName)) {
                if ($this->$handlesMethodName($method, $args)) {
                    $this->$callsMethodName($method, $args);
                    return $this;
                }
            }
        }

        if (count($args) > 1) {
            $this->attributes[$method] = array_merge($this->attributes[$method] ?? [], $args);
        }
        else {
            $this->attributes[$method] = $args[0] ?? null;
        }

        return $this;
    }

    /**
     * Whether an attribute has been set
     * @internal
     */
    function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Create a new factory by calling `::factory()` on the type of element to be
     * created, such as `Entry::factory()` or `Asset::factory()`.
     */
    static function factory() {
        return (new static);
    }

    /**
     * Typically the `->create()` method throws exceptions when a validation error
     * occurs. Calling `->muteValidationErrors()` will mute those exceptions and return
     * the unsaved element with the `->errors` property filled out.
     */
    function muteValidationErrors(bool $muted = true)
    {
        $this->muted = $muted;

        return $this;
    }

    /**
     * Set an attribute and return the factory so you can chain on multiple field
     * in one call, for example,
     * 
     * ```php
     * Asset::factory()
     *   ->set('volume', 'someVolumeHandle')
     *   ->set('fooField', 'the value of fooField')
     * ```
     * 
     * The an attributes value can be set in three ways,
     * 
     * 1. a scalar value, like a string or integer
     * 2. a callable that returns a scalar. In this case the callable will be
     * passed an instance of faker
     * 3. an array containing either of the first two ways
     * 
     * ```php
     * Entry::factory()
     *   ->set('title, 'SOME GREAT TITLE')
     *   ->set('title', fn ($faker) => str_to_upper($faker->sentence))
     *   ->set([
     *     'title' => 'SOME GREAT TITLE',
     *     'title' => fn ($faker) => str_to_upper($faker->sentence)
     *   ])
     * ```
     * 
     * Sometimes you need to ensure an attribute is unset, not just null. If you
     * set an attribute's value to `Factory::NULL` it will be removed from the
     * model before it is made.
     */
    function set($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        }
        else {
            $this->attributes[$key] = $value;
        }


        return $this;
    }

    /**
     * Get the element to be generated.
     * @internal
     */
    abstract function newElement();

    /**
     * Set the number of entries to be created.
     * 
     * This method affects the return of `->create()` and `->make()`. When only a
     * single model is created the single model will be returned. When 2 or more
     * models are created a collection of models will be returned.
     * 
     * ```php
     * Entry::factory()->count(3)->make() // array of three Entry objects
     * Entry::factory()->count(1)->make() // returns a single Entry
     */
    function count(int $count=1) {
        $this->count = $count;

        return $this;
    }

    /**
     * The faker definition for this model. Each model has its own unique definitions. For example
     * an Entry will automatically set the title, while an Asset will automatically set the source.
     * 
     * Factories are meant to be extended and subclasses should almost certainly overwrite the 
     * `definition()` method to set sensible defaults for the model. The definition can overwrite
     * any fields that the model may need. For example a `Post` factory may look like this,
     * 
     * ```php
     * use \markhuot\craftpest\factories\Category;
     *
     * class Post extends \markhuot\craftpest\factories\Entry
     * {
     *   function definition()
     *   {
     *     return [
     *       // The entry's title field
     *       'title' => $this->faker->sentence,          
     *
     *       // A Category field takes an array of category ids or category factories
     *       'category' => Category::factory()->count(3), 
     *
     *       // Generate three body paragraphs of text
     *       'body' => $this->faker->paragraphs(3),
     *     ];
     *   }
     * }
     * ```
     */
    function definition(int $index = 0) {
        return [];
    }

    /**
     * Definitions are complex beasts so simplify all the logic around resolving a definition
     * to an actionable array here.
     *
     * @internal
     * @return array
     */
    function resolveDefinition($definition) {
        if (is_callable($definition)) {
            $definition = $definition($this->faker);
        }

        if (!is_array($definition)) {
            $definition = [];
        }

        // now that all the "static"/non-callables have been resolved
        // we can run the callables and pass in the existing
        // values for reference
        foreach ($definition as &$value) {
            if (!is_callable($value)) {
                continue;
            }

            $value = $value($this->faker, $definition);
        }

        return $definition;
    }

    /**
     * When building a model's definition the inferences are the last step before the
     * model is built. This provides a place to take all the statically defined attributes
     * and make some dynamic assumptions based on it.
     * 
     * For example the `Entry` factory uses this to set the `slug` after the title has been
     * set by definition or through a `->set()` call.
     * 
     * When creating custom factories, this will most likely meed to be overridden.
     */
    function inferences(array $definition=[]) {
        return $definition;
    }

    /**
     * Instantiate an Model without persisting it to the database.
     * 
     * You may pass additional definition to further customize the model's attributes.
     * 
     * Because the model is not persisted it is up to the caller to ensure the model is saved
     * via something like `->saveElement($model)`.
     *
     * @return \craft\elements\Entry|Collection
     */
    function make($definition=[]) {
        // Create the models
        $elements = collect([])
            ->pad($this->count, null)
            ->map(fn () => $this->internalMake($definition));

        // Store a reference to the created models
        $this->models = $elements;

        // If the count is one we return the first model, otherwise return
        // the full collection of models
        return ($this->count === 1) ? $elements->first() : $elements;
    }
    
    /**
     * Instantiate an Model and persist it to the database.
     * 
     * You may pass additional definition to further customize the model's attributes.
     *
     * @return \craft\elements\Entry|Collection
     */
    function create(array $definition=[]) {
        $elements = collection_wrap($this->make($definition));

        $elements = $elements->map(function ($element) {
            $beforeStoreEvent = new FactoryStoreEvent;
            $beforeStoreEvent->sender = $this;
            $beforeStoreEvent->model = $element;
            Event::trigger(static::class, static::EVENT_BEFORE_STORE, $beforeStoreEvent);

            // If our event has been canceled and is no longer valid do not perform the
            // native storage routine. Instead we'll just return the element as-is assuming
            // the event has already handled persisting it.
            if (!$beforeStoreEvent->isValid) {
                return $element;
            }

            // Try to save our model and if we get back a bad response, convert the
            // response in to an exception. We don't do anything with the exception here
            // because sometimes an exception will be thrown inside of `->store` and
            // other times it won't. Regardless somewhere in this `try` block an exception
            // will be thrown and the next `catch` block will determine what to do
            // with it.
            try {
                if (!$this->store($element)) {
                    throw new ModelStoreException($element);
                }
            }

            // If the store method threw an exception and exceptions are muted then ignore
            // the error and allow code to continue processing. You would want to do
            // this if you're expecting a validation exception.
            catch (\Throwable $e) {
                if (!$this->muted) {
                    throw $e;
                }
            }

            $afterStoreEvent = new FactoryStoreEvent;
            $afterStoreEvent->sender = $this;
            $afterStoreEvent->model = $element;
            Event::trigger(static::class, static::EVENT_AFTER_STORE, $afterStoreEvent);

            return $element;
        });

        // If the count is one return the first model, otherwise return the full
        // collection of models
        // @TODO, why is this reversed. It's necessary but I don't know why and I'd like to
        return ($this->count === 1) ? $elements->first() : $elements->reverse();
    }

    /**
     * Returns the models that were made after calling `->make()` or `->create()`.
     * This can be helpful if you are passing factories in to nested factories and
     * you need to reference them later on. For example, the following creates a
     * plain text field, a matrix field with a single block type containing that
     * plain text field, a section with the matrix field and finally an entry in
     * that section. Notice that we only call `->create()` on the section and let
     * the system figure out the rest of the inter-dependencies (like which fields
     * are global and which fields are matrix fields).
     *
     * ```php
     * $plainText = Field::factory()->type(PlainText::class);
     * $blockType = BlockType::factory()->fields($plainText);
     * $matrix = MatrixField::factory()->blockTypes($blockType);
     * $section = Section::factory()->fields($matrix)->create();
     * $entry = Entry::factory()
     *   ->section($section->handle)
     *   ->set(
     *     $matrix->getMadeModels()->first()->handle,
     *     Block::factory()
     *       ->set($plainText->getMadeModels()->first()->handle, 'foo')
     *       ->count(3)
     *   );
     * ```
     */
    function getMadeModels()
    {
        return $this->models;
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

    protected function setPriorityAttributes($attributes, $element)
    {
        foreach ($this->priorityAttributes as $key) {
            if (isset($attributes[$key])) {
                $element->{$key} = $attributes[$key];
            }
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

        [$priorityAttributes, $attributes] = collect($attributes)
            ->partition(function ($_, $key) {
                return in_array($key, $this->priorityAttributes, true);
            });

        $element = $this->setPriorityAttributes($priorityAttributes->toArray(), $element);
        $element = $this->setAttributes($attributes->toArray(), $element);

        return $element;
    }

}
