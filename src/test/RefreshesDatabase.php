<?php

namespace markhuot\craftpest\test;

use craft\events\ModelEvent;
use craft\helpers\ProjectConfig;
use Symfony\Component\Process\Process;
use yii\base\Event;
use yii\db\Transaction;

trait RefreshesDatabase {

    /**
     * @var bool
     */
    public static $projectConfigCheckedOnce = false;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @TODO projectConfigChecked is reset on each test run, this runs every test not once before all tests
     */
    function refreshDatabase() {
        if (!static::$projectConfigCheckedOnce && $this->isProjectConfigDirty()) {
            $this->projectConfigApply();
        }

        static::$projectConfigCheckedOnce = true;
    }

    protected function isProjectConfigDirty() {
        return ProjectConfig::diff() !== '';
    }

    protected function projectConfigApply() {
        $process = new Process(['./craft', 'project-config/apply', '--force']);
        $process->setTty(true);
        $process->start();

        foreach ($process as $type => $data) {
            if ($type === $process::OUT) {
                echo $data;
            } else {
                echo $data;
            }
        }
    }

    protected function beginTransaction() {
        $this->transaction = \Craft::$app->db->beginTransaction('READ UNCOMMITTED');
    }

    protected function endTransaction() {
        $this->transaction->rollBack();

        $event = new RollbackTransactionEvent();
        $event->sender = $this;
        Event::trigger(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', $event);
    }

}
