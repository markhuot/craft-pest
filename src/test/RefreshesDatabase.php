<?php

namespace markhuot\craftpest\test;

use Symfony\Component\Process\Process;
use yii\db\Transaction;

trait RefreshesDatabase {

    /**
     * @var bool
     */
    protected $projectConfigChecked = false;

    /**
     * @var Transaction
     */
    protected $transaction;

    function refreshDatabase() {
        if (!$this->projectConfigChecked && $this->isProjectConfigDirty()) {
            $this->projectConfigApply();
        }

        $this->projectConfigChecked = true;
    }

    protected function isProjectConfigDirty() {
        $process = new Process(['./craft', 'project-config/diff']);
        $exitCode = $process->run();

        return $exitCode !== 0;
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
    }

}
