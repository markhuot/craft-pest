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

class Coverage implements AddsOutput, HandlesArguments
{
    /**
     * @var string
     */
    private const COVERAGE_OPTION = 'coverages';

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

    function handleArguments(array $originals): array
    {
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
            $originals[]         = __DIR__ . '/../../.temp/coverage.php';
        }

        if ($input->getOption(self::MIN_OPTION) !== null) {
            $this->coverageMin = (float) $input->getOption(self::MIN_OPTION);
        }

        \yii\base\Event::on(\craft\web\View::class, \craft\web\View::EVENT_AFTER_RENDER_TEMPLATE, function ($event) {
            $compiledTemplate = \Craft::$app->view->twig->loadTemplate($event->template);
    
            // make no-op calls to ensure they don't show up in coverage reports as uncovered
            $compiledTemplate->getTemplateName();
            $compiledTemplate->isTraitable();
            $compiledTemplate->getDebugInfo();
        });

        // Lifted the logic to find all templates out of services/View::_resolveTemplateInternal
        \yii\base\Event::on(\craft\web\Application::class, \craft\web\Application::EVENT_INIT, function ($event) {
            $compileTemplates = function ($path, $base='') {
                $directory = new \RecursiveDirectoryIterator($path);
                $iterator = new \RecursiveIteratorIterator($directory);
                $regex = new \RegexIterator($iterator, '/^.+\.(html|twig)$/i', \RecursiveRegexIterator::GET_MATCH);
                foreach ($regex as $match) {
                    $logicalName = substr($match[0], strlen($path));
                    $oldTemplateMode = \Craft::$app->view->getTemplateMode();
                    \Craft::$app->view->setTemplateMode('site');
                    \Craft::$app->view->twig->loadTemplate($logicalName);
                    \Craft::$app->view->setTemplateMode($oldTemplateMode);
                }
            };

            // Site specific templates
            foreach (\Craft::$app->sites->getAllSites() as $site) {
                $sitePath = implode(DIRECTORY_SEPARATOR, [CRAFT_BASE_PATH, 'templates', $site->handle]);
                if (is_dir($sitePath)) {
                    $compileTemplates($sitePath);
                }
            }

            // Native templates
            $sitePath = CRAFT_BASE_PATH . '/templates/';
            if (is_dir($sitePath)) {
                $compileTemplates($sitePath);
            }

            // Template roots
            foreach (array_filter(array_merge([
                \Craft::$app->view->getSiteTemplateRoots(),
                \Craft::$app->view->getCpTemplateRoots(),
            ])) as $templateRoot => $basePath) {
                $compileTemplates($basePath, $templateRoot);
            }
        });

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

        $reportPath = __DIR__ . '/../../.temp/coverage.php';

        /** @var CodeCoverage $codeCoverage */
        $codeCoverage = require $reportPath;

        $totalWidth = (new Terminal())->getWidth();

        $dottedLineLength = $totalWidth <= 70 ? $totalWidth : 70;

        $totalCoverage = $codeCoverage->getReport()->percentageOfExecutedLines();

        $this->output->writeln(
            sprintf(
                '  <fg=white;options=bold>Cov:    </><fg=default>%s</>',
                $totalCoverage->asString()
            )
        );

        $this->output->writeln('');

        /** @var Directory<File|Directory> $report */
        $report = $codeCoverage->getReport();


        foreach ($report->getIterator() as $file) {
            if (!$file instanceof File) {
                continue;
            }
            $fileId = static::remapName($file);

            $dirname  = dirname($fileId);
            $basename = basename($fileId, '.php');

            $name = $dirname === '.' ? $basename : implode(DIRECTORY_SEPARATOR, [
                $dirname,
                $basename,
            ]);
            $rawName = $dirname === '.' ? $basename : implode(DIRECTORY_SEPARATOR, [
                $dirname,
                $basename,
            ]);

            $linesExecutedTakenSize = 0;

            if ($file->percentageOfExecutedLines()->asString() != '0.00%') {
                $linesExecutedTakenSize = strlen($uncoveredLines = trim(implode(', ', self::getMissingCoverage($file)))) + 1;
                $name .= sprintf(' <fg=red>%s</>', $uncoveredLines);
            }

            $percentage = $file->numberOfExecutableLines() === 0
                ? '100.0'
                : number_format($file->percentageOfExecutedLines()->asFloat(), 1, '.', '');

            $takenSize = strlen($rawName . $percentage) + 4 + $linesExecutedTakenSize; // adding 3 space and percent sign

            $percentage = sprintf(
                '<fg=%s>%s</>',
                $percentage === '100.0' ? 'green' : ($percentage === '0.0' ? 'red' : 'yellow'),
                $percentage
            );

            $this->output->writeln(sprintf(
                '  %s %s %s %%',
                $name,
                str_repeat('.', max($dottedLineLength - $takenSize, 1)),
                $percentage
            ));
        }

        if ($totalCoverage->asFloat() < $this->coverageMin) {
            $this->output->writeln(sprintf(
                "\n  <fg=white;bg=red;options=bold> FAIL </> Code coverage below expected:<fg=red;options=bold> %s %%</>. Minimum:<fg=white;options=bold> %s %%</>.",
                number_format($totalCoverage->asFloat(), 1),
                number_format($this->coverageMin, 1)
            ));
        }

        return $totalCoverage->asFloat();
    }

    /**
     * Generates an array of missing coverage on the following format:.
     *
     * ```
     * ['11', '20..25', '50', '60..80'];
     * ```
     *
     * @param File $file
     *
     * @return array<int, string>
     */
    public static function getMissingCoverage($file): array
    {
        $shouldBeNewLine = true;

        $eachLine = function (array $array, array $tests, int $line) use (&$shouldBeNewLine, $file): array {
            if (count($tests) > 0) {
                $shouldBeNewLine = true;

                return $array;
            }

            $line = static::remapLine($file, $line);

            if ($shouldBeNewLine) {
                $array[]         = (string) $line;
                $shouldBeNewLine = false;

                return $array;
            }

            $lastKey = count($array) - 1;

            if (array_key_exists($lastKey, $array) && strpos($array[$lastKey], '..') !== false) {
                [$from]          = explode('..', $array[$lastKey]);
                $array[$lastKey] = $line > $from ? sprintf('%s..%s', $from, $line) : sprintf('%s..%s', $line, $from);

                return $array;
            }

            $array[$lastKey] = sprintf('%s..%s', $array[$lastKey], $line);

            return $array;
        };

        $array = [];
        foreach (array_filter($file->lineCoverageData(), 'is_array') as $line => $tests) {
            $array = $eachLine($array, $tests, $line);
        }

        return $array;
    }

    public static function remapName($file)
    {
        if ($template = static::loadTemplate($file)) {
            $source = $template->getSourceContext();
            $logicalName = $source->getName();
            if (empty($logicalName)) {
                $logicalName = basename($source->getPath());
            }
            $ext = pathinfo($source->getPath(), PATHINFO_EXTENSION);
            return rtrim($logicalName, '.' . $ext) . '.' . $ext;
        }

        return $file->id();
    }

    public static function remapLine($file, $line)
    {
        if ($template = static::loadTemplate($file)) {
            $actualLine = $template->getDebugInfo()[$line] ?? null;
            if ($actualLine) {
                return $actualLine;
            }
        }

        return $line;
    }

    protected static function loadTemplate($file)
    {
        if (strpos($file->pathAsString(), 'storage/runtime/compiled_templates') !== false) {
            $contents = file_get_contents($file->pathAsString());
            preg_match('/(__TwigTemplate_[a-z0-9]+)/', $contents, $matches);
            if ($matches[1]) {
                $compiledClass = $matches[1];
                return new $compiledClass(\Craft::$app->view->twig);
            }
        }

        return false;
    }
}