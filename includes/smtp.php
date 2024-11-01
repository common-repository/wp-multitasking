<?php
/**
 * SMTP settings
 */
class WPMT_SMTP {
  static $_s = null;
  
  public function __construct() {
    add_action('admin_menu', array(&$this, 'menu'));
    add_action('admin_print_footer_scripts', array(&$this, 'admin_smtp_js'));
    add_action('phpmailer_init', array(&$this, 'send_smtp_email'));
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
        __('SMTP Settings'), // Page title
        __('SMTP'), // Menu title
        'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
        'wpmt_smtp', // Submenu ID – Unique id of the submenu.
        array(&$this, 'menu_page') // render output function
    );
  }

  public function menu_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (isset($_POST['action']) and 'save' == $_POST['action']) {
        if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'security_nonce')) {
            $fields = ['wpmt_smtp_from', 'wpmt_smtp_fromname', 'wpmt_smtp_host', 'wpmt_smtp_user', 'wpmt_smtp_passwd', 'wpmt_smtp_port', 'wpmt_smtp_secure'];
            foreach ($fields as $field) {
                if (isset($_REQUEST[$field]) and !empty($_REQUEST[$field])) {
                    update_option($field, $_REQUEST[$field]);
                } else {
                    delete_option($field);
                }
            }
        }
    }
?>
  <div class="wrap">
    <h2>SMTP Settings</h2>
    <?php
    if (isset($_POST['action']) and 'save' == $_POST['action']) {
        echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
    }

    $smtp_from = stripslashes(get_option('wpmt_smtp_from'));
    $smtp_from = (empty($smtp_from)) ? get_option('admin_email') : $smtp_from;
    $smtp_fromname = stripslashes(get_option('wpmt_smtp_fromname'));
    $smtp_fromname = (empty($smtp_fromname)) ? get_option('blogname') : $smtp_fromname;
    $smtp_host = stripslashes(get_option('wpmt_smtp_host'));
    $smtp_host = (empty($smtp_host)) ? 'localhost' : $smtp_host;
    $smtp_secure = get_option('wpmt_smtp_secure');
    $smtp_secure = (empty($smtp_secure)) ? 'none' : $smtp_secure;
    $smtp_port = intval(get_option('wpmt_smtp_port'));
    $smtp_port = (empty($smtp_port)) ? 25 : $smtp_port;
    ?>
    <form id="frmWpmtSmtp" method="post">
        <table class="form-table" width="100%">
            <tr>
                <td>
                    <label for="wpmt_smtp_from">From Email:</label>
                </td>
                <td>
                    <input type="email" name="wpmt_smtp_from" id="wpmt_smtp_from" value="<?php echo $smtp_from; ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpmt_smtp_fromname">From Name:</label>
                </td>
                <td>
                    <input type="text" name="wpmt_smtp_fromname" id="wpmt_smtp_fromname" value="<?php echo $smtp_fromname; ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpmt_smtp_host">SMTP Host:</label>
                </td>
                <td>
                    <input type="text" name="wpmt_smtp_host" id="wpmt_smtp_host" value="<?php echo $smtp_host; ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpmt_smtp_secure">Encryption:</label>
                </td>
                <td>
                    <input type="radio" name="wpmt_smtp_secure" id="wpmt_smtp_secure_none" value="none" 
                        <?php echo ($smtp_secure === 'none') ? "checked" : ""; ?> />
                    <label for="wpmt_smtp_secure_none">None</label>&nbsp;
                    <input type="radio" name="wpmt_smtp_secure" id="wpmt_smtp_secure_ssl" value="ssl" 
                        <?php echo ($smtp_secure === 'ssl') ? "checked" : ""; ?> />
                    <label for="wpmt_smtp_secure_ssl">SSL</label>&nbsp;
                    <input type="radio" name="wpmt_smtp_secure" id="wpmt_smtp_secure_tls" value="tls" 
                        <?php echo ($smtp_secure === 'tls') ? "checked" : ""; ?> />
                    <label for="wpmt_smtp_secure_tls">TLS</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpmt_smtp_port">SMTP Port:</label>
                </td>
                <td>
                    <input type="number" name="wpmt_smtp_port" id="wpmt_smtp_port" value="<?php echo $smtp_port; ?>" class="small-text" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpmt_smtp_user">SMTP Username:</label>
                </td>
                <td>
                    <input type="text" name="wpmt_smtp_user" id="wpmt_smtp_user" value="<?php echo stripslashes(get_option('wpmt_smtp_user')); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpmt_smtp_passwd">SMTP Password:</label>
                </td>
                <td>
                    <input type="password" name="wpmt_smtp_passwd" id="wpmt_smtp_passwd" value="<?php echo get_option('wpmt_smtp_passwd'); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <div class="submit">
            <input name="save" type="submit" value="Save changes" class="button button-large button-primary" />
            <input type="hidden" name="action" value="save" />
            <?php wp_nonce_field('security_nonce'); ?>
        </div>
    </form>
  </div>
  <?php
  }

  function admin_smtp_js(){
  ?>
    <script type="text/javascript">/* <![CDATA[ */
      jQuery(function($){
        $('#frmWpmtSmtp input[name=wpmt_smtp_secure]').click(function(){
          var value = $('#frmWpmtSmtp input[name=wpmt_smtp_secure]:checked').val();
          if (value == 'ssl') {
            $('#wpmt_smtp_port').val(465);
          } else if (value == 'tls') {
            $('#wpmt_smtp_port').val(587);
          } else {
            $('#wpmt_smtp_port').val(25);
          }
        });
      });
      /* ]]> */
    </script>
  <?php
  }

  function send_smtp_email( $phpmailer ) {
    $smtp_from = stripslashes(get_option('wpmt_smtp_from'));
    $smtp_from = (empty($smtp_from)) ? get_option('admin_email') : $smtp_from;
    $smtp_fromname = stripslashes(get_option('wpmt_smtp_fromname'));
    $smtp_fromname = (empty($smtp_fromname)) ? get_option('blogname') : $smtp_fromname;
    $smtp_host = stripslashes(get_option('wpmt_smtp_host'));
    $smtp_host = (empty($smtp_host)) ? 'localhost' : $smtp_host;
    $smtp_port = intval(get_option('wpmt_smtp_port'));
    $smtp_port = (empty($smtp_port)) ? 25 : $smtp_port;
    $smtp_secure = get_option('wpmt_smtp_secure');
    $smtp_user = stripslashes(get_option('wpmt_smtp_user'));
    $smtp_passwd = get_option('wpmt_smtp_passwd');

    $phpmailer->isSMTP();
    $phpmailer->From       = $smtp_from;
    $phpmailer->FromName   = $smtp_fromname;
    $phpmailer->Host       = $smtp_host;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = $smtp_port;
    $phpmailer->Username   = $smtp_user;
    $phpmailer->Password   = $smtp_passwd;
    if (in_array($smtp_secure, ['ssl', 'tls'])) {
      $phpmailer->SMTPSecure = $smtp_secure;
    }
  }

}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', array( 'WPMT_SMTP', 'init' ) );