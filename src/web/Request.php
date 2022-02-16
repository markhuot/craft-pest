<?php

namespace markhuot\craftpest\web;

use yii\base\Event;

class Request extends \craft\web\Request {

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
