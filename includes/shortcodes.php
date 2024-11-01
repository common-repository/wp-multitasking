<?php
/**
 * Shortcodes
 */
class WPMT_Shortcodes {
  static $_s = null;
  
  public function __construct() {
    add_action('admin_menu', array(&$this, 'menu'));
    add_action('admin_print_footer_scripts', array(&$this, 'admin_shortcodes_js'));

    add_shortcode('currentyear', array(&$this, 'shortcode_currentyear'));
    add_shortcode('currentmonth', array(&$this, 'shortcode_currentmonth'));

    add_filter('the_title', array(&$this, 'custom_the_title_output'));
    add_filter('get_the_title', array(&$this, 'custom_get_the_title'));
    add_filter('the_excerpt', array(&$this, 'custom_excerpt_shortcode'));
    add_filter('get_the_excerpt', array(&$this, 'custom_get_excerpt_shortcode'));
    add_filter('rank_math/frontend/title', array(&$this, 'custom_seo_title'));
    add_filter('rank_math/frontend/description', array(&$this, 'custom_seo_description'));
    add_filter('wpseo_title', array(&$this, 'custom_seo_title') );
    add_filter('wpseo_twitter_title', array(&$this, 'custom_seo_title') );
    add_filter('wpseo_opengraph_title', array(&$this, 'custom_seo_title') );
    add_filter('wpseo_metadesc', array(&$this, 'custom_seo_description') );
    add_filter('wpseo_twitter_description', array(&$this, 'custom_seo_description') );
    add_filter('wpseo_opengraph_desc', array(&$this, 'custom_seo_description') );
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
        __('Shortcodes'), // Page title
        __('Shortcodes'), // Menu title
        'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
        'wpmt_shortcodes', // Submenu ID – Unique id of the submenu.
        array(&$this, 'menu_page') // render output function
    );
  }

  public function menu_page() {
      if (!current_user_can('manage_options')) {
          wp_die(__('You do not have sufficient permissions to access this page.'));
      }
?>
  <div class="wrap">
    <h2>Shortcodes</h2>
    <table class="form-table" style="max-width:800px">
        <tr>
            <td>
                <label for="wpmt_shortcode_currentyear">Current Year:</label>
            </td>
            <td>
                <input type="text" id="wpmt_shortcode_currentyear" value="[currentyear]" data-value="[currentyear]" class="regular-text" />
            </td>
            <td>
              Example: <?php echo date('Y') ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="wpmt_shortcode_currentmonth">Current Month:</label>
            </td>
            <td>
                <input type="text" id="wpmt_shortcode_currentmonth" value="[currentmonth]" data-value="[currentmonth]" class="regular-text" />
            </td>
            <td>
              Example: <?php echo date('m') ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="wpmt_shortcode_currentmonth_text">Current Month with Format:</label>
            </td>
            <td>
                <input type="text" id="wpmt_shortcode_currentmonth_text" value='[currentmonth format="F"]' data-value='[currentmonth format="F"]' class="regular-text" />
            </td>
            <td>
              Example: <?php echo date('F') ?>. <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">Date format</a>
            </td>
        </tr>
    </table>
  </div>
  <?php
  }
  /* ----------------------------------------------------------------------------------- */
  # Custom post title output
  /* ----------------------------------------------------------------------------------- */
  function custom_the_title_output($title) {
    $title = do_shortcode($title);
    return $title;
  }

  function custom_get_the_title($title) {
    $title = do_shortcode($title);
    return $title;
  }
  /* ----------------------------------------------------------------------------------- */
  # Custom excerpt output
  /* ----------------------------------------------------------------------------------- */
  function custom_excerpt_shortcode($excerpt) {
    // global $post;
    // $excerpt = $post->post_excerpt;
    if (!empty($excerpt)) {
      $excerpt = do_shortcode($excerpt);
    }
    $excerpt = strip_tags($excerpt);
    return $excerpt;
  }
  function custom_get_excerpt_shortcode($excerpt, $post = null) {
    if ($post) {
      $excerpt = $post->post_excerpt;
    }
    if (!empty($excerpt)) {
      $excerpt = do_shortcode($excerpt);
    }
    $excerpt = strip_tags($excerpt);
    return $excerpt;
  }
  /* ----------------------------------------------------------------------------------- */
  # Custom rankmath/yoastseo output
  /* ----------------------------------------------------------------------------------- */
  function custom_seo_title( $title ) {
    return do_shortcode($title);
  }
  function custom_seo_description( $description ) {
    return do_shortcode($description);
  }
  /* ----------------------------------------------------------------------------------- */
  # SHORTCODES
  /* ----------------------------------------------------------------------------------- */
  function shortcode_currentyear($atts, $content = null) {
    return date('Y');
  }

  function shortcode_currentmonth($atts, $content = null) {
    $atts = shortcode_atts( array(
      'format' => 'm'
    ), $atts, 'currentmonth' );
    return date($atts['format']);
  }
  /* ----------------------------------------------------------------------------------- */
  # Scripts
  /* ----------------------------------------------------------------------------------- */
  function admin_shortcodes_js(){
  ?>
    <script type="text/javascript">/* <![CDATA[ */
      jQuery(function($){
        $('input[id^=wpmt_shortcode_]').click(function(){
          this.select()
        })
        $('input[id^=wpmt_shortcode_]').change(function(){
          var val = $(this).data('value');
          if ($(this).val() != val) {
            $(this).val(val);
          }
        })
      })
      /* ]]> */
    </script>
  <?php
  }

}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', array( 'WPMT_Shortcodes', 'init' ) );