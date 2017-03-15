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
		$this->bucketPath = $this->getOption('S3_BUCKET_PATH');

		if (!(
			$this->key
			|| $this->secret
			|| $this->bucketPath
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
		}

		catch (Exception $e) {
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
}