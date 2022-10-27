<?php

it('benchmarks queries', function () {
    $this->beginBenchmark();
    $this->get('/')->assertOk();
    $this->get('/')->assertOk();
    $this->endBenchmark()->summary()->assertNoDuplicateQueries();
})->skip();

it('benchmarks')
    ->beginBenchmark()
    ->get('/')
    ->assertOk()
    ->endBenchmark()
    ->assertNoDuplicateQueries();