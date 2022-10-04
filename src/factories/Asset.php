<?php

namespace markhuot\craftpest\factories;

use craft\models\VolumeFolder;
use Illuminate\Support\Collection;
use markhuot\craftpest\test\RefreshesDatabase;
use yii\base\Event;
use function markhuot\craftpest\helpers\base\collection_wrap;
use function markhuot\craftpest\helpers\craft\volumeDeleteFileAtPath;

/**
 * Assets can be generated via the `Asset` factory. By only defining a volume you can create an entire asset that is
 * ready to be inserted in to Craft's asset library.
 *
 * ```php
 * $volume = Volume::factory()->create();
 * Asset::factory()->volume($volume->handle)->create();
 * ```
 *
 * Note: any assets created during a test will be cleaned up and deleted after the test.
 */
class Asset extends Element {

    /** @var string */
    protected $volumeHandle;

    /** @var VolumeFolder */
    protected $folder;

    /** @var string */
    protected $source;

    /**
     * Set the volume of the asset. Note: if you point this to a live volume that is in use in
     * production then your test assets will go to your live volume that is in use in production.
     *
     * Commonly, you will want to set this to an a temporary volume, that is only used in tests.
     */
    function volume(string $handle) {
        $this->volumeHandle = $handle;

        return $this;
    }

    /**
     * Set the folder the asset should be created within.
     */
    function folder(VolumeFolder $folder) {
        $this->folder = $folder;

        return $this;
    }

    /**
     * By default the Asset factory will create a 500x500 gray square. If, however, you'd like to
     * upload an existing file you can specify the local path to the file via the `->source($path)`.
     *
     * ```php
     * Asset::factory()->volume($volume)->source('/path/to/file.jpg')->create();
     * ```
     */
    function source(string $source) {
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
        $folder = $this->folder ?: \Craft::$app->assets->getRootFolderByVolumeId($volume->id);

        $tempPath = \Craft::$app->path->getTempPath();
        $tempFile = tempnam($tempPath, 'pest');

        if ($this->source) {
            $filename = basename($this->source);
            file_put_contents($tempFile, file_get_contents($this->source));
        }
        else {
            $filename = 'asset' . mt_rand(1000, 9999) . '.jpg';
            file_put_contents($tempFile, file_get_contents(__DIR__ . '/../../stubs/images/gray.jpg'));
        }

        return array_merge(parent::definition($index), [
            'folderId' => $folder->id,
            'tempFilePath' => $tempFile,
            'filename' => $filename,
        ]);
    }

    /**
     * @return \craft\elements\Asset|Collection
     */
    function create(array $definition=[])
    {
        $assets = parent::create($definition);

        Event::on(RefreshesDatabase::class, 'EVENT_ROLLBACK_TRANSACTION', function () use ($assets) {
            foreach (collection_wrap($assets) as $asset) {
                volumeDeleteFileAtPath($asset->volume, $asset->path);
            }
        });

        return $assets;
    }

}
