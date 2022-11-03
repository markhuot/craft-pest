<?php

namespace markhuot\craftpest\dom;

use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\traits\Dd;
use markhuot\craftpest\web\TestableResponse;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

final class Form
{
    use Dd;

    private \Symfony\Component\DomCrawler\Form $form;
    private Crawler $crawler;

    public function __construct(?NodeList $nodeList)
    {
        if ($nodeList->count === 0) {
            throw new \InvalidArgumentException("Unable to select form.");
        }

        if ($nodeList->count > 1) {
            $ids = implode(', ', $nodeList->getNodeOrNodes(fn (Crawler $node) => $node->attr('id')));
            throw new \InvalidArgumentException("From selector is ambiguous. Found {$nodeList->count} forms: {$ids}.");
        }

        $this->crawler = $nodeList->crawler->eq(0);
        $node = $this->crawler->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(sprintf('The selected node should be instance of DOMElement, got "%s".', get_debug_type($node)));
        }

        $this->form = new \Symfony\Component\DomCrawler\Form($node, 'http://localhost:8080/');

    }

    /**
     * Fills input or textarea
     */
    public function fill(string $fieldNameOrSelector, mixed $value): self
    {
        $this->form[$fieldNameOrSelector]->setValue((string) $value);

        return $this;
    }

    /**
     * Creates and fills a virtual field
     * This is useful to emulate DOM manipulation that actually happens via javascript
     */
    public function fake(string $fieldNameOrSelector, mixed $value): self
    {
        $virtual = new FormFakeField(new \DOMElement('input'));
        $virtual->setName($fieldNameOrSelector);
        $virtual->setValue((string) $value);

        $this->form->set($virtual);

        return $this;
    }



    /**
     * Checks checkbox
     */
    public function tick(string $fieldNameOrSelector): self
    {
       if (!($this->form[$fieldNameOrSelector] instanceof ChoiceFormField)) {
           throw new \InvalidArgumentException("Field '$fieldNameOrSelector' is not a checkbox, unable to tick()");
       }

        $this->form[$fieldNameOrSelector]->tick();

        return $this;
    }

    /**
     * Unchecks checkbox
     */
    public function untick(string $fieldNameOrSelector): self
    {
        if (!($this->form[$fieldNameOrSelector] instanceof ChoiceFormField)) {
            throw new \InvalidArgumentException("Field '$fieldNameOrSelector' is not a checkbox, unable to untick()");
        }

        $this->form[$fieldNameOrSelector]->untick();

        return $this;
    }

    /**
     * Selects one or many options from select
     */
    public function select(string $fieldNameOrSelector, string|array|bool $value): self
    {
        if (!($this->form[$fieldNameOrSelector] instanceof ChoiceFormField)) {
            throw new \InvalidArgumentException("Field '$fieldNameOrSelector' is not a select, unable to select()");
        }

        try {
            $this->form[$fieldNameOrSelector]->select($value);
        } catch (\InvalidArgumentException) {
            $optionByName = $this->crawler
                ->filterXPath(sprintf("//select[@name='%s']", $fieldNameOrSelector))
                ->filterXPath(sprintf("//option[contains(.,'%s')]", $value))->attr('value');
            $this->form[$fieldNameOrSelector]->select($optionByName);
        }

        return $this;
    }


    public function click(string $buttonSelectorOrLabel): TestableResponse
    {
        $button = $this->crawler->selectButton($buttonSelectorOrLabel);

        if ($button->count() !== 1) {
            throw new \InvalidArgumentException("Unable to find exact button to click on.");
        }

        $this->fill($button->attr('name'), $button->attr('value') ?: $button->attr('formaction'));

        return $this->submit();
    }

    public function submit(): TestableResponse
    {
        $request = new RequestBuilder(
            $this->form->getMethod(),
            $this->form->getUri()
        );

        $request->setBody($this->form->getPhpValues());

        return $request->send();
    }

    /**
     * Useful to verify the fields before submitting the form
     */
    public function getFields(): array
    {
        return $this->form->getPhpValues();
    }
}
