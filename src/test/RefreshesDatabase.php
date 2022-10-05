<?php

namespace markhuot\craftpest\test;

use craft\helpers\ProjectConfig;
use markhuot\craftpest\events\FactoryStoreEvent;
use markhuot\craftpest\events\RollbackTransactionEvent;
use markhuot\craftpest\exceptions\AutoCommittingFieldsException;
use markhuot\craftpest\factories\Factory;
use markhuot\craftpest\factories\Field;
use Symfony\Component\Process\Process;
use yii\base\Event;
use yii\db\Transaction;

trait RefreshesDatabase {

    /**
     * @var bool
     */
    public static $projectConfigCheckedOnce = false;

    /**
     * The config version before the test ran, so we can re-set it back after
     *
     * @var string
     */
    public $oldConfigVersion = null;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * Whether the current transaction has tried to write entries, elements, etc… to the
     * database already. Because MySQL has an implicit COMMIT on `ALTER TABLE` queries we
     * have to make sure that all `Field::factory()` calls are first so we can manually
     * roll field changes back.
     *
     * @var bool
     */
    protected $hasStoredNonFieldContent = false;

    /**
     * An array of models that were auto committed to the database and must be manually rolled
     * back because they live outside of the transaction lifecycle.
     *
     * @var array
     */
    protected $autoCommittedModels = [];

    function setUpRefreshesDatabase()
    {
        $this->listenForStores();
        $this->refreshDatabase();
        $this->beginTransaction();
    }

    protected function tearDownRefreshesDatabase()
    {
        $this->rollBackTransaction();
        $this->rollBackAutoCommittedModels();
        $this->stopListeningForStores();
    }

    protected function listenForStores()
    {
        $this->hasStoredNonFieldContent = false;

        Event::on(Factory::class, Factory::EVENT_BEFORE_STORE, [$this, 'beforeStore']);
        Event::on(Factory::class, Factory::EVENT_AFTER_STORE, [$this, 'afterStore']);
    }

    protected function stopListeningForStores()
    {
        Event::off(Factory::class, Factory::EVENT_BEFORE_STORE, [$this, 'beforeStore']);
        Event::off(Factory::class, Factory::EVENT_AFTER_STORE, [$this, 'afterStore']);
    }

    function beforeStore(FactoryStoreEvent $event) {
        $isFieldFactory = is_a($event->sender, Field::class) || is_subclass_of($event->sender, Field::class);

        if ($isFieldFactory && $this->hasStoredNonFieldContent) {
            throw new AutoCommittingFieldsException('You can not create fields after creating elements while refreshesDatabase is in use.');
        }

        if (!$isFieldFactory) {
            $this->hasStoredNonFieldContent = true;
        }
    }

    function afterStore(FactoryStoreEvent $event)
    {
        $isFieldFactory = is_a($event->sender, Field::class) || is_subclass_of($event->sender, Field::class);

        // Fields are autocommitted so we'll track those for removal later
        if ($isFieldFactory) {
            $this->autoCommittedModels[] = $event->model;
        }

        // If Yii thinks we're in a transaction but the transaction isn't
        // active any more (probably because it was autocommitted) then we'll
        // reset the internal state and re-start the transaction
        $transaction = \Craft::$app->db->getTransaction();
        if ($transaction && !\Craft::$app->db->pdo->inTransaction()) {
            $transaction->commit();
            $this->beginTransaction();
        }
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
        $process = new Process(['./craft', 'project-config/apply']);
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

    function beginTransaction()
    {
        $this->oldConfigVersion = \Craft::$app->info->configVersion;
        $this->transaction = \Craft::$app->db->beginTransaction();
    }

    function rollBackTransaction()
    {
        if (empty($this->transaction)) {
            return;
        }

        $this->transaction->rollBack();

        $event = new RollbackTransactionEvent();
        $event->sender = $this;
        Event::trigger(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', $event);

        \Craft::$app->info->configVersion = $this->oldConfigVersion;
        $this->transaction = null;
    }

    function rollBackAutoCommittedModels()
    {
        foreach ($this->autoCommittedModels as $model) {
            if (is_a($model, \craft\base\Field::class) || is_subclass_of($model, \craft\base\Field::class)) {
                \Craft::$app->fields->deleteField($model);
            }
        }
    }

}
