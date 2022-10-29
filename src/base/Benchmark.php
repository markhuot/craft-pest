<?php

namespace markhuot\craftpest\base;

use Illuminate\Support\Collection;
use yii\db\Command;
use yii\debug\Module;

/**
 * # Benchmarks
 * 
 * Benchmarks can be taken on Craft actions which you can then assert against. For example you
 * may want to load the homepage and ensure there are no duplicate queries that could have been
 * lazy loaded. You would do this with,
 * 
 * ```php
 * it('checks for duplicate queries')
 *   ->beginBenchmark()
 *   ->get('/')
 *   ->endBenchmark()
 *   ->assertNoDuplicateQueries();
 * ```
 * 
 * @see \markhuot\craftpest\traits\Benchmark
 */
class Benchmark
{
    /**
     * The messages. Have the following keys,
     *   0: string // message
     *   1: int // level
     *   2: string // category
     *   3: float // time
     *   4: array
     *   5: int
     * 
     * 
     * @internal
     * @var Collection<int, array{
     *   0: string,
     *   1: int,
     *   2: string,
     *   3: float,
     *   4: array,
     *   5: int,
     * }> */
    protected Collection $messages;

    protected $dbQueryCache;
    protected $dbQueryTimingCache;
    protected $manifestCache;

    function __construct(array $messages) {
        $this->messages = collect($messages);
    }

    function summary()
    {
        $dbQueries = $this->messages->filter(function($message) {
            return $message[2] === Command::class . '::query';
        });

        $timings = collect(\Craft::getLogger()->calculateTimings($dbQueries))
            ->sortByDesc('duration');


        echo 'There were ' . $dbQueries->count() . ' queries'."\n";
        echo 'Slowest Query ' . $timings->first()['duration'] . ' seconds: ' . $timings->first()['info']."\n";
        echo 'Duplicate queries ' . $timings->duplicates('info')->count()."\n";

        return $this;
    }

    function __get($key)
    {

    }

    function getQueries()
    {
        if ($this->dbQueryCache !== null) {
            return $this->dbQueryCache;
        }

        // dd($this->getPanels()['profiling']->data);

        return $this->dbQueryCache = $this->messages->filter(function($message) {
            return $message[2] === Command::class . '::query';
        });
    }

    function getQueryTiming()
    {
        if ($this->dbQueryTimingCache !== null) {
            return $this->dbQueryTimingCache;
        }

        return $this->dbQueryTimingCache = collect(\Craft::getLogger()->calculateTimings($this->getQueries()))
            ->sortByDesc('duration');
    }

    function getDuplicateQueries()
    {
        return $this->getQueryTiming()->filter(function ($query) {
            return preg_match('/^SHOW/', $query['info']) === false;
        })->duplicates('info');
    }

    protected function getPanels()
    {
        $logTarget = Module::getInstance()->logTarget;

        if (empty($this->manifestCache)) {
            $this->manifestCache = $logTarget->loadManifest();
        }

        $tags = array_keys($this->manifestCache);

        if (empty($tags)) {
            throw new \Exception("No debug data have been collected yet, try browsing the website first.");
        }

        $tag = reset($tags);

        $logTarget->loadTagToPanels($tag);
        
        return Module::getInstance()->panels;
    }

    /**
     * Ensures there are no duplicate queries since the benchmark began.
     * 
     * ```php
     * $benchmark->assertNoDuplicateQueries();
     * ```
     */
    function assertNoDuplicateQueries()
    {
        $duplicates = $this->getDuplicateQueries();

        test()->assertSame(
            0,
            $duplicates->count(),
            'Duplicate queries were found during the test. ' . "\n" . $duplicates->first()
        );

        return $this;
    }

    /**
     * Assert that the execution timing of the benchmark is less than the given timing
     * in seconds.
     * 
     * Note: Benchmarks must begin and end in your test. That allows you to do any necessary
     * setup before the benchmark begins so your test preamble doesn't affect your assertion.
     * 
     * ```php
     * it('loads an article', function () {
     *   $entry = Entry::factory()->section('articles')->create();
     * 
     *   $this->beginBenchmark()
     *     ->get($entry->uri)
     *     ->endBenchmark()
     *     ->assertLoadTimeLessThan(2);
     * });
     * ```
     */
    function assertLoadTimeLessThan(float $expectedLoadTime)
    {
        $actualLoadTime = $this->getPanels()['profiling']->data['time'];

        test()->assertLessThan($expectedLoadTime, $actualLoadTime);

        return $this;
    }

    /**
     * Assert that the peak memory load of the benchmark is less than the given memory limit
     * in megabytes.
     *
     * 
     * ```php
     * it('loads the homepage')
     *   ->beginBenchmark()
     *   ->get('/');
     *   ->endBenchmark()
     *   ->assertMemoryLoadLessThan(128);
     * });
     * ```
     */
    function assertMemoryLoadLessThan(float $expectedMemoryLoad)
    {
        $actualMemoryLoadBytes = $this->getPanels()['profiling']->data['memory'];
        $actualMemoryLoadMb = $actualMemoryLoadBytes/1024/1024;

        test()->assertLessThan($expectedMemoryLoad, $actualMemoryLoadMb);

        return $this;
    }

    /**
     * Assert that every query is faster than the given threshold in seconds.
     *
     * 
     * ```php
     * it('loads the homepage')
     *   ->beginBenchmark()
     *   ->get('/');
     *   ->endBenchmark()
     *   ->assertAllQueriesFasterThan(0.05);
     * });
     * ```
     */
    function assertAllQueriesFasterThan(float $expectedQueryTime)
    {
        $failing = $this->getQueryTiming()->filter(function ($query) use ($expectedQueryTime) {
            return (float)$query['duration'] > $expectedQueryTime;
        });

        if ($failing->count()) {
            test()->fail($failing->count() . ' queries were slower than ' . $expectedQueryTime);
        }

        expect(true)->toBe(true);
    }

}