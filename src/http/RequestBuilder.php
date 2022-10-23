<?php

namespace markhuot\craftpest\http;

use craft\web\User;
use markhuot\craftpest\http\requests\GetRequest;
use markhuot\craftpest\http\requests\PostRequest;
use markhuot\craftpest\http\requests\WebRequest;
use markhuot\craftpest\web\TestableResponse;
use yii\web\Cookie;

class RequestBuilder
{
    /** @var WebRequest */
    private $request;
    /** @var \craft\web\Application */
    private $app;
    /** @var RequestHandler */
    private $handler;
    protected string $method;
    protected array $body = [];
    protected array $originalGlobals;

    /**
     * @param string $method
     * @param string $uri
     * @param \craft\web\Application|null $app
     * @param RequestHandler|null $handler
     */
    public function __construct(string $method, string $uri, $app = null, $handler = null) {
        $this->method = $method;
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
        $this->request->cookies->add(new Cookie([
            'name' => $key,
            'value' => $value,
        ]));

        return $this;
    }

    public function setBody(array $body): self
    {
        $this->body = array_merge($this->body, $body);

        return $this;
    }

    public function withCsrfToken(): self
    {
        $this->body['CRAFT_CSRF_TOKEN'] = $this->request->getCsrfToken();

        return $this;
    }

    /**
     * @param string|null $value
     */
    public function setReferrer($value): self
    {
       $this->request->headers->set('Referer', $value);
       return $this;
    }

    /**
     * @param User|string $user
     */
    public function asUser($user): self
    {
        // TODO
        return $this;
    }

    public function send(): TestableResponse
    {
        $skipSpecialHandling = false;

        $this->setGlobals();
        $response = $this->handler->handle($this->request, $skipSpecialHandling);
        $this->resetGlobals();

        return $response;
    }

    protected function setGlobals()
    {
        $this->originalGlobals['_POST'] = array_merge($_POST);
        $this->originalGlobals['_SERVER'] = array_merge($_SERVER);
        $_SERVER['HTTP_METHOD'] = $this->method;
        $this->request->headers->add('X-Http-Method-Override', $this->method);
        $_POST = $body = $this->body ?? [];

        if (!empty($body)) {
            $contentType = $this->request->getContentType();
            $isJson = strpos($contentType, 'json') !== false;

            // Not needed just yet. If we add more content-types we'll need
            // to add more to this conditional
            // $isFormData = strpos($contentType, 'form-data') !== false;

            $this->request->setBody(
                $isJson ? json_encode($body) : http_build_query($body)
            );
            $this->request->headers->add('content-type', 
                $isJson ? 'application/json' : 'multipart/form-data'
            );
        }
    }

    protected function resetGlobals()
    {
        $_POST = $this->originalGlobals['_POST'] ?? [];
        $_POST = $this->originalGlobals['_SERVER'] ?? [];
        $this->originalGlobals = [];
    }

    /**
     * Pre-populate the request object
     */
    private function prepareRequest(string $method, string $uri): WebRequest
    {
        switch (strtolower($method)) {
            case 'get': $request = GetRequest::make($uri); break;
            case 'post': $request = PostRequest::make($uri); break;
            default: throw new \InvalidArgumentException("Unable to build request. Unknown method '$method'");
        };

        return $request;
    }
}
