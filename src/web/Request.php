<?php

namespace markhuot\craftpest\web;

use craft\helpers\App;
use yii\base\Event;
use yii\web\NotFoundHttpException;

class Request extends \craft\web\Request {

    public static function createGetRequestFromUri($uri, $isCpRequest = false): Request
    {
        
        $uri = ltrim($uri, '/');
        $parts = preg_split('/\?/', $uri);
        $uri = $parts[0];
        $queryString = $parts[1] ?? '';

        parse_str($queryString, $queryParams);


        $config = App::webRequestConfig();
        $config['class'] = \markhuot\craftpest\web\Request::class;

        $opts = [
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
            '_isCpRequest' => $isCpRequest,
        ];

        /** @var self $request */
        $request = \Craft::createObject($config)->setRaw($opts);
        $request->headers->set('User-Agent', 'Pest-Agent');
        $request->headers->set('X-Forwarded-For', '127.0.0.1');
        return $request;
    }

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

    function setRaw(array $props) {
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

}
