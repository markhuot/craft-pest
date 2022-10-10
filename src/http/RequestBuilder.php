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

    /**
     * When sending a request we need to fake the $_POST data so
     * before we overwrite it we store a reference to what it was
     * so that we can return it to the original value after our
     * request goes through.
     */
    protected $originalPost;

    /**
     * The intended body for the request
     */
    protected array $body = [];

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

    /**
     * We don't actually _set_ the body here, because our fluent interface means
     * we don't actually know what kind of body to set. For example, the body 
     * may need to be encoded as JSON or as form data. We won't know that until
     * the content-type header is set.
     * Instead, we'll store a reference to what the user _wants_ the body to be
     * and then, right before the request is sent (and the content-type is known),
     * we'll set the body and encode it for them.
     */
    public function setBody(array $body)
    {
        $this->body = $body;

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

        // if (\Craft::alias('@webroot') === '@webroot') {
        //     throw new \Exception('The `@webroot` alias is not set. This could cause requests in Pest to fail.');
        // }
        

        $this->registerBodyValues();
        $response = $this->handler->handle($this->request, $skipSpecialHandling);
        $this->resetBodyValues();

        return $response;
    }

    protected function registerBodyValues(): void
    {
        $this->originalPost = array_merge($_POST);
        $_POST = $body = $this->body ?? [];

        $contentType = $this->request->getContentType();
        $isJson = strpos($contentType, 'json') !== false;

        // Not needed just yet. If we add more content-types we'll need
        // to add more to this conditional
        // $isFormData = strpos($contentType, 'form-data') !== false;

        $this->request->setBody(
            $isJson ? json_encode($body) : http_build_query($body)
        );
    }

    protected function resetBodyValues(): void
    {
        $_POST = $this->originalPost ?? [];
        $this->originalPost = null;
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
    private function prepareRequest(string $method, string $url): WebRequest
    {
        // TODO, figure out what to do here if the host is not
        // our craft host...
        $info = parse_url($url);
        $uri = $info['path'];

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
