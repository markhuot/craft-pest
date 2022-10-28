<?php

namespace markhuot\craftpest\base;

use Illuminate\Support\Collection;
use yii\db\Command;
use yii\debug\Module;

class Benchmark
{
    /** @var Collection<int, array{
     *   0: string // message
     *   1: int // level
     *   2: string // category
     *   3: float // time
     *   4: array
     *   5: int
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

    function assertLoadTimeLessThan(float $expectedLoadTime)
    {
        $actualLoadTime = $this->getPanels()['profiling']->data['time'];

        test()->assertLessThan($expectedLoadTime, $actualLoadTime);

        return $this;
    }

    function assertMemoryLoadLessThan(float $expectedMemoryLoad)
    {
        $actualMemoryLoadBytes = $this->getPanels()['profiling']->data['memory'];
        $actualMemoryLoadMb = $actualMemoryLoadBytes/1024/1024;

        test()->assertLessThan($expectedMemoryLoad, $actualMemoryLoadMb);

        return $this;
    }

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