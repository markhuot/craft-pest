<?php

namespace markhuot\craftpest\pest;

use Illuminate\Support\Collection;
use SebastianBergmann\CodeCoverage\Node\File;

abstract class Reporter
{
    const IGNORE = 'ignore';

    protected File $file;

    function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return bool|string
     */
    function canReportOn()
    {
        return true;
    }

    function getName(): string
    {
        return $this->file->id();
    }

    function getSourceLineFor(int $line)
    {
        return $line;
    }

    function getLineCoverageData()
    {
        return collect($this->file->lineCoverageData())
            ->mapWithKeys(fn ($value, $key) => [$this->getSourceLineFor($key) => $value])
            ->filter(fn ($value, $key) => !empty($key));
    }

    function getUncoveredLines(): Collection
    {
        return $this->getLineCoverageData()
            ->filter(fn ($line) => empty($line))
            ->keys();
    }

    function getUncoveredLineRanges(): Collection
    {
        $ranges = [];
        $lastLineMerged = false;

        // [1 => true, 2 => true, 3 => true, 4 => false, 5 => true , 6 => true]
        // 1..3, 5, 6
        foreach ($this->getLineCoverageData() as $line => $lineCoverageData) {
            $missingCoverage = empty($lineCoverageData);

            if ($missingCoverage && $lastLineMerged) {
                $ranges[count($ranges) - 1][1] = $line;
                $lastLineMerged = true;
            }
            else if ($missingCoverage) {
                $ranges[] = [$line];
                $lastLineMerged = true;
            }
            else {
                $lastLineMerged = false;
            }
        }

        return collect($ranges)->map(fn ($r) => implode('..', $r))->flatten();
    }

    function getNumberOfExecutableLines(): int
    {
        return $this->file->numberOfExecutableLines();
    }

    function getNumberOfExecutedLines(): int
    {
        return $this->file->numberOfExecutedLines();
    }
}
