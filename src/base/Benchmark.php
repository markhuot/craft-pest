<?php

namespace markhuot\craftpest\base;

use Illuminate\Support\Collection;
use yii\db\Command;

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
        return $this->getQueryTiming()->duplicates('info');
    }

    function assertNoDuplicateQueries()
    {
        $duplicates = $this->getDuplicateQueries();

        test()->assertSame(
            0,
            $duplicates->count(),
            'Duplicate queries were found during the test. ' . "\n" . $duplicates->first()
        );
    }
}