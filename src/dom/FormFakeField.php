<?php

namespace markhuot\craftpest\dom;

use Symfony\Component\DomCrawler\Field\FormField;

class FormFakeField extends FormField
{

    public function __construct(\DOMElement $node)
    {

    }

    protected function initialize()
    {
        //
    }

    /**
     * Check if the current field is disabled.
     */
    public function isDisabled(): bool
    {
        return false;
    }

    public function getLabel(): ?\DOMElement
    {
        return null;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

}
