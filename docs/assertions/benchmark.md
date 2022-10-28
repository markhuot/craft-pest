# Benchmarks

Benchmarks can be taken on Craft actions which you can then assert against. For example you
may want to load the homepage and ensure there are no duplicate queries that could have been
lazy loaded. You would do this with,

```php
it('checks for duplicate queries')
  ->beginBenchmark()
  ->get('/')
  ->endBenchmark()
  ->assertNoDuplicateQueries();
```

## assertNoDuplicateQueries()
Ensures there are no duplicate queries since the benchmark began.

```php
$benchmark->assertNoDuplicateQueries();
```

## assertLoadTimeLessThan(float $expectedLoadTime)
Assert that the execution timing of the benchmark is less than the given timing
in seconds.

Note: Benchmarks must begin and end in your test. That allows you to do any necessary
setup before the benchmark begins so your test preamble doesn't affect your assertion.

```php
it('loads an article', function () {
  $entry = Entry::factory()->section('articles')->create();

  $this->beginBenchmark()
    ->get($entry->uri);
    ->endBenchmark()
    ->assertLoadTimeLessThan(2);
});
```

## assertMemoryLoadLessThan(float $expectedMemoryLoad)
Assert that the peak memory load of the benchmark is less than the given memory limit
in megabytes.

```php
it('loads the homepage')
  ->beginBenchmark()
  ->get('/');
  ->endBenchmark()
  ->assertMemoryLoadLessThan(128);
});
```

## assertAllQueriesFasterThan(float $expectedQueryTime)
Assert that every query is faster than the given threshold in seconds.

```php
it('loads the homepage')
  ->beginBenchmark()
  ->get('/');
  ->endBenchmark()
  ->assertAllQueriesFasterThan(0.05);
});
```