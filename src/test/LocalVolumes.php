<?php

namespace markhuot\craftpest\test;

use craft\events\ModelEvent;
use craft\helpers\ProjectConfig;
use Symfony\Component\Process\Process;
use yii\base\Application;
use yii\base\Event;
use yii\db\Transaction;

trait LocalVolumes {

    function setUpLocalVolumes() {
        \Craft::$container->set(\craft\awss3\Volume::class, function($container, $params, $config) {
            if (empty($config['id'])) {
                return new \craft\awss3\Volume($config);
            }

            return new \craft\volumes\Local([
                'id' => $config['id'],
                'uid' => $config['uid'],
                'name' => $config['name'],
                'handle' => $config['handle'],
                'hasUrls' => $config['hasUrls'],
                'url' => "@web/volumes/{$config['handle']}",
                'path' => "@webroot/volumes/{$config['handle']}",
                'sortOrder' => $config['sortOrder'],
                'dateCreated' => $config['dateCreated'],
                'dateUpdated' => $config['dateUpdated'],
                'fieldLayoutId' => $config['fieldLayoutId'],
            ]);
        });
    }

}
