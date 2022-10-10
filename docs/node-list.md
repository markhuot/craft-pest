A `NodeList` represents a fragment of HTML. It can contain one or more nodes and
the return values of its methods vary based on the count. For example getting the text
of a single h1 element via `$response->querySelector('h1')->text === "string"` will return the string
contents of that node. However, if the `NodeList` contains multiple nodes the return
will be an array such as when you get back multiple list items, `$response->querySelector('li')->text === ["list", "text", "items"]`

## expect()
You can turn any `NodeList` in to an expectation API by calling `->expect()` on it. From there
you are free to use the expectation API to assert the DOM matches your expectations.

```php
$response->querySelector('li')->expect()->count->toBe(10);
```

## querySelector()
Filter the node list down further. For example, get a specific unordered list
and then get the list items within,

```php
$response->querySelector('ul')->querySelector('li');
```

Note, many times this could be better written in a single selector such as,

```php
$response->querySelector('ul li');
```

Sometimes, this is necessary, though, when you have a form and you want get a
specific element within the form, for example,

```php
$response->querySelector('form')
    ->assertAttribute('method', 'post')
    ->querySelector('input')
    ->assertCount(1);
```

## form()
Get a form for the current crawler instance

## getText()
Available as a method or a magic property of `->text`. Gets the text content of the node or nodes. This
will only return the text content of the node as well as any child nodes. Any non-text content such as
HTML tags will be removed.

## getInnerHTML()
Available as a method or a magic property of `->innerHTML`. Gets the inner HTML of the node or nodes.

## fill()
Fills any form data with a matching key with the given value.

## submit()
Submits a form matching the given selector