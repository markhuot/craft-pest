# Assets
Assets can be generated via the `Asset` factory. By only defining a volume you can create an entire asset that is
ready to be inserted in to Craft's asset library.
```php
$volume = Volume::factory()->create();
Asset::factory()->volume($volume->handle)->create();
```
> **Note**
> Any assets created during a test will be cleaned up and deleted after the test.

## volume(string $handle)
Set the volume of the asset. Note: if you point this to a live volume that is in use in
production then your test assets will go to your live volume that is in use in production.
Commonly, you will want to set this to an a temporary volume, that is only used in tests.


If you're using an older version of Craft that does not support swappable filesystems you can
use the `LocalVolumes` trait to convert any S3 volumes in to local folder volumes during
test.
Add the `LocalVolumes` trait to your `Pest.php`'s `uses()` call like so,
```php
uses(
  markhuot\craftpest\test\TestCase::class,
  markhuot\craftpest\test\LocalVolumes::class,
);
```

## folder(craft\models\VolumeFolder $folder)
Set the folder the asset should be created within.

## source(string $source)
By default the Asset factory will create a 500x500 gray square. If, however, you'd like to
upload an existing file you can specify the local path to the file via the `->source($path)`.
```php
Asset::factory()->volume($volume)->source('/path/to/file.jpg')->create();
```