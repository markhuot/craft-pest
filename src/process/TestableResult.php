<?php

namespace markhuot\craftpest\process;

class TestableResult
{
    protected ?int $exitCode;
    protected ?string $stdout;
    protected ?string $stderr;
    protected ?string $output;

    function setExitCode(int $exitCode)
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    function getExitCode()
    {
        return $this->exitCode;
    }

    function setStdout(string $stdout)
    {
        $this->stdout = $stdout;

        return $this;
    }

    function getStdout()
    {
        return $this->stdout;
    }

    function setStderr(string $stderr)
    {
        $this->stderr = $stderr;

        return $this;
    }

    function getStderr()
    {
        return $this->stderr;
    }

    function setOutput(string $output)
    {
        $this->output = $output;

        return $this;
    }

    function getOutput()
    {
        return $this->output;
    }

    function assertOk()
    {
        return $this->assertExitCode(0);
    }

    function assertExitCode(int $exitCode)
    {
        expect($this->exitCode)->toBe($exitCode);

        return $this;
    }

    function assertStdout($stdout, $trimmed=true)
    {
        expect($trimmed ? trim($this->stdout) : $this->stdout)->toBe($stdout);

        return $this;
    }

    function assertSee(string $text)
    {
        expect($this->output)->toContain($text);

        return $this;
    }

    function assertDontSee(string $text)
    {
        expect($this->stdout)->not->toContain($text);

        return $this;
    }
}
