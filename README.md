# Invison WPS3
This is a simple solution to a simple problem. The plugin takes some constants, and pushes all uploads to s3. Since we're working with _existing_ s3 buckets, it will just assume they are there already and rewrite the URLs.

```wp-config.php
define('S3_ACCESS_KEY', 'youraccesskey');
define('S3_SECRET_KEY', 'yoursecretkey');
define('S3_BUCKET', 'yourbucket');
define('S3_BUCKET_PATH', 'https://s3.amazonaws.com/'. S3_BUCKET . '/uploads/$1');
define('S3_REGION', 'us-east-1');
```

Note the `$1` in `S3_BUCKET_PATH`, this will get replaced by the filename/filepath on upload.
