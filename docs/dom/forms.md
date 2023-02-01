# Form
You can interact with HTML forms globally on a response or by targeting the specific form
on the page. When interacting with a global form on the response the _first_ form on the
page will be used. If you have multiple forms on a page and need to access a form other than
the first you will need to target it.
```php
$response->fill('name', 'Foo Bar')->submit();
$response->form('#some-form-selector')->fill('name', 'Foo Bar')->submit();
```

## fill(string $fieldNameOrSelector, mixed $value)
Fills input or textarea
```php
$response->fill('name', 'Foo Bar');
```

## addField(string $fieldNameOrSelector, mixed $value)
Creates and fills a virtual field
This is useful to emulate DOM manipulation that actually happens via javascript such as
an Alpine or Vue component that dynamically adds a form field to the DOM.
```php
$response->addField('wysiwyg_content', '<p>foo</p>');
```

## tick(string $fieldNameOrSelector)
Set the checked state of a checkbox.
```php
$response->tick('#checkbox');
```

## untick(string $fieldNameOrSelector)
Unset the checked state of a checkbox
```php
$response->untick('#checkbox');
```

## select(string $fieldNameOrSelector, array|string|bool $value)
Selects one or many options from select
```php
$response->select('#states', ['NY', 'NJ']);
```

## click(string $buttonSelectorOrLabel)
Clicks a button on the form and submits the form
```php
$response->fill('q', 'foo')->click('Feeling lucky');
```

## submit()
Submits the form. When used directly on a response will find and submit the
first form on the page. Otherwise will use the selected form.
```php
$response->submit();
$response->form('#second-form')->submit();
```

## getFields()
Pulls the values of the form fields out of the DOM and returns them as a PHP array.
This array can then be `expect()`-ed and asserted on.
Note: this is a contrived example, it doesn't actually test anything useful. Realistically
you'll use this for debugging to see what Pest is doing, but remove it once you get
to a passing test.
```php
$values = $response->fill('name', 'Foo Bar')->getFields();
expect($values)->name->toBe('Foo Bar');
```

## dd($var = NULL)
Does a dump on the class