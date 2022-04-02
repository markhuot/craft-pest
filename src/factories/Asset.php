<?php

namespace markhuot\craftpest\factories;

use Illuminate\Support\Collection;
use markhuot\craftpest\test\RefreshesDatabase;
use yii\base\Event;
use function markhuot\craftpest\helpers\model\collectOrCollection;

class Asset extends Element {

    /** @var string */
    protected $volumeHandle;

    /** @var string */
    protected $folderHandle;

    /** @var string */
    protected $source;

    function volume($handle) {
        $this->volumeHandle = $handle;

        return $this;
    }

    function folder($handle) {
        $this->folderHandle = $handle;

        return $this;
    }

    function source($source) {
        $this->source = $source;

        return $this;
    }

    function newElement() {
        return new \craft\elements\Asset();
    }

    function definition(int $index = 0)
    {
        $volume = \Craft::$app->volumes->getVolumeByHandle($this->volumeHandle);
        $folder = \Craft::$app->assets->getRootFolderByVolumeId($volume->id);

        $tempPath = \Craft::$app->path->getTempPath();
        $tempFile = tempnam($tempPath, 'pest');
        file_put_contents($tempFile, file_get_contents($this->source));

        return array_merge(parent::definition($index), [
            'folderId' => $folder->id,
            'tempFilePath' => $tempFile,
            'filename' => basename($this->source),
        ]);
    }


    /**
     * @param array $definition
     *
     * @return \craft\elements\Asset|Collection
     */
    function create($definition=[]) {
        $assets = parent::create($definition);

        Event::on(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', function () use ($assets) {
            collectOrCollection($assets)->each(fn (\craft\elements\Asset $asset) => $asset->volume->deleteFile($asset->path));
        });

        return $assets;
    }

}
