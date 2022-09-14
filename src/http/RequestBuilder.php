<?php

namespace markhuot\craftpest\http;

use craft\web\User;
use markhuot\craftpest\http\requests\GetRequest;
use markhuot\craftpest\http\requests\PostRequest;
use markhuot\craftpest\http\requests\WebRequest;
use markhuot\craftpest\web\Application;
use markhuot\craftpest\web\TestableResponse;
use yii\web\Cookie;

class RequestBuilder
{
    private WebRequest $request;
    private \craft\web\Application $app;
    private RequestHandler $handler;

    public function __construct(
        string         $method,
        string         $uri,
        Application    $app = null,
        RequestHandler $handler = null,
    ) {
        $this->app = $app ?? \Craft::$app;
        $this->handler = $handler ?? new RequestHandler($this->app);
        $this->request = $this->prepareRequest($method, $uri);
    }

    public function addHeader(string $name, $value): self
    {
        $this->request->headers->add( $name, $value);
        return $this;
    }

    public function addCookie(string $key, $value): self
    {
        // TODO
        //$this->request->cookies->add(new Cookie());
        return $this;
    }

    public function setReferrer(?string $value): self
    {
       $this->request->headers->set('Referer', $value);
       return $this;
    }

    public function asUser(User|string $user): self
    {
        // TODO
        return $this;
    }

    public function send(): TestableResponse
    {
        $skipSpecialHandling = false;

        return $this->handler->handle($this->request, $skipSpecialHandling);
    }

    private function uriContainsAdminSlug(string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $slug = $this->app->getConfig()->getGeneral()->cpTrigger ?? 'admin';

        return str_starts_with(ltrim($path,'/'), $slug);
    }

    /**
     * Pre-populate the request object
     */
    private function prepareRequest(string $method, string $uri): WebRequest
    {
        $isCpRequest = $this->uriContainsAdminSlug($uri);
        if ($isCpRequest) {
            $uri = preg_replace('#^'.preg_quote($this->app->getConfig()->getGeneral()->cpTrigger).'/?#', '', $uri);
        }

        $request = match (strtolower($method)) {
            'get' => GetRequest::make($uri),
            'post' => PostRequest::make($uri),
            default => throw new \InvalidArgumentException("Unable to build request. Unknown method '$method'"),
        };

        $request->setIsCpRequest($isCpRequest);

        return $request;
    }
}
