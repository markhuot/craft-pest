<?php

namespace markhuot\craftpest\factories;

use Illuminate\Support\Collection;
use markhuot\craftpest\test\RefreshesDatabase;
use yii\base\Event;
use function markhuot\craftpest\helpers\base\collection_wrap;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

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
        // @phpstan-ignore-next-line Craft 3 doesn't have `VolumeInterface->id` exposed so PHPStan fails this line.
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
            collection_wrap($assets)->each(function (\craft\elements\Asset $asset) {
                if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
                    // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
                    $asset->volume->getFs()->deleteFile($asset->path);
                }
                else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
                    // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
                    $asset->volume->deleteFile($asset->path);
                }

            });
        });

        return $assets;
    }

}
