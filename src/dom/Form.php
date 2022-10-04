<?php

namespace markhuot\craftpest\dom;

use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\web\TestableResponse;
use Symfony\Component\DomCrawler\Crawler;

final class Form
{
    public NodeList $node;

    public array $fields = [];

    public function __construct(NodeList $nodeList)
    {
        if ($nodeList->count === 0) {
            throw new \InvalidArgumentException("Unable to select form.");
        }

        if ($nodeList->count > 1) {
            $ids = $nodeList->getNodeOrNodes(fn (Crawler $node) => $node->attr('id'));
            throw new \InvalidArgumentException("From selector is ambiguous. Found {$nodeList->count} forms: {$ids}.");
        }

        $this->node = $nodeList;
    }

    public function fill(string $field, mixed $value): self
    {
        $this->fields[$field] = $value;

        return $this;
    }

    public function submit(): TestableResponse
    {
        $uri =  $this->node->getNodeOrNodes(fn (Crawler $node) => $node->attr('id'));
        $request = new RequestBuilder('post', $uri);
        $request->setBodyParams($this->fields);

        return $request->send();
    }
}
