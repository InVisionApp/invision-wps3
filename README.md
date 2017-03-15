# Invison WPS3
This is a simple solution to a simple problem. The plugin takes some constants, and pushes all uploads to s3. Since we're working with _existing_ s3 buckets, it will just assume they are there already and rewrite the URLs.

```wp-config.php
define('S3\_ACCESS\_KEY', 'youraccesskey');
define('S3\_SECRET\_KEY', 'yoursecretkey');
define('S3\_BUCKET', 'yourbucket');
define('S3\_BUCKET\_PATH', 'https://s3.amazonaws.com/'. S3\_BUCKET . '/uploads/$1');
define('S3\_REGION', 'us-east-1');
```

Note the `$1` in S3\_BUCKET\_PATH, this will get replaced by the filename/filepath on upload.