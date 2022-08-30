<?php

namespace markhuot\craftpest\pest;

use Illuminate\Support\Collection;
use SebastianBergmann\CodeCoverage\Node\File;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Template;

class TwigReporter extends Reporter
{
    protected Template $template;

    function __construct(File $file)
    {
        parent::__construct($file);

        $firstClass = array_keys($this->file->classes())[0] ?? '';
        if (!(strpos($firstClass, '__TwigTemplate_') === 0)) {
            return;
        }

        if (!class_exists($firstClass)) {
            require $this->file->pathAsString();
        }

        $this->template = new $firstClass(new Environment(new ArrayLoader([])));
    }

    function canReportOn()
    {
        if (empty($this->template)) {
            return false;
        }

        $templateName = $this->template->getTemplateName();
        if (strpos($templateName, '__string_template__') === 0) {
            return self::IGNORE;
        }

        return true;
    }

    function getName(): string
    {
        return str_replace(getcwd() . '/', '', $this->template->getSourceContext()->getPath());
    }

    function getSourceLineFor(int $line)
    {
        $debugInfo = $this->template->getDebugInfo();

        if (!empty($debugInfo[$line])) {
            return $debugInfo[$line];
        }

        while ($line > 0 && empty($debugInfo[$line])) {
            $line--;
        }

        if ($line > 0) {
            return $debugInfo[$line];
        }

        return null;
    }

    function getNumberOfExecutableLines(): int
    {
        return count($this->template->getDebugInfo());
    }

    function getNumberOfExecutedLines(): int
    {
        return $this->getNumberOfExecutableLines() - $this->getUncoveredLines()->count();
    }
}
