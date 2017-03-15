<?php
class Invision_S3 {
	private
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

		$this->client = new Aws\Sdk([
			'region' => $region,
			'version' => '2006-03-01',
			'credentials' => [
				'key' => $this->key,
				'secret' => $this->secret,
			],
		])->createS3();
	}

	private function getOption($key) {
		if (defined($key))
			return getenv($key);

		return false;
	}
}