<?php
/**
 * Insert head, body and footer scripts
 */
class WPMT_HeaderFooterScripts {
  static $_s = null;
  
  public function __construct() {
    add_action('admin_menu', array(&$this, 'menu'));
    add_action('admin_print_footer_scripts', array(&$this, 'admin_hfs_js'));
    add_action('wp_head', array(&$this, 'add_header_code'));
    add_action('wp_body_open', array(&$this, 'add_body_code'));
    add_action('wp_footer', array(&$this, 'add_footer_code'));
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
        __('Header & Footer Scripts'), // Page title
        __('Header & Footer Scripts'), // Menu title
        'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
        'wpmt_headerfooterscripts', // Submenu ID – Unique id of the submenu.
        array(&$this, 'menu_page') // render output function
    );
  }

  public function menu_page() {
      if (!current_user_can('manage_options')) {
          wp_die(__('You do not have sufficient permissions to access this page.'));
      }
      if (isset($_POST['action']) and 'save' == $_POST['action']) {
          if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'security_nonce')) {
              $fields = ['wpmt_insert_header', 'wpmt_insert_body', 'wpmt_insert_footer'];
              foreach ($fields as $field) {
                  if (isset($_REQUEST[$field]) and !empty($_REQUEST[$field])) {
                      update_option($field, $_REQUEST[$field]);
                  } else {
                      delete_option($field);
                  }
              }
          }
      }
      $wpmt_insert_header = stripslashes(get_option('wpmt_insert_header'));
      $wpmt_insert_body = stripslashes(get_option('wpmt_insert_body'));
      $wpmt_insert_footer = stripslashes(get_option('wpmt_insert_footer'));
?>
  <div class="wrap">
      <h1>Insert Header & Footer Scripts</h1>
      <form id="frmWpmtHfs" method="post">
          <div class="wpmt-code-textarea" id="wpmt-global-header">
              <h2>Header</h2>
              <textarea name="wpmt_insert_header" id="wpmt_insert_header" class="widefat" rows="10"><?php echo $wpmt_insert_header ?></textarea>
              <p>These scripts will be printed in the <code>&lt;head&gt;</code> section.</p>
          </div>
          <div class="wpmt-code-textarea" id="wpmt-global-body">
              <h2>Body</h2>
              <textarea name="wpmt_insert_body" id="wpmt_insert_body" class="widefat" rows="10"><?php echo $wpmt_insert_body ?></textarea>
              <p>These scripts will be printed just below the opening <code>&lt;body&gt;</code> tag.</p>
          </div>
          <div class="wpmt-code-textarea" id="wpmt-global-footer">
              <h2>Footer</h2>
              <textarea name="wpmt_insert_footer" id="wpmt_insert_footer" class="widefat" rows="10"><?php echo $wpmt_insert_footer ?></textarea>
              <p>These scripts will be printed above the closing <code>&lt;body&gt;</code> tag.</p>
          </div>
          <div class="submit">
              <input name="save" type="submit" value="Save changes" class="button button-large button-primary" />
              <input type="hidden" name="action" value="save" />
              <?php wp_nonce_field('security_nonce'); ?>
          </div>
      </form>
  </div>
  <?php
  }
  /*----------------------------------------------------------------------------*/
  # Add Header Code
  /*----------------------------------------------------------------------------*/
  function add_header_code(){
      echo stripslashes(get_option('wpmt_insert_header'));
  }
  /*----------------------------------------------------------------------------*/
  # Add Footer Code
  /*----------------------------------------------------------------------------*/
  function add_footer_code(){
      echo stripslashes(get_option('wpmt_insert_footer'));
  }
  /* ----------------------------------------------------------------------------------- */
  # Add body code
  /* ----------------------------------------------------------------------------------- */
  function add_body_code() {
      echo stripslashes(get_option('wpmt_insert_body'));
  }
  /* ----------------------------------------------------------------------------------- */
  # Scripts
  /* ----------------------------------------------------------------------------------- */
  function admin_hfs_js(){
  ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js"></script>
    <script type="text/javascript">/* <![CDATA[ */
      var wpmt_insert_header = document.getElementById("wpmt_insert_header");
      if (wpmt_insert_header) {
        var editorHeader = CodeMirror.fromTextArea(wpmt_insert_header, {
          lineNumbers: true
        })
      }
      var wpmt_insert_body = document.getElementById("wpmt_insert_body");
      if (wpmt_insert_body) {
        var editorBody = CodeMirror.fromTextArea(wpmt_insert_body, {
          lineNumbers: true
        })
      }
      var wpmt_insert_footer = document.getElementById("wpmt_insert_footer");
      if (wpmt_insert_footer) {
        var editorFooter = CodeMirror.fromTextArea(wpmt_insert_footer, {
          lineNumbers: true
        })
      }
      /* ]]> */
    </script>
  <?php
  }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', array( 'WPMT_HeaderFooterScripts', 'init' ) );