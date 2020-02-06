<?php
class InVision_WPS3 {
  protected $client,
  $key, $secret,
    $bucketPath;

  public function __construct() {
    $this->key        = $this->getOption('S3_ACCESS_KEY');
    $this->secret     = $this->getOption('S3_SECRET_KEY');
    $this->region     = $this->getOption('S3_REGION') ?: 'US Standard';
    $this->bucket     = $this->getOption('S3_BUCKET');
    $this->bucketPath = $this->getOption('S3_BUCKET_PATH');

    if (!(
      $this->key && $this->secret
      && $this->bucket && $this->bucketPath
    )) {
      return;
    }

    try {
      $this->client = (new Aws\Sdk([
        'region'      => $this->region,
        'version'     => '2006-03-01',
        'credentials' => [
          'key'    => $this->key,
          'secret' => $this->secret
        ]
      ]))->createS3();
    } catch (Exception $e) {
      wp_die($e);
    }
  }

  public function bind() {
    return new InVision_WPS3_Hooks();
  }

  protected function download($id, $local) {
    try {
      $file = $this->parseBucketPath(wp_get_attachment_metadata($id)['file'], true);

      if ($this->client->doesObjectExist($this->bucket, $file)) {
        $this->client->getObject([
          'Bucket' => $this->bucket,
          'Key'    => $file,
          'SaveAs' => $local
        ]);
      }
    } catch (Exception $e) {
      wp_die($e);
    }

    return $local;
  }

  protected function getImageKey($path) {
    preg_match("/\/([0-9]+\/[0-9]+\/.+)$/", $path, $matches);
    return $matches[1];
  }

  protected function getOption($key) {
    return defined($key) ? constant($key) : false;
  }

  protected function getSubdir($filename) {
    if (preg_match("/([0-9]+\/[0-9]+)\/(.+)$/", $filename, $matches) === 1) {
      return (count($matches) > 1) ? $matches[1] : null;
    }
    return null;
  }

  protected function parseBucketPath($filename, $sanitize = false) {
    $filename = trim($filename, '/');
    $url = str_replace('$1', $filename, $this->bucketPath);

    if ($sanitize):
      $pos = strrpos($url, $this->bucket);
      $url = trim(substr($url, $pos + strlen($this->bucket)), '/');
    endif;

    return $url;
  }

  protected function remove($data) {
    foreach ($this->genKeys($data) as $k):
      try {
        $file = $this->parseBucketPath($k, true);

        if ($this->client->doesObjectExist($this->bucket, $file)) {
          $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $file
          ]);
        }
      } catch (\Exception $e) {
        wp_die($e);
      }
    endforeach;

    return true;
  }

  // -----------------------------------------------

  protected function upload($data) {
    set_time_limit(120);

    foreach ($this->genKeys($data) as $k):
      $local  = wp_upload_dir()['basedir'].'/'.$k;
      $remote = $this->parseBucketPath($k, true);

      if (!file_exists($local)) {
        continue;
      }

      $mu = new Aws\S3\MultipartUploader($this->client, $local, [
        'bucket'          => $this->bucket,
        'key'             => $this->client->encodeKey($remote),
        'concurrency'     => 10,
        'part_size'       => 5242880,
        'acl'             => 'public-read',
        'before_initiate' => function (\Aws\Command $cmd) {
          $cmd['CacheControl'] = 'max-page='. 172800;
        }
      ]);

      try {
        $res = $mu->upload();
        unlink($local);
        error_log('Upload complete: '.$res['ObjectURL']);
      } catch (Aws\Exception\MultipartUploadException $e) {
        wp_die($e->getMessage());
      }
    endforeach;

    return true;
  }

  // -----------------------------------------------

  private function genKeys($data) {
    $path   = $this->getSubdir($data['file']);
    $keys[] = $data['file'];

    if (isset($data['sizes'])) {
      foreach ($data['sizes'] as $s => $r) {
        $keys[] = (!is_null($path)) ? $path.'/'.$r['file'] : $r['file'];
      }
    }

    return $keys;
  }
}
