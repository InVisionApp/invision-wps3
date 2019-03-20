<?php
class Invision_WPS3_Hooks extends Invision_WPS3 {
  public function __construct() {
    parent::__construct();

    add_action('add_attachment', [$this, 'handleNonImage']);
    add_action('delete_attachment', [$this, 'handleDelete']);

    add_action('wp_generate_attachment_metadata', [$this, 'handleImage'], 20, 5);
    add_action('wp_update_attachment_metadata', [$this, 'handleImage'], 20, 5);
    add_filter('get_attached_file', [$this, 'handleRegen'], 10, 4);

    add_action('wp_get_attachment_url', [$this, 'transformUrl']);
    add_action('wp_calculate_image_srcset', [$this, 'transformSrcset']);
  }

  public function handleDelete($id) {
    $data = wp_get_attachment_metadata($id);

    if (!$data) {
      $data['file'] = $this->getImageKey(get_attached_file($id));
    }

    return $this->remove($data);
  }

  public function handleImage($data) {
    if ($data) {
      $this->upload($data);
    }

    return $data;
  }

  // -----------------------------------------------

  public function handleNonImage($id) {
    if (strstr(get_post_mime_type($id), 'image')) {
      return $id;
    }

    $results['file'] = $this->getImageKey(get_attached_file($id));
    return $this->upload($results);
  }

  public function handleRegen($url, $id) {
    if (
      isset($_POST['action'])
      && $_POST['action'] === 'regeneratethumbnail'
      && ($file = $this->download($id, $url))
    ) {
      return $file;
    }

    return $this->transformUrl($url);
  }

  public function transformSrcset($sources) {
    foreach ($sources as &$source) {
      $source['url'] = $this->transformUrl($source['url']);
    }

    return $sources;
  }

  // -----------------------------------------------

  public function transformUrl($url) {
    $parts = parse_url(site_url());

    if (isset($parts['host'])) {
      $pattern = '/^https?:\/\/'.$parts['host'];
      $pattern .= isset($parts['port']) ? ":{$parts['port']}" : '';
      $pattern .= '/';

      $dir  = preg_replace($pattern, null, wp_upload_dir()['baseurl']).'/';
      $path = str_replace($dir, null, parse_url($url)['path']);

      return $this->parseBucketPath($this->encode($path));
    }

    return $url;
  }

  private function encode($str) {
    $symbols = ['&', '$', '@', '=', ':', '+', ',', '?'];

    foreach ($symbols as $s) {
      $str = str_replace($s, urlencode($s), $str);
    }

    return $str;
  }
}
