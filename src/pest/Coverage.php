<?php

namespace markhuot\craftpest\pest;

use Pest\Contracts\Plugins\AddsOutput;
use Pest\Contracts\Plugins\HandlesArguments;
use Symfony\Component\Console\Terminal;
use SebastianBergmann\CodeCoverage\Node\File;
use Pest\Support\Str;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use craft\helpers\FileHelper;
use Symfony\Component\Process\Process;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class Coverage implements AddsOutput, HandlesArguments
{
    /**
     * @var string
     */
    private const COVERAGE_OPTION = 'twig-coverage';

    /**
     * @var string
     */
    private const MIN_OPTION = 'min';

    /**
     * Whether should show the coverage or not.
     *
     * @var bool
     */
    public $coverage = false;

    /**
     * The minimum coverage.
     *
     * @var float
     */
    public $coverageMin = 80.0;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * This method is called every time pest is executed
     * but we are only interested in the --twig-coverage option
     */
    function handleArguments(array $originals): array
    {
        if (!in_array('--' . self::COVERAGE_OPTION, $originals)) {
            return $originals;
        }

        $arguments = array_merge([''], array_values(array_filter($originals, function ($original): bool {
            foreach ([self::COVERAGE_OPTION, self::MIN_OPTION] as $option) {
                if ($original === sprintf('--%s', $option) || Str::startsWith($original, sprintf('--%s=', $option))) {
                    return true;
                }
            }

            return false;
        })));

        $originals = array_flip($originals);
        foreach ($arguments as $argument) {
            unset($originals[$argument]);
        }
        $originals = array_flip($originals);

        $inputs   = [];
        $inputs[] = new InputOption(self::COVERAGE_OPTION, null, InputOption::VALUE_NONE);
        $inputs[] = new InputOption(self::MIN_OPTION, null, InputOption::VALUE_REQUIRED);

        $input = new ArgvInput($arguments, new InputDefinition($inputs));
        if ((bool) $input->getOption(self::COVERAGE_OPTION)) {
            $this->coverage      = true;
            $originals[]         = '--coverage-php';
            $originals[]         = getcwd() . '/storage/coverage.php';
        }

        if ($input->getOption(self::MIN_OPTION) !== null) {
            $this->coverageMin = (float) $input->getOption(self::MIN_OPTION);
        }

        $this->output->writeln('  i Pre-compiling templates so they may be analysed by PHPUnit\'s code coverage.');

        $process = new Process(['./src/bin/craft', 'pest/test/compile-templates']);
        $process->setTty(true);
        $process->setTimeout(null);
        $process->start();
        foreach ($process as $data) {
            $this->output->writeln($data);
        }

        return $originals;
    }

    public function addOutput(int $result): int
    {
        if (!$this->coverage) {
            return $result;
        }

        if ($result === 0) {
            if (!\Pest\Support\Coverage::isAvailable()) {
                $this->output->writeln(
                    "\n  <fg=white;bg=red;options=bold> ERROR </> No code coverage driver is available.</>",
                );
                exit(1);
            }
        }

        $reportPath = getcwd() . '/storage/coverage.php';

        /** @var \SebastianBergmann\CodeCoverage\CodeCoverage $codeCoverage */
        $codeCoverage = require $reportPath;

        $report = $codeCoverage->getReport();

        $totalPart = 0;
        $totalWhole = 0;

        foreach ($report->getIterator() as $file) {
            if (!$file instanceof File) {
                continue;
            }

            $reporters = [
                new TwigReporter($file),
                new PhpReporter($file),
            ];
            foreach ($reporters as $reporter) {
                $result = $reporter->canReportOn();

                if ($result === Reporter::IGNORE) {
                    break;
                }

                if ($result === true) {
                    $name = $reporter->getName();
                    $lines = $reporter->getUncoveredLineRanges();
                    // if ($name === 'tests/templates/loop-test.twig') {
                    //     var_dump($file->lineCoverageData());
                    //     die;
                    // }
                    $part = $reporter->getNumberOfExecutedLines();
                    $whole = $reporter->getNumberOfExecutableLines();
                    $percent = $whole <= 0 ? 0 : $part / $whole * 100;

                    if ($percent === 100) {
                        $percent = '<fg=green>✓</>';
                        $lines = '';
                    }
                    else if ($percent === 0) {
                        $percent = '';
                        $lines = '';
                    }
                    else {
                        $percent = '<fg='.($percent>90?'green':($percent>75?'yellow':'red')).'>'.number_format($percent, 2) . ' %</>';
                        $lines = implode(', ', $lines->toArray());
                    }

                    $left = wordwrap(implode(' ', array_filter([
                        $name,
                        $lines ? '<fg=yellow>'.$lines.'</>' : null,
                    ])), 60).' ';
                    $leftLines = preg_split('/[\r\n]+/', $left);
                    $lastLineOfLeft = $leftLines[count($leftLines)-1];

                    $right = ($percent ? ' ' : '').$percent;
                    $hang = 0;
                    if (preg_match('/%$/', strip_tags($right))) {
                        $hang = -2;
                    }

                    $dots = str_repeat('.', 70 - $hang - mb_strlen(strip_tags($lastLineOfLeft)) - mb_strlen(strip_tags($right)));

                    $this->output->writeln($left . $dots . $right);

                    $totalPart += $part;
                    $totalWhole += $whole;
                    break;
                }
            }
        }

        return 0;
    }
}
