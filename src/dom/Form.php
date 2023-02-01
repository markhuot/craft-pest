<?php

namespace markhuot\craftpest\dom;

use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\test\Dd;
use markhuot\craftpest\web\TestableResponse;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\InputFormField;

/**
 * # Form
 *
 * You can interact with HTML forms globally on a response or by targeting the specific form
 * on the page. When interacting with a global form on the response the _first_ form on the
 * page will be used. If you have multiple forms on a page and need to access a form other than
 * the first you will need to target it.
 *
 * ```php
 * $response->fill('name', 'Foo Bar')->submit();
 * $response->form('#some-form-selector')->fill('name', 'Foo Bar')->submit();
 * ```
 */
class Form
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
     *
     * ```php
     * $response->fill('name', 'Foo Bar');
     * ```
     */
    public function fill(string $fieldNameOrSelector, mixed $value): self
    {
        $this->form[$fieldNameOrSelector]->setValue((string) $value);

        return $this;
    }

    /**
     * Creates and fills a virtual field
     *
     * This is useful to emulate DOM manipulation that actually happens via javascript such as
     * an Alpine or Vue component that dynamically adds a form field to the DOM.
     *
     * ```php
     * $response->addField('wysiwyg_content', '<p>foo</p>');
     * ```
     */
    public function addField(string $fieldNameOrSelector, mixed $value): self
    {
        $node = $this->form->getNode()->ownerDocument->createElement('input');
        $node->setAttribute('name', $fieldNameOrSelector);

        $field = new InputFormField($node);
        $field->setValue((string) $value);

        $this->form->set($field);

        return $this;
    }



    /**
     * Set the checked state of a checkbox.
     *
     * ```php
     * $response->tick('#checkbox');
     * ```
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
     * Unset the checked state of a checkbox
     *
     *
     * ```php
     * $response->untick('#checkbox');
     * ```
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
     *
     * ```php
     * $response->select('#states', ['NY', 'NJ']);
     * ```
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


    /**
     * Clicks a button on the form and submits the form
     *
     * ```php
     * $response->fill('q', 'foo')->click('Feeling lucky');
     * ```
     */
    public function click(string $buttonSelectorOrLabel): TestableResponse
    {
        $button = $this->crawler->selectButton($buttonSelectorOrLabel);

        if ($button->count() !== 1) {
            throw new \InvalidArgumentException("Unable to find exact button to click on.");
        }

        $this->fill($button->attr('name'), $button->attr('value') ?: $button->attr('formaction'));

        return $this->submit();
    }

    /**
     * Submits the form. When used directly on a response will find and submit the
     * first form on the page. Otherwise will use the selected form.
     *
     * ```php
     * $response->submit();
     * $response->form('#second-form')->submit();
     * ```
     */
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
     * Pulls the values of the form fields out of the DOM and returns them as a PHP array.
     * This array can then be `expect()`-ed and asserted on.
     *
     * Note: this is a contrived example, it doesn't actually test anything useful. Realistically
     * you'll use this for debugging to see what Pest is doing, but remove it once you get
     * to a passing test.
     *
     * ```php
     * $values = $response->fill('name', 'Foo Bar')->getFields();
     * expect($values)->name->toBe('Foo Bar');
     * ```
     */
    public function getFields(): array
    {
        return $this->form->getPhpValues();
    }
}
