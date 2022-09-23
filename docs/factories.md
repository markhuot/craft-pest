# Factories

Elements (and some models) within Craft can be generated at test time utilizing Craft Pest's built in Factory methods. Factories abstract away the boilerplate of creating elements within the Craft database and allow you to concentrate more on the action of the test than the act of setting up the environment to be tested.

For example, you could create a new section, with specific fields. Then, create an entry in that new section and, finally, test that a template renders the detail view of the entry correctly.

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

That example uses most of the common factory methods, outlined in more detail below.

## Methods
All factories share some common methods to generate elements. More specific factories (outlined later) are responsible for adding specific methods for interacting with those element types. For example, all factories can be `->create()`-ed but only the field factory can specify a field's `->type()`.

### ->create()
Will create the element in the database. The created element is returned after being fully created. If there was an error during the creation the element's `->errors` will be filled.

### ->make()
Works the same as `->create()` but does not persist the element to the database. It is up to the test to call `->saveElement()` if you need the element persisted.

### ->count()
When called will change `->create()` and `->make()` to generate a sequence of elements instead of a single element.

## Custom Factories
You can, and should, create your own factories specific to your site's schema. The `Entry` factory, specifically, is intended to be extended in to a `Post` or `Article` or `News` factory, depending on your site's sections.

When creating a custom factory you'll most commonly want to overwrite the `definition()` method with your own custom definition. Here is an example of a `Post` factory that extends the default `Entry` factory with some fields specific to a blog post,

```php
use \markhuot\craftpest\factories\Category;

class Post extends \markhuot\craftpest\factories\Entry
{
  function definition()
  {
    $title = $this->faker->sentence;
    $categories = Category::factory()->count(3)->create();
    $categoryIds = $categories->pluck('id')->toArray();
  
    return [
      // The section and type id can be set, if it is ambiguous. However, by
      // default the section and type will be inferred from the class name. In this
      // case Craft Pest will look for a section named Post with a single entry
      // type that is also named Post.  
      'sectionId' => $sectionId,
      'typeId' => $typeId,
    
      // The entry's title field
      'title' => $title,          
      
      // A Category field takes an array of category ids
      'category' => $categoryIds, 
      
      // Generate three body paragraphs of text
      'body' => $this->faker->paragraphs(3),
    ];
  }
}
```