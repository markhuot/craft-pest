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

    function setUpRefreshesDatabase()
    {
        $this->refreshDatabase();
        $this->beginTransaction();
    }

    function refreshDatabase()
    {
        if (static::$projectConfigCheckedOnce) {
            return;
        }
        static::$projectConfigCheckedOnce = true;

        if ($this->hasPendingMigrations()) {
            $this->runMigrations();
        }

        if ($this->isProjectConfigDirty()) {
            $this->projectConfigApply();
        }
    }

    /**
     * @todo
     */
    protected function hasPendingMigrations()
    {
        return false;
    }

    /**
     * @todo
     */
    protected function runMigrations()
    {

    }

    protected function isProjectConfigDirty()
    {
        return ProjectConfig::diff() !== '';
    }

    protected function projectConfigApply()
    {
        $process = new Process(['./craft', 'project-config/apply', '--force']);
        $process->setTty(true);
        $process->setTimeout(null);
        $process->start();

        foreach ($process as $type => $data) {
            if ($type === $process::OUT) {
                echo $data;
            } else {
                echo $data;
            }
        }

        if (!$process->isSuccessful()) {
            throw new \Exception('Project config apply failed');
        }
    }

    protected function beginTransaction()
    {
        $this->transaction = \Craft::$app->db->beginTransaction('READ UNCOMMITTED');
    }

    protected function tearDownRefreshesDatabase()
    {
        $this->transaction->rollBack();

        $event = new RollbackTransactionEvent();
        $event->sender = $this;
        Event::trigger(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', $event);
    }

}
