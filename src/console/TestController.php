<?php

namespace markhuot\craftpest\console;

use craft\console\Controller;
use Symfony\Component\Process\Process;

class TestController extends Controller {

    /**
     * Run the Pest tests
     */
    function actionIndex() {
        $this->runInit();
        $this->runTests();
    }

    /**
     * Install Pest
     */
    function actionInit() {
        $this->runInit();
    }

    /**
     * Do the install
     */
    protected function runInit() {
        if (!file_exists(CRAFT_BASE_PATH . '/phpunit.xml')) {
            $process = new Process(['./vendor/bin/pest', '--init']);
            $process->setTty(true);
            $process->start();

            foreach ($process as $type => $data) {
                if ($type === $process::OUT) {
                    echo $data;
                } else {
                    echo $data;
                }
            }

            copy(__DIR__ . DIRECTORY_SEPARATOR . '../../stubs/init/ExampleTest.php', './tests/ExampleTest.php');
            copy(__DIR__ . DIRECTORY_SEPARATOR . '../../stubs/init/Pest.php', './tests/Pest.php');
        }
    }

    /**
     * Run the tests
     */
    protected function runTests() {
        $process = new Process(['./vendor/bin/pest']);
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

}
