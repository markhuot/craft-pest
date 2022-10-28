<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Section;

it('benchmarks queries', function () {
    $this->beginBenchmark();
    $this->get('/')->assertOk();
    $this->get('/')->assertOk();
    $this->endBenchmark()
        ->assertNoDuplicateQueries();
});

it('benchmarks', function () {
    $section = Section::factory()->template('entry')->create();
    $entry = Entry::factory()->section($section)->create();

    $this->beginBenchmark()
        ->get($entry->uri)
        ->assertOk()
        ->endBenchmark()
        ->assertNoDuplicateQueries()
        ->assertLoadTimeLessThan(1)
        ->assertMemoryLoadLessThan(2048)
        ->assertAllQueriesFasterThan(0.5);
});