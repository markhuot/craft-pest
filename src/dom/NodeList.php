<?php

namespace markhuot\craftpest\dom;

use markhuot\craftpest\traits\SubmitsForm;

/**
 * A `NodeList` represents a fragment of HTML. It can contain one or more nodes and
 * the return values of its methods vary based on the count. For example getting the text
 * of a single h1 element via `$response->querySelector('h1')->text === "string"` will return the string
 * contents of that node. However, if the `NodeList` contains multiple nodes the return
 * will be an array such as when you get back multiple list items, `$response->querySelector('li')->text === ["list", "text", "items"]`
 * 
 * @property string $text
 * @property string $innerHTML
 * @property int $count
 */
class NodeList implements \Countable
{
    use SubmitsForm;

    /** @var \Symfony\Component\DomCrawler\Crawler */
    public $crawler;

    function __construct(\Symfony\Component\DomCrawler\Crawler $crawler) {
        $this->crawler = $crawler;
    }

    /**
     * You can turn any `NodeList` in to an expectation API by calling `->expect()` on it. From there
     * you are free to use the expectation API to assert the DOM matches your expectations.
     * 
     * ```php
     * $response->querySelector('li')->expect()->count->toBe(10);
     * ```
     */
    function expect()
    {
        return test()->expect($this);
    }

    function __get($property) {
        $getter = 'get' . ucfirst($property);

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        throw new \Exception("Property `{$property}` not found on Pest\\CraftCms\\NodeList");
    }

    protected function getNodeOrNodes(callable $callback) {
        if ($this->crawler->count() === 1) {
            return $callback($this->crawler->eq(0));
        }

        $result = [];
        for ($i=0; $i<$this->crawler->count(); $i++) {
            $node = $this->crawler->eq($i);
            $result[] = $callback($node);
        }

        return $result;
    }

    /**
     * Filter the node list down further. For example, get a specific unordered list
     * and then get the list items within,
     * 
     * ```php
     * $response->querySelector('ul')->querySelector('li');
     * ```
     * 
     * Note, many times this could be better written in a single selector such as,
     * 
     * ```php
     * $response->querySelector('ul li');
     * ```
     * 
     * Sometimes, this is necessary, though, when you have a form and you want get a
     * specific element within the form, for example,
     * 
     * ```php
     * $response->querySelector('form')
     *     ->assertAttribute('method', 'post')
     *     ->querySelector('input')
     *     ->assertCount(1);
     * ```
     */
    function querySelector(string $selector)
    {
        return new NodeList($this->crawler->filter($selector));
    }

    /**
     * Get a form for the current crawler instance
     */
    function form()
    {
        return $this->crawler->form();
    }

    /**
     * Available as a method or a magic property of `->text`. Gets the text content of the node or nodes. This
     * will only return the text content of the node as well as any child nodes. Any non-text content such as
     * HTML tags will be removed.
     */
    function getText(): array|string {
        return $this->getNodeOrNodes(fn ($node) => $node->text());
    }

    /**
     * Available as a method or a magic property of `->innerHTML`. Gets the inner HTML of the node or nodes.
     */
    public function getInnerHTML(): array|string  {
        return $this->getNodeOrNodes(fn ($node) => $node->html());
    }

    public function count(): int {
        return $this->crawler->count();
    }

    public function getCount(): int {
        return $this->count();
    }

    public function assertText($expected) {
        test()->assertSame($expected, $this->getText());

        return $this;
    }

    public function assertContainsString($expected) {
        test()->assertStringContainsString($expected, $this->getText());

        return $this;
    }

    public function assertCount($expected) {
        test()->assertCount($expected, $this);

        return $this;
    }
}
