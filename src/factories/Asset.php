<?php

namespace markhuot\craftpest\factories;

use Illuminate\Support\Collection;
use markhuot\craftpest\test\RefreshesDatabase;
use yii\base\Event;

class Asset extends Element {

    /** @var string */
    protected $volumeHandle;

    /** @var string */
    protected $folderHandle;

    function volume($handle) {
        $this->volumeHandle = $handle;

        return $this;
    }

    function folder($handle) {
        $this->folderHandle = $handle;

        return $this;
    }

    function newElement() {
        return new \craft\elements\Asset();
    }

    function definition(int $index = 0)
    {
        $volume = \Craft::$app->volumes->getVolumeByHandle($this->volumeHandle);
        $folder = \Craft::$app->assets->getRootFolderByVolumeId($volume->id);

        $originalFile = CRAFT_BASE_PATH . '/storage/temp/leaves.png';

        $tempPath = \Craft::$app->path->getTempPath();
        $tempFile = tempnam($tempPath, 'pest');
        file_put_contents($tempFile, file_get_contents($originalFile));

        return array_merge(parent::definition($index), [
            'folderId' => $folder->id,
            'tempFilePath' => $tempFile,
            'filename' => basename($originalFile),
        ]);
    }

    function create($definition=[]) {
        $return = parent::create($definition);

        /** @var Collection $assets */
        $assets = is_a($return, Collection::class) ? $return : collect()->push($return);

        Event::on(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', function () use ($assets) {
            $assets->each(fn (\craft\elements\Asset $asset) => $asset->volume->deleteFile($asset->path));
        });

        return $return;
    }

}
