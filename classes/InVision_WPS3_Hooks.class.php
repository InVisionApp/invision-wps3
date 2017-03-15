<?php
class Invision_WPS3_Hooks extends Invision_WPS3 {
	public function __construct() {
		parent::__construct();

		add_action('add_attachment', [$this, 'handleNonImage']);
		add_action('delete_attachment', [$this, 'handleDelete']);
		add_action('wp_generate_attachment_metadata', [$this, 'handleImage'], 20, 5);
		add_action('wp_update_attachment_metadata', [$this, 'handleImage'], 20, 5);

		add_action('wp_get_attachment_url', [$this, 'transformUrl']);
		add_action('wp_calculate_image_srcset', [$this, 'transformSrcset']);
	}

	// -----------------------------------------------

	public function transformUrl($url) {
		$dir = str_replace(home_url(), null, wp_upload_dir()['baseurl']) . '/';
		$path = str_replace($dir, null, parse_url($url)['path']);

		return str_replace('$1', $path, $this->bucketPath);
	}

	public function transformSrcset($url) {
		foreach ($sources AS &$source)
			$source['url'] = $this->transformUrl($url);

		return $sources;
	}

	// -----------------------------------------------

	public function handleNonImage($id) {
		if (strstr(get_post_mime_type($id), 'image'))
			return $id;

		$results['file'] = $this->getImageKey(get_attached_file($id));
		return $this->upload($results);
	}

	public function handleImage($data) {
		if ($data) $this->upload($data);
		return $data;
	}

	public function handleDelete($id) {
		$data = wp_get_attachment_metadata($id);

		if (!$data)
			$data['file'] = $this->getImageKey(get_attached_file($id));

		$this->remove($data);
	}
}