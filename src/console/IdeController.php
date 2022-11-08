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
    /**
     * Run the Pest tests
     */
    function actionGenerateMixins()
    {
        (new RenderCompiledClasses)->handle();

        echo "Mixins successfully generated!\n";

        return ExitCode::OK;
    }
}
