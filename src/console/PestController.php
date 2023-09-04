<?php

namespace markhuot\craftpest\console;

use craft\console\Controller;
use craft\helpers\FileHelper;
use markhuot\craftpest\actions\RenderCompiledClasses;
use markhuot\craftpest\Pest;
use Symfony\Component\Process\Process;
use yii\console\ExitCode;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

class PestController extends Controller
{

    public bool $force = false;

    function options($actionID): array
    {
        if (in_array($actionID, ['init', 'generate-mixins'], true)) {
            return [
                'force',
            ];
        }

        return [];
    }

    /**
     * Run the Pest tests
     */
    function actionIndex() {
        $this->runInit();
        $this->runTests();
        return ExitCode::OK;
    }

    /**
     * Install Pest
     */
    function actionInit() {
        $this->runInit();
        return ExitCode::OK;
    }

    /**
     * Do the install
     */
    protected function runInit() {
        if (file_exists(CRAFT_BASE_PATH . '/phpunit.xml') && !$this->force) {
            echo 'A phpunit.xml file already exists in this project. If you want to replace the existing testing infrastructure re-run this command with the `--force` flag.'."\n";
        }

        if (!is_dir(CRAFT_BASE_PATH . '/tests')) {
            mkdir(CRAFT_BASE_PATH . '/tests');
        }
        if (!file_exists(CRAFT_BASE_PATH . '/tests/Pest.php')) {
            copy(__DIR__ . '/../../stubs/init/ExampleTest.php', CRAFT_BASE_PATH . '/tests/ExampleTest.php');
            copy(__DIR__ . '/../../stubs/init/Pest.php', CRAFT_BASE_PATH . '/tests/Pest.php');
        }
        if (!file_exists(CRAFT_BASE_PATH . '/phpunit.xml')) {
            copy(__DIR__ . '/../../stubs/init/phpunit.xml', CRAFT_BASE_PATH . '/phpunit.xml');
        }
    }

    /**
     * Run the tests
     */
    protected function runTests() {
        $params = $this->request->getParams();
        $pestOptions = [];
        $stdOutIndex = array_search('--', $params, true);

        if ($stdOutIndex !== false) {
            $pestOptions = array_slice($params, ++$stdOutIndex);
        }

        $process = new Process(['./vendor/bin/pest', ...$pestOptions]);
        $process->setTty(true);
        $process->setTimeout(null);
        $process->start();

        foreach ($process as $type => $data) {
            if ($type === $process::OUT) {
                echo $data;
            } else {
                echo $data;
            }
        }
    }

    function actionCompileTemplates()
    {
        $compiledTemplatesDir = \Craft::$app->path->getCompiledTemplatesPath();
        FileHelper::removeDirectory($compiledTemplatesDir);

        $compileTemplates = function ($path, $base='')
        {
            if (!is_string($path)) {
                return;
            }

            $directory = new \RecursiveDirectoryIterator($path);
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($iterator, '/^.+\.(html|twig)$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach ($regex as $match) {
                $logicalName = ltrim(substr($match[0], strlen($path)), '/');
                if ($logicalName === 'index.twig' || $logicalName === 'index.html') {
                    $logicalName = '';
                }
                $oldTemplateMode = \Craft::$app->view->getTemplateMode();
                \Craft::$app->view->setTemplateMode('site');
                $twig = \Craft::$app->view->twig;
                if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
                    // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
                    $twig->loadTemplate($twig->getTemplateClass($logicalName), $logicalName);
                }
                else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
                    // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
                    $twig->loadTemplate($logicalName);
                }
                \Craft::$app->view->setTemplateMode($oldTemplateMode);
            }
        };

        // // Site specific templates
        // foreach (\Craft::$app->sites->getAllSites() as $site) {
        //     $sitePath = implode(DIRECTORY_SEPARATOR, [CRAFT_BASE_PATH, 'templates', $site->handle]);
        //     if (is_dir($sitePath)) {
        //         $compileTemplates($sitePath);
        //     }
        // }
        //
        // // Template Alias
        // $aliasPath = \Craft::getAlias('@templates');
        // if (is_dir($aliasPath)) {
        //     $compileTemplates($aliasPath);
        // }
        //
        // // Template roots
        // foreach (array_filter(array_merge([
        //     \Craft::$app->view->getSiteTemplateRoots(),
        //     \Craft::$app->view->getCpTemplateRoots(),
        // ])) as $templateRoot => $basePath) {
        //     $compileTemplates($basePath, $templateRoot);
        // }

        // hack
        $compileTemplates(\Craft::getAlias('@templates'));

        return 0;
    }

    function actionGenerateMixins()
    {
        $result = (new RenderCompiledClasses)->handle($this->force);

        if ($result) {
            echo "Mixins successfully generated!\n";
        }
        else {
            echo "Mixins already exist, skipping.\n";
        }

        return ExitCode::OK;
    }
}
