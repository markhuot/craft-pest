![craft-pest screen shot](./screenshot.png)

# Pest for Craft CMS

```shell
composer require markhuot/craft-pest --dev
./craft pest/test
```

Handles the setup and installation of [Pest PHP](https://pestphp.com) in to [Craft CMS](https://craftcms.com). This allows you to write tests that look something like this!

```php
it('loads the homepage')
    ->get('/')
    ->assertOk();

it('has a welcoming h1 element')
    ->expect(fn() => $this->get('/'))
    ->querySelector('h1')->text->toBe('Welcome');

it('asserts nine list items')
    ->get('/')
    ->querySelector('li')
    ->assertCount(9);

it('expects nine list items')
    ->expect(fn() => $this->get('/'))
    ->querySelector('li')
    ->count->toBe(9);

it('promotes craft')
    ->get('/')
    ->assertHeader('x-powered-by', 'Craft CMS');

it('saves news', function() {
    $titles = Craft::factory()
        ->section('news')
        ->create(3)
        ->pluck('title');

    expect(get('/'))
        ->querySelector('.news__title')
        ->text
        ->toBe($titles);
});
```
