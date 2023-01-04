# Local Volumes
If you're using an older version of Craf that does not support swappable filesystems you can
use the `LocalVolumes` trait to convert any S3 volumes in to local folder volumes during
test.
Add the `LocalVolumes` trait to your `Pest.php`'s `uses()` call like so,
```php
uses(
  markhuot\craftpest\test\TestCase::class,
  markhuot\craftpest\test\LocalVolumes::class,
);
```