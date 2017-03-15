<?php
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

class InVision_WPS3 {
	protected
		$client,
		$key, $secret,
		$bucketPath;

	public function __construct() {
		$this->key = $this->getOption('S3_ACCESS_KEY');
		$this->secret = $this->getOption('S3_SECRET_KEY');
		$this->region = $this->getOption('S3_REGION') ?: 'US Standard';
		$this->bucket = $this->getOption('S3_BUCKET');
		$this->bucketPath = $this->getOption('S3_BUCKET_PATH');

		if (!(
			$this->key && $this->secret
			&& $this->bucket && $this->bucketPath
		)) return;

		try {
			$this->client = (new Aws\Sdk([
				'region' => $this->region,
				'version' => '2006-03-01',
				'credentials' => [
					'key' => $this->key,
					'secret' => $this->secret,
				],
			]))->createS3();
		} catch (Exception $e) {
			wp_die($e);
		}
	}

	public function bind() {
		return new InVision_WPS3_Hooks();
	}

	protected function getOption($key) {
		if (defined($key))
			return constant($key);

		return false;
	}

	// -----------------------------------------------

	private function genKeys($data) {
		$path = $this->getSubdir($data['file']);
		$keys[] = $data['file'];

		if (isset($data['sizes']))
		foreach ($data['sizes'] AS $s => $r)
			$keys[] = $path . '/' . $r['file'];

		return $keys;
	}

	protected function getSubdir($filename) {
		preg_match("/([0-9]+\/[0-9]+)\/(.+)$/", $data['file'], $matches);
		return $matches[1];
	}

	protected function getImageKey($path) {
		preg_match("/\/([0-9]+\/[0-9]+\/.+)$/", $path, $matches);
		return $matches[1];
	}

	// -----------------------------------------------

	protected function upload($data) {
		set_time_limit(120);

		array_map($this->genKeys($data), function($k) {
			$localFile = wp_upload_dir('basedir');
			$remoteFile = 'not sure yet';

			echo $localFile, '<hr />', $remoteFile;
			exit;
		});
	}

	protected function delete($data) {
		array_map($this->genKeys($data), function($k) {
			try {
				$this->client->deleteObject([
					'Bucket' => $this->bucket,
					'Key' => $file,
				]);
			} catch (Exception $e) {
				wp_die($e);
			}
		});
	}
}