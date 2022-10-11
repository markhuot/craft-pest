<?php

namespace markhuot\craftpest\http\requests;

use craft\helpers\App;
use markhuot\craftpest\traits\Dd;
use yii\web\NotFoundHttpException;

abstract class WebRequest extends \craft\web\Request
{
    use Dd;

    private const HEADER_USER_AGENT = 'Pest-Agent';
    private const HEADER_X_FORWARDED_FOR = '127.0.0.1';

    public static function make($uri): WebRequest
    {
        $config = App::webRequestConfig();
        $config['class'] = static::class;

        $opts = self::defaultProperties($uri);

        /** @var self $request */
        $request = \Craft::createObject($config);
        $request->setRaw($opts);
        $request->headers->set('User-Agent', self::HEADER_USER_AGENT);
        $request->headers->set('X-Forwarded-For', self::HEADER_X_FORWARDED_FOR);

        return $request;
    }


    /**
     * It's called by Application::handleRequest()
     * at a later point
     */
    public function resolve(): array
    {
        if (($result = \Craft::$app->getUrlManager()->parseRequest($this)) === false) {
            throw new NotFoundHttpException(\Craft::t('yii', 'Page not found.'));
        }

        [$route, $params] = $result;

        // This is stupid, but it works
        if (isset($params['template'])) {
            $params['template'] = str_replace('admin/', '', $params['template']);
        }

        /** @noinspection AdditionOperationOnArraysInspection */
        return [$route, $params + $this->getQueryParams()];
    }

    public function setBody($body): self
    {
        $this->setRaw([
            '_rawBody' => $body,
            '_bodyParams' => null,
        ]);

        return $this;
    }

    /**
     * Populate private properties
     */
    protected function setRaw(array $props) {
        $findProperty = function (\ReflectionClass $ref, $property) {
            while ($ref && !$ref->hasProperty($property)) {
                $ref = $ref->getParentClass();
            }

            return $ref->getProperty($property);
        };

        foreach ($props as $key => $value) {
            $ref = new \ReflectionClass($this);
            $propertyRef = $findProperty($ref, $key);
            if ($propertyRef->isPrivate()) {
                $propertyRef->setAccessible(true);
            }
            $propertyRef->setValue($this, $value);
            if ($propertyRef->isPrivate()) {
                $propertyRef->setAccessible(false);
            }
        }

        return $this;
    }

    protected static function defaultProperties(string $url): array
    {
        // Split path and query params
        $parts = parse_url($url);
        
        $uri = $parts['path'] ?? '';
        $uri = ltrim($uri, '/');

        $queryString = $parts['query'] ?? '';
        
        parse_str($queryString, $queryParams);

        return [
            '_isConsoleRequest' => false,
            '_fullPath' => $uri,
            '_path' => $uri,
            '_fullUri' => $uri.'?'.$queryString,
            '_ipAddress' => '::1',
            '_rawBody' => '',
            '_bodyParams' => [],
            '_queryParams' => $queryParams,
            '_hostInfo' => 'http://localhost:8080',
            '_hostName' => 'localhost',
            '_baseUrl' => '',
            '_scriptUrl' => '/index.php',
            '_scriptFile' => '',
            '_pathInfo' => $uri,
            '_url' => "/{$uri}?{$queryString}",
            '_port' => 8080,
            '_isCpRequest' => false,
        ];
    }

    function assertMethod($method)
    {
        test()->assertSame(strtoupper($method), $this->getMethod());

        return $this;
    }

    function assertBody($body)
    {
        test()->assertEqualsCanonicalizing($body, $this->getBodyParams());

        return $this;
    }

}
