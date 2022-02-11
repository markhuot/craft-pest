<?php

namespace markhuot\craftpest\dom;

use function Webmozart\Assert\Tests\StaticAnalysis\methodExists;

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

    function getText() {
        if ($this->crawler->count() === 1) {
            return $this->crawler->eq(0)->text();
        }

        $result = [];
        for ($i=0; $i<$this->crawler->count(); $i++) {
            $node = $this->crawler->eq($i);
            $result[] = $node->text();
        }

        return $result;
    }

    public function count() {
        return $this->crawler->count();
    }

    public function getCount() {
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
