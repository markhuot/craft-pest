<?php

namespace markhuot\craftpest\console;

use craft\console\Controller;
use craft\helpers\FileHelper;
use markhuot\craftpest\actions\RenderCompiledClasses;
use Symfony\Component\Process\Process;
use yii\console\ExitCode;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

class IdeController extends Controller
{
    public bool $force = false;

    function options($actionID): array
    {
        return [
            'force',
        ];
    }

    /**
     * Run the Pest tests
     */
    function actionGenerateMixins()
    {
        $result = (new RenderCompiledClasses)->handle($this->force);

        if ($result) {
            echo "Mixins successfully generated!\n";
        }
        else {
            echo "Mixins already exist, skipping.\n";
        }

        return ExitCode::OK;
    }
}
