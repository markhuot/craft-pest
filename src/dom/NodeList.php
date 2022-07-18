<?php

namespace markhuot\craftpest\dom;

/**
 * @property string $text
 * @property string $innerHTML
 * @property int $count
 */
class NodeList implements \Countable {
    /** @var \Symfony\Component\DomCrawler\Crawler */
    public $crawler;

    function __construct(\Symfony\Component\DomCrawler\Crawler $crawler) {
        $this->crawler = $crawler;
    }

    function __get($property) {
        $getter = 'get' . ucfirst($property);

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        throw new \Exception("Property `{$property}` not found on Pest\\CraftCms\\NodeList");
    }

    function getNodeOrNodes(callable $callback) {
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

    function getText(): string {
        return $this->getNodeOrNodes(fn ($node) => $node->text());
    }

    public function getInnerHTML(): string  {
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

    public function assertCount($expected) {
        test()->assertCount($expected, $this);
        return $this;
    }
}
