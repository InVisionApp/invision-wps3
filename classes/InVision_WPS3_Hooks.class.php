<?php
class Invision_WPS3_Hooks extends Invision_WPS3 {
	public function __construct() {
		parent::__construct();

		add_action('add_attachment', [$this, 'handleUpload']);
		add_action('wp_get_attachment_url', [$this, 'transformUrl']);
		add_action('wp_calculate_image_srcset', [$this, 'transformSrcset']);
	}

	private function handleUpload() {
		//
	}

	public function transformUrl($url) {
		$info = parse_url($url);
		return str_replace('$1', $info['path'], $this->bucketPath);
	}

	public function transformSrcset($url) {
		foreach ($sources AS &$source)
			$source['url'] = $this->transformUrl($url);

		return $sources;
	}
}