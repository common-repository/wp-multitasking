<?php
// This file is not called from WordPress. We don't like that.
if (!function_exists('add_filter')) {
    echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
    exit;
}
if(class_exists( 'Add_Quicktag' )){
    add_action('admin_menu', 'wpmt_addquicktag_menu');
}

function wpmt_addquicktag_menu(){
    add_submenu_page(WPMT_MENU_ID, //Menu ID – Defines the unique id of the menu that we want to link our submenu to. 
                                    //To link our submenu to a custom post type page we must specify - 
                                    //edit.php?post_type=my_post_type
            __('Add Quick Tag Settings'), // Page title
            __('Add Quick Tag'), // Menu title
            'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
            'wpmt_addquicktag', // Submenu ID – Unique id of the submenu.
            'wpmt_addquicktag_menu_page' // render output function
        );
    /*-------------------------------------------------------------------------*/
    # Update data
    /*-------------------------------------------------------------------------*/
    if (isset($_GET['page']) and $_GET['page'] == 'wpmt_addquicktag') {
        if (isset($_POST['action']) and 'save' == $_POST['action']) {
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'security_nonce')) {
                if(isset($_POST["wpmt_cpt_addquicktag"]) && !empty($_POST["wpmt_cpt_addquicktag"])){
                    update_option("wpmt_cpt_addquicktag", json_encode($_POST["wpmt_cpt_addquicktag"]));
                }else{
                    delete_option("wpmt_cpt_addquicktag");
                }
                header("Location: {$_SERVER['REQUEST_URI']}&saved=true");
                die();
            }
        } 
    }
}
function wpmt_addquicktag_menu_page(){
?>
    <div class="wrap">
        <h2>Include Post type for addQuickTag settings</h2>
        <?php
        if (isset($_REQUEST['saved']))
            echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
        ?>
        <form method="post" action="">
            <div>
                <?php
                $post_types = get_post_types();
                $exclude_types = array('post', 'page', 'attachment');
                foreach ($post_types as $post_type) {
                    if(in_array($post_type, $exclude_types)){
                        echo <<<HTML
<div>
    <input type="checkbox" name="wpmt_cpt_addquicktag[]" value="{$post_type}" id="{$post_type}" checked="checked" disabled="disabled" />
    <label for="{$post_type}">{$post_type}</label>
</div>
HTML;
                    }elseif(in_array($post_type, wpmt_get_cpt_addquicktag())){
                        echo <<<HTML
<div>
    <input type="checkbox" name="wpmt_cpt_addquicktag[]" value="{$post_type}" id="{$post_type}" checked="checked" />
    <label for="{$post_type}">{$post_type}</label>
</div>
HTML;
                    }else if (is_post_type_viewable($post_type)){
                        echo <<<HTML
<div>
    <input type="checkbox" name="wpmt_cpt_addquicktag[]" value="{$post_type}" id="{$post_type}" />
    <label for="{$post_type}">{$post_type}</label>
</div>
HTML;
                    }
                }
                ?>
            </div>
            <p class="submit">
                <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
                <input type="hidden" name="action" value="save" />
                <?php wp_nonce_field('security_nonce'); ?>
            </p>
        </form>
    </div>
    <?php
}
function wpmt_get_cpt_addquicktag(){
    $post_types = array();
    if(get_option("wpmt_cpt_addquicktag")){
        $post_types = json_decode(get_option("wpmt_cpt_addquicktag"));
    }
    return $post_types;
}
    
if (!function_exists('wpmt_addquicktag_post_types') and class_exists( 'Add_Quicktag' )) {
    // add custom function to filter hook 'addquicktag_post_types'
    add_filter('addquicktag_post_types', 'wpmt_addquicktag_post_types');
    /**
     * Return array $post_types with custom post types strings
     * 
     * @param   $post_type Array
     * @return  $post_type Array
     */
    function wpmt_addquicktag_post_types($post_types) {
        $cpt_post_types = wpmt_get_cpt_addquicktag();
        foreach ($cpt_post_types as $post_type) {
            $post_types[] = $post_type;
        }
        return $post_types;
    }
}