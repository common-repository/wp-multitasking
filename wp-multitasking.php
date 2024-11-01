<?php 
/***************************************************************************
Plugin Name: WP MultiTasking - WP Utilities
Plugin URI:  http://wordpress.org/plugins/wp-multitasking/
Description: This plugin is synthetic utility for your WordPress site: Shortcode, BBCode, AddQuickTag, Exit pop-up, Welcome pop-up, Remove base slug, SMTP, Classic Editor, Classic widgets...
Version:     0.1.18
Author:      thangnv27
Author URI:  https://ngothang.me/
**************************************************************************/

if ( ! defined( 'WPMT_PLUGIN_DIR' ) )
    define( 'WPMT_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'WPMT_PLUGIN_URL' ) )
    define( 'WPMT_PLUGIN_URL', plugins_url('/', __FILE__) );

if ( ! defined( 'WPMT_MENU_NAME' ) )
    define( 'WPMT_MENU_NAME', 'WP Utilities' );

if ( ! defined( 'WPMT_MENU_ID' ) )
    define( 'WPMT_MENU_ID', 'wpmt_settings' );
    
if ( ! defined( 'WPMT_VER' ) )
    define( 'WPMT_VER', '0.1.17' );

add_action('admin_menu', 'wpmt_add_settings_page');

function wpmt_add_settings_page(){
    $menu_name = esc_html(get_option('wpmt_menu_name'));
    $menu_name = (empty($menu_name)) ? WPMT_MENU_NAME : $menu_name;
    add_menu_page($menu_name, // Page title
        $menu_name, // Menu title
        'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
        WPMT_MENU_ID, // menu id - Unique id of the menu
        'wpmt_display_settings_page',// render output function
        '', // URL icon, if empty default icon
        null // Menu position - integer, if null default last of menu list
    );
    /*-------------------------------------------------------------------------*/
    # Update data
    /*-------------------------------------------------------------------------*/
    if (isset($_GET['page']) and $_GET['page'] == WPMT_MENU_ID) {
        if (isset($_POST['action']) and 'save' == $_POST['action']) {
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'security_nonce')) {
                $fields = ['wpmt_menu_name', 'wpmt_classic_editor', 'wpmt_classic_widgets', 'wpmt_popup_type', 'wpmt_colorbox_type'];
                foreach ($fields as $field) {
                    $fieldVal = sanitize_text_field($_REQUEST[$field]);
                    if (isset($_REQUEST[$field]) and !empty($fieldVal)) {
                        update_option($field, $fieldVal);
                    } else {
                        delete_option($field);
                    }
                }

                header("Location: {$_SERVER['REQUEST_URI']}&saved=true");
                die();
            }
        }
    }
}

function wpmt_display_settings_page() {
    $popup_type = get_option('wpmt_popup_type');
    $colorbox_type = get_option('wpmt_colorbox_type');
    $colorbox_type = (!in_array($colorbox_type, [1,2,3,4,5])) ? 3 : $colorbox_type;
?>
    <div class="wrap">
        <div class="opwrap" style="margin-top: 10px;" >
            <div class="icon32" id="icon-options-general"></div>
            <h2 class="wraphead">
                <?php 
                $wpmt_menu_name = esc_html(get_option('wpmt_menu_name'));
                $menu_name = (empty($wpmt_menu_name)) ? WPMT_MENU_NAME : $wpmt_menu_name;
                echo $menu_name;
                ?>
            </h2>
            <?php
            if (isset($_REQUEST['saved']))
                echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
            ?>
            <form method="post">
                <table class="form-table" width="100%">
                    <tr>
                        <td>
                            <label for="wpmt_menu_name">Menu name:</label>
                        </td>
                        <td>
                            <input type="text" name="wpmt_menu_name" id="wpmt_menu_name" value="<?php echo esc_html(get_option('wpmt_menu_name')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="wpmt_classic_editor">Classic Editor:</label>
                        </td>
                        <td>
                            <input type="checkbox" name="wpmt_classic_editor" id="wpmt_classic_editor" value="1" 
                                <?php echo (get_option('wpmt_classic_editor') == '1') ? "checked" : ""; ?> />
                            <label for="wpmt_classic_editor">Replace the Block Editor with the Classic Editor</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="wpmt_classic_widgets">Classic Widgets:</label>
                        </td>
                        <td>
                            <input type="checkbox" name="wpmt_classic_widgets" id="wpmt_classic_widgets" value="1" 
                                <?php echo (get_option('wpmt_classic_widgets') == '1') ? "checked" : ""; ?> />
                            <label for="wpmt_classic_widgets">Enables the classic widgets settings screens in Appearance - Widgets</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="wpmt_popup_type">Popup type:</label>
                        </td>
                        <td>
                            <input type="radio" name="wpmt_popup_type" id="wpmt_popup_type_colorbox" value="colorbox" 
                                <?php echo ($popup_type !== 'fancybox') ? "checked" : ""; ?> />
                            <label for="wpmt_popup_type_colorbox">Colorbox</label>&nbsp;
                            <input type="radio" name="wpmt_popup_type" id="wpmt_popup_type_fancybox" value="fancybox" 
                                <?php echo ($popup_type === 'fancybox') ? "checked" : ""; ?> />
                            <label for="wpmt_popup_type_fancybox">Fancybox</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="wpmt_colorbox_type">Colorbox style:</label>
                        </td>
                        <td>
                            <select name="wpmt_colorbox_type" id="wpmt_colorbox_type" class="postform">
                                <?php for($i = 1; $i < 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($colorbox_type == $i) ? "selected" : ""; ?>>Style <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select> <em>(Apply for Colorbox type)</em>
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
    </div>
<?php
}
/* ----------------------------------------------------------------------------------- */
# Register main Scripts and Styles
/* ----------------------------------------------------------------------------------- */
add_action('admin_enqueue_scripts', 'wpmt_register_scripts');

function wpmt_register_scripts(){
    wp_enqueue_media();

    ## Get Global Styles
    wp_enqueue_style('wpmt-chosen-template', WPMT_PLUGIN_URL . 'css/chosen.min.css', array(), '1.0.0');

    ## Get Global Scripts
    wp_enqueue_script('wpmt-chosen', WPMT_PLUGIN_URL . 'js/chosen.jquery.min.js', array('jquery'), '1.0.0',  true);
}

add_action('admin_print_footer_scripts', 'wpmt_admin_add_custom_js', 99);

function wpmt_admin_add_custom_js(){
    ?>
    <script type="text/javascript">/* <![CDATA[ */
        jQuery(function($){
            try {
                $("select.wpmt-chosen-select").chosen({width: "40%"});
            } catch(err) {
                console.log(err.message)
            }
        });
        /* ]]> */
    </script>
<?php
}

require_once WPMT_PLUGIN_DIR . '/includes/bbcode.php';
require_once WPMT_PLUGIN_DIR . '/includes/classic-editor.php';
require_once WPMT_PLUGIN_DIR . '/includes/classic-widgets.php';
require_once WPMT_PLUGIN_DIR . '/includes/shortcode_markid.php';
require_once WPMT_PLUGIN_DIR . '/includes/addquicktag_cpt.php';
require_once WPMT_PLUGIN_DIR . '/includes/permalinks.php';
require_once WPMT_PLUGIN_DIR . '/includes/floating-popup/welcome-popup.php';
require_once WPMT_PLUGIN_DIR . '/includes/floating-popup/exit-popup.php';
require_once WPMT_PLUGIN_DIR . '/includes/floating-popup/global-popup.php';
require_once WPMT_PLUGIN_DIR . '/includes/smtp.php';
require_once WPMT_PLUGIN_DIR . '/includes/shortcodes.php';
require_once WPMT_PLUGIN_DIR . '/includes/header-footer-scripts.php';
require_once WPMT_PLUGIN_DIR . '/includes/pagespeed-insights.php';