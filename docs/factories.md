# Factories
Elements (and some models) within Craft can be generated at test time utilizing
Craft Pest's built in Factory methods. Factories abstract away the boilerplate
of creating elements within the Craft database and allow you to concentrate
more on the action of the test than the act of setting up the environment to
be tested.
For example, you could create a new section, with specific fields. Then, create
an entry in that new section and, finally, test that a template renders the
detail view of the entry correctly.
```php
it('renders detail views', function () {
  $plainTextField = Field::factory()
    ->type(\craft\fields\PlainText::class)
    ->create();

  $section = Section::factory()
    ->template('_test/entry')
    ->fields([$plainTextField])
    ->create();
   
  $text = 'plain text value';
  $entry = Entry::factory()
    ->section($section->handle)
    ->{$plainTextField->handle}($text)
    ->create(); 
  
  get($entry->uri)
    ->assertOk()
    ->assertSee($text)
});
```
That example uses most of the common factory methods.

## factory()
Create a new factory by calling `::factory()` on the type of element to be
created, such as `Entry::factort()` or `Asset::factory()`.

## muteValidationErrors(bool $muted = true)
Typically the `->create()` method throws exceptions when a validation error
occurs. Calling `->muteValidationErrors()` will mute those exceptions and return
the unsaved element with the `->errors` property filled out.

## set($key, $value = NULL)
Set an attribute and return the factory so you can chain on multiple field
in one call, for example,

```php
Asset::factory()
  ->set('volume', 'someVolumeHandle')
  ->set('fooField', 'the value of fooField')
```

The an attributes value can be set in three ways,

1. a scalar value, like a steing or integer
2. a callable that returns a scalar. In this case the callable will be
passed an instance of faker
3. an array containing either of the first two ways

```php
Entry::factory()
  ->set('title, 'SOME GREAT TITLE')
  ->set('title', fn ($faker) => str_to_upper($faker->sentence))
  ->set([
    'title' => 'SOME GREAT TITLE',
    'title' => fn ($faker) => str_to_upper($faker->sentence)
  ])
```

Sometimes you need to ensure an attribute is unset, not just null. If you
set an attribute's value to `Factory::NULL` it will be removed from the
model before it is made.

## count(int $count = 1)
Set the number of entries to be created.

This method affects the return of `->create()` and `->make()`. When only a
single model is created the single model will be returned. When 2 or more
models are created a collection of models will be returned.

```php
Entry::factory()->count(3)->make() // array of three Entry objects
Entry::factory()->count(1)->make() // returns a single Entry

## definition(int $index = 0)
The faker definition for this model. Each model has its own unique definitions. For example
an Entry will automatically set the title, while an Asset will automatically set the source.

Factories are meant to be extended and subclasses should almost certainly overwrite the 
`definition()` method to set sensible defaults for the model. The definition can overwrite
any fields that the model may need. For example a `Post` factory may look like this,

```php
use \markhuot\craftpest\factories\Category;
class Post extends \markhuot\craftpest\factories\Entry
{
  function definition()
  {
    return [
      // The entry's title field
      'title' => $this->faker->sentence,          
      // A Category field takes an array of category ids or category factories
      'category' => Category::factory()->count(3), 
      // Generate three body paragraphs of text
      'body' => $this->faker->paragraphs(3),
    ];
  }
}
```

## inferences(array $definition = array ())
When building a model's definition the inferences are the last step before the
model is build. tThis provides a place to take all the statically defined attributes
and make some dynamic assumptions based on it.

For example the `Entry` factory uses this to set the `slug` after the title has been
set by definiton or through a `->set()` call.

When creating custom factories, this will most likely meed to be overridden.

## make($definition = array ())
Instantiate an Model without persisting it to the database.

You may pass additional definition to further customize the model's attributes.

Because the model is not persisted it is up to the caller to ensure the model is saved
via something like `->saveElement($model)`.

## create(array $definition = array ())
Instantiate an Model and persist it to the database.

You may pass additional definition to further customize the model's attributes.

## getMadeModels()
Returns the models that were made after calling `->make()` or `->create()`.
This can be helpful if you are passing factories in to nested factories and
you need to reference them later on. For example, the following creates a
plain text field, a matrix field with a single block type containing that
plain text field, a section with the matrix field and finally an entry in
that section. Notice that we only call `->create()` on the section and let
the system figure out the rest of the inter-dependencies (like which fields
are global and which fields are matrix fields).
```php
$plainText = Field::factory()->type(PlainText::class);
$blockType = BlockType::factory()->fields($plainText);
$matrix = MatrixField::factory()->blockTypes($blockType);
$section = Section::factory()->fields($matrix)->create();
$entry = Entry::factory()
  ->section($section->handle)
  ->set(
    $matrix->getMadeModels()->first()->handle,
    Block::factory()
      ->set($plainText->getMadeModels()->first()->handle, 'foo')
      ->count(3)
  );
```