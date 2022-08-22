# Factories

Test data can be created via Craft Pest's included factories. There are factories for most element types in Craft, such as Sections and Entries. A quick example of creating a section and then an entry may look like this,

```php
$section = Section::factory()->create();
$entry = Entry::factory()->section($section->handle)->create();
```

Factories are meant to reduce as much boilerplate as possible and automatically insert "faker" data in to all common fields. In the above we didn't define a section name or handle so they will be automatically filled with 2-3 random words, such as "Lorem Ipsum" as the name and `loremIpsum` as the handle. For _most_ tests where you're only concerned with the presence of elements this may be all you need.

You can see the available factories in the [factory](/src/factories) directory of the source code. Each factory has a `definition` method that specifies the default field values. The section's default field values look somthing like this,

```php
$name = $this->faker->words(2, true);
$handle = StringHelper::toCamelCase($name);

return [
  'name' => $name,
  'handle' => $handle,
  'type' => 'channel',
];
```

All defaults can be overridden when calling the factory. For example, a Section could have it's `type` overridden from `channel` to `single` via the `->type()` magic method. This would look like,

```php
Section::factory()->type('single')->create();
```

This magic `->type()` method works with all factories. So, to set an Entry's `title` field you would call `Entry::factory()->title('foo bar')` to override the default entry title.

## Creating your own factories

Factories also provide a convenient place to stash boilerplate data. And since the default factories are meant to be overridden you could take the existing `Entry` factory and create your own site-specific `Post` factory. The goal of site-specific factories is to make testing as easy and repeatable as possible. Creating new data in the site to test against should be as easy as

```php
Post::factory()->create()
```

Notice, as a tester, I don't have to worry about any implementation details of the Post entry type. If Post's have a required "Category" field the factory could automatically handle adding that. Or, if posts will always have a `body` field you could pre fill it with something like `'body' => $this->faker->paragraphs(3),`. This way your tests are more concerned with the "what" of what is being tested and not the "how" of how it's being created.
