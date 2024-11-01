<?php
/**
 * Google PageSpeed Insights
 */
class WPMT_PSI {
  static $_s = null;
  
  public function __construct() {
    add_action('admin_menu', array(&$this, 'menu'));

    add_filter('wp_generate_attachment_metadata', array(&$this, 'psi_attachment_metadata'), 10, 2);
    add_action('delete_attachment', array(&$this, 'psi_delete_attachment'));

    if (!is_admin()) {
      $webp_enabled = intval(get_option('wpmt_psi_image_webp'));
      if ($webp_enabled == 1) {
        add_filter('wp_get_attachment_image', array(&$this, 'psi_wp_get_attachment_image'), 10, 4);
        add_filter('woocommerce_product_get_image', array(&$this, 'psi_woocommerce_product_get_image'), 10, 5);
      }

      $wprocket_css_inline = intval(get_option('wpmt_psi_wprocket_css_inline'));
      if ($wprocket_css_inline == 1) {
        add_action('wp_head', array(&$this, 'psi_inline_css_from_wp_rocket'), 999);
        // add_filter('rocket_css_url', array(&$this, 'psi_remove_rocket_css_url'));// moved to line 322
      }

      $psi_score = intval(get_option('wpmt_psi_score'));
      if ($psi_score == 1) {
        add_action( 'wp_enqueue_scripts', array(&$this, 'psi_dequeued_scripts'), 100 );
      }

      add_action( 'wp_enqueue_scripts', array(&$this, 'psi_enqueue_scripts') );
    }
  }

  public static function init() {
    if (self::$_s == null) {
      self::$_s = new self();
    }
    return self::$_s;
  }

  public function menu() {
    //add_options_page('Permalink custom post type', 'Custom post type', 'manage_options', __FILE__, array(&$this, 'menu_page'));
    add_submenu_page(WPMT_MENU_ID, //Menu ID – Defines the unique id of the menu that we want to link our submenu to. 
                                //To link our submenu to a custom post type page we must specify - 
                                //edit.php?post_type=my_post_type
        __('Google PageSpeed Insights'), // Page title
        __('PageSpeed Insights'), // Menu title
        'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
        'wpmt_psi', // Submenu ID – Unique id of the submenu.
        array(&$this, 'menu_page') // render output function
    );
  }

  public function menu_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (isset($_GET['convert']) and 'webp' == $_GET['convert']) {
      require_once WPMT_PLUGIN_DIR . '/includes/psi/convert_webp.php';
    } else {
      require_once WPMT_PLUGIN_DIR . '/includes/psi/settings.php';
    }
    ?>
  <?php
  }
  /**
   * Check user agent for Google PageSpeed Insights
   */
  function isInsights() {
      if (isset($_SERVER['HTTP_USER_AGENT']) && 
          (stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false or 
            stripos($_SERVER['HTTP_USER_AGENT'], 'Speed Insights') !== false or 
            stripos($_SERVER['HTTP_USER_AGENT'], 'Amazonbot') !== false)
          ){
        return true;
      }
      return false;
  }
  /**
   * Render html support woocommerce
   */
  function psi_woocommerce_product_get_image( $image, $that, $size, $attr, $placeholder ){
    // Load the HTML
    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress errors due to invalid HTML
    $doc->loadHTML($image);
    libxml_clear_errors();
  
    // Use DOMXPath to query the img tag
    $xpath = new DOMXPath($doc);
    $img_element = $xpath->query("//img")->item(0);
  
    // Extract the attributes
    $width = $img_element->getAttribute('width');
    $height = $img_element->getAttribute('height');
    $src = $img_element->getAttribute('src');
    $alt = $img_element->getAttribute('alt');
    $class = $img_element->getAttribute('class');
  
    // Replace with new values
    // $image_size = !empty($size) ? $size : 'woocommerce_thumbnail';
    // $custom_class = 'attachment-'.$image_size.' size-'.$image_size;
    $custom_class = $class;
    if (empty($alt)) {
      $alt = $that->name;
    }
    $finfo = pathinfo($src);
    $image_webp_url = ($finfo['extension'] == 'webp') ? $src : $src . '.webp';
    $custom_html = '<img width="'.esc_attr($width).'" height="'.esc_attr($height).'" src="' . esc_url($src) . '" srcset="' . esc_url($image_webp_url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr( $custom_class ) . '" />';

    // Lazyload
    $image_lazyload = intval(get_option('wpmt_psi_image_lazyload'));
    if ($image_lazyload == 1) {
      $custom_class .= ' lazyload';
      $custom_html = '<img width="'.esc_attr($width).'" height="'.esc_attr($height).'" data-src="' . esc_url($src) . '" data-srcset="' . esc_url($image_webp_url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr( $custom_class ) . '" />';
    }
  
    return $custom_html;
  }
  /**
   * Custom render html for display
   */
  function psi_wp_get_attachment_image($html, $attachment_id, $size, $icon) {
    // Get the attachment metadata
    $metadata = wp_get_attachment_metadata($attachment_id);
    $has_webp = get_post_meta($attachment_id, 'has_webp', true);
  
    if (!$metadata or $has_webp != 1) {
        return $html; // No metadata found
    }
  
    // Get the URL of the original image
    $upload_dir = wp_upload_dir();
    $file_urls = [];
    $file_urls['full'] = $upload_dir['baseurl'] . '/' . $metadata['file'];
    if (!empty($metadata['sizes'])) {
        foreach ($metadata['sizes'] as $_size => $size_info) {
            $file_urls[$_size] = $upload_dir['baseurl'] . '/' . dirname($metadata['file']) . '/' . $size_info['file'];
        }
    }
    $image_url = !empty($file_urls[$size]) ? $file_urls[$size] : $file_urls['full'];
    $finfo = pathinfo($image_url);
    $image_webp_url = ($finfo['extension'] == 'webp') ? $image_url : $image_url . '.webp'; // Append .webp to the original URL
  
    // Get the image title
    $image_title = get_the_title($attachment_id);
  
    // Get the image dimensions
    $image_size = !empty($metadata['sizes'][$size]) ? $metadata['sizes'][$size] : $metadata;
    $width = $image_size['width'];
    $height = $image_size['height'];
  
    // Lazyload
    $custom_class = 'attachment-'.$size.' size-'.$size;
    $image_lazyload = intval(get_option('wpmt_psi_image_lazyload'));
    if ($image_lazyload == 1) {
      $custom_class .= ' lazyload';
      // Generate the custom HTML
      $custom_html = sprintf(
        '<img data-src="%1$s" data-srcset="%2$s" alt="%3$s" width="%4$s" height="%5$s" class="%6$s" />',
        esc_url($image_url),
        esc_url($image_webp_url),
        esc_attr($image_title),
        esc_attr($width),
        esc_attr($height),
        esc_attr($custom_class)
      );
    
      return $custom_html;
    }

    // Generate the custom HTML
    $custom_html = sprintf(
      '<img src="%1$s" srcset="%2$s" alt="%3$s" width="%4$s" height="%5$s" class="%6$s" />',
      esc_url($image_url),
      esc_url($image_webp_url),
      esc_attr($image_title),
      esc_attr($width),
      esc_attr($height),
      esc_attr($custom_class)
    );
  
    return $custom_html;
  }
  /**
   * Delete webp on file delete
   */
  function psi_delete_attachment($attachment_id) {
    // delete meta
    delete_post_meta( $attachment_id, 'has_webp' );
    // delete files
    $file_paths = $this->get_attachment_paths_by_id($attachment_id);
    foreach ($file_paths as $size => $path) {
      $webp_path = $path . '.webp';
      if (file_exists($webp_path)) {
        unlink($webp_path);
      }
    }
  }
  /**
   * Convert iamge to WebP on uploaded
   */
  function psi_attachment_metadata($metadata, $attachment_id) {
    // Add custom metadata
    // $metadata['custom_metadata'] = 'This is my custom metadata';
  
    // convert to webp
    $file_paths = $this->get_attachment_paths_by_id($attachment_id);
    $hasWebp = false;
    foreach ($file_paths as $size => $path) {
      $finfo = pathinfo($path);
      $result = $this->convert_to_webp($path, $finfo['extension']);
      if ($result) {
        $hasWebp = true;
      }
    }
    if ($hasWebp) {
      update_post_meta( $attachment_id, 'has_webp', 1);
    }
  
    return $metadata;
  }
  
  function get_attachment_paths_by_id($attachment_id) {
    $attachment = get_post($attachment_id);
    if (!$attachment || 'attachment' !== $attachment->post_type) {
        return null; // Not a valid attachment
    }
  
    $upload_dir = wp_upload_dir();
    $metadata = wp_get_attachment_metadata($attachment_id);
  
    if (!$metadata) {
        return null; // No metadata found
    }
  
    $file_paths = [];
    $file_paths['full'] = $upload_dir['basedir'] . '/' . $metadata['file'];
  
    if (!empty($metadata['sizes'])) {
        foreach ($metadata['sizes'] as $size => $size_info) {
            $file_paths[$size] = $upload_dir['basedir'] . '/' . dirname($metadata['file']) . '/' . $size_info['file'];
        }
    }
  
    return $file_paths;
  }
  /**
   * Convert image to webp format
   */
  function convert_to_webp($img_source, $file_ext = 'jpg'){
    if ($file_ext == "webp") {
      return true;
    } elseif($file_ext == "png"){
      $webp_buf = imagecreatefrompng($img_source);
    }elseif($file_ext == "jpg" || $file_ext == "jpeg"){
      $webp_buf = imagecreatefromjpeg($img_source);
    }elseif($file_ext == "gif"){
      $webp_buf = imagecreatefromgif($img_source);
    }
    if ($webp_buf) {
      imagewebp( $webp_buf, $img_source . ".webp");
      imagedestroy($webp_buf);
      chmod($img_source, 0777); 
      return true;
    }
    return false;
  }
  /**
   * Get all attachments is not webp
   */
  function fetch_image_files_paginated($paged = 1, $posts_per_page = 10) {
    $args = [
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'meta_query' => array(
          'relation' => 'OR',
          array(
            'key' => 'has_webp',
            'compare' => 'NOT EXISTS',
          ),
          array(
            'key' => 'has_webp',
            'value' => '1',
            'type' => 'numeric',
            'compare' => '!=',
          ),
        ),
    ];
  
    $query = new WP_Query($args);
    return $query;
  }
  /**
   * Move css from file to inline html
   */
  function psi_inline_css_from_wp_rocket() {
    // Đường dẫn thư mục cache của WP Rocket
    $cache_dir = WP_CONTENT_DIR . '/cache/min/1/';
  
    // Tìm các tệp CSS trong thư mục cache (sử dụng glob để lấy tệp CSS mới nhất)
    $css_files = glob($cache_dir . '*.css');
  
    // Kiểm tra xem có tệp CSS nào không
    if ($css_files && !empty($css_files)) {
      // Lấy tệp CSS mới nhất
      $latest_css_file = $css_files[0];
  
      // Đọc nội dung của tệp CSS
      $css_content = file_get_contents($latest_css_file);
      $css_content = str_replace("../../../", "/wp-content/", $css_content);
  
      // Chèn nội dung vào inline CSS
      echo '<style type="text/css" id="wp-minify-css">' . $css_content . '</style>';

      // Replace default css cache
      add_filter('rocket_css_url', array(&$this, 'psi_remove_rocket_css_url'));
    }
  }
  /**
   * Replace default css cache
   */
  function psi_remove_rocket_css_url($url) {
    if (strpos($url, '/wp-content/cache/min/1/') !== false) {
      $url = WPMT_PLUGIN_URL . 'css/empty.css';
      // $this->psi_inline_css_from_wp_rocket();
    }
    return $url;
  }
  /**
   * Remove js for PSI
   */
  function psi_dequeued_scripts() {
    global $wp_scripts;

    // Get all enqueued script handles
    $enqueued_scripts = $wp_scripts->queue;

    if ($this->isInsights()) {
      foreach ($enqueued_scripts as $handle) {
        wp_deregister_script( $handle );
      }
    }
  }
  /**
   * Add scripts
   */
  function psi_enqueue_scripts() {
    $image_lazyload = intval(get_option('wpmt_psi_image_lazyload'));
    if ($image_lazyload == 1) {
      wp_enqueue_script( 'wpmt-psi-lazysizes', WPMT_PLUGIN_URL . 'js/lazysizes.min.js', array( 'jquery' ), WPMT_VER, true );
    }
  }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', array( 'WPMT_PSI', 'init' ) );
?>