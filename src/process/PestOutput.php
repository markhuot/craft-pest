<?php

namespace markhuot\craftpest\process;

use Pest\Contracts\Plugins\AddsOutput;
use Pest\Support\Container;
use React\ChildProcess\Process;
use Symfony\Component\Console\Output\OutputInterface;

class PestOutput implements AddsOutput
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function addOutput(int $testReturnCode): int
    {
        $processes = Container::getInstance()->get('processes');
        
        foreach ($processes as $process) {
            $cmd = $process['process']->getCommand();
            if (is_array($cmd)) {
                $cmd = implode(' ', $cmd);
            }
            $exitCode = $process['result']->getExitCode();

            if ($exitCode !== 0) {
                $this->output->writeln(['', '  ---', '']);
                $this->output->writeln('  <fg=red>• ' . $cmd . '</>');
                $this->output->write(preg_replace('/^/m', '  ', $process['result']->getStdout()));
            }
        }

        return $testReturnCode;
    }
}
