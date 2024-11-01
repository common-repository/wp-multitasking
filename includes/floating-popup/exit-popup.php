<?php
$wpmt_exit_fields = array(
    'exit_popup_status', 'exit_popup_home', 'default_exit_init', 'default_exit_pop_days', 
    'default_exit_width', 'default_exit_height', 'default_exit_speed',
    "default_exit_embed_content", "default_exit_content_popup", "default_exit_content_url", 
    "default_exit_custom_content", 'default_exit_overlayClose',
);
$exit_meta_box = array(
    'id' => 'exit-meta-box',
    'title' => 'Exit pop-up',
    'page' => array('page', 'post'),
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Tình trạng',
            'desc' => '',
            'id' => 'exit_active',
            'type' => 'radio',
            'std' => '',
            'options' => array(
                '0' => 'Không kích hoạt',
                '1' => 'Kích hoạt',
            )
        ),
        array(
            'name' => 'Thiết lập',
            'desc' => '',
            'id' => 'exit_using_settings',
            'type' => 'radio',
            'std' => '',
            'options' => array(
                '1' => 'Sử dụng thiết lập chung',
                '0' => 'Tùy chỉnh',
            )
        ),
        array(
            'name' => 'Hiển thị sau số ngày',
            'desc' => 'Sau bao nhiêu ngày thì hiển thị',
            'id' => 'exit_pop_days',
            'type' => 'text',
            'std' => '',
        ),
        array(
            'name' => 'Tùy chọn nội dung',
            'desc' => '',
            'id' => 'exit_embed_content',
            'type' => 'radio',
            'std' => '',
            'options' => array(
                0 => 'Nhúng từ một nội dung',
                1 => 'Nhúng từ một URL',
                2 => 'Nội dung tùy chỉnh',
            )
        ),
        array(
            'name' => 'Nội dung có sẵn',
            'desc' => '',
            'id' => 'exit_content_popup',
            'type' => 'select',
            'std' => '',
            'options' => array(
            )
        ),
        array(
            'name' => 'Nội dung từ URL',
            'desc' => '',
            'id' => 'exit_content_url',
            'type' => 'text',
            'std' => '',
        ),
        array(
            'name' => 'Nội dung tùy soạn',
            'desc' => '',
            'id' => 'exit_custom_content',
            'type' => 'textarea',
            'std' => '',
        ),
        array(
            'name' => 'Chiều rộng',
            'desc' => '',
            'id' => 'exit_width',
            'type' => 'text',
            'std' => '',
        ),
        array(
            'name' => 'Chiều cao',
            'desc' => '',
            'id' => 'exit_height',
            'type' => 'text',
            'std' => '',
        ),
        array(
            'name' => 'Tốc độ pop-up',
            'desc' => '',
            'id' => 'exit_speed',
            'type' => 'text',
            'std' => '',
        ),
        array(
            'name' => 'Đóng pop-up khi click vào nền',
            'desc' => '',
            'id' => 'exit_overlayClose',
            'type' => 'radio',
            'std' => '',
            'options' => array(
                1 => 'Có',
                0 => 'Không'
            )
        ),
));

add_action('plugins_loaded', 'wpmt_exit_popup_init');
add_action('admin_menu', 'wpmt_add_exit_popup_menu');

function wpmt_exit_popup_init(){
    // init default settings
    if(intval(get_option('default_exit_init')) == 0){
        update_option('exit_popup_status', 0);
        update_option('default_exit_pop_days', 30);
        update_option('default_exit_embed_content', 2);
        update_option('default_exit_content_popup', 2);
        update_option('default_exit_content_url', '');
        update_option('default_exit_custom_content', '');
        update_option('default_exit_width', 500);
        update_option('default_exit_height', 400);
        update_option('default_exit_speed', 350);
        update_option('default_exit_overlayClose', 1);
    }
    if(intval(get_option('exit_popup_status')) == 1){
        // show/hide meta box
        add_action('admin_menu', 'exit_add_box');
        add_action('save_post', 'exit_add_box');
        add_action('save_post', 'exit_save_data');
        
        // fontend init
        add_action( 'init', 'wpmt_exit_popup_frontend_init' );
    }
}
function wpmt_add_exit_popup_menu(){
    global $wpmt_exit_fields;
    
    add_submenu_page(WPMT_MENU_ID, //Menu ID – Defines the unique id of the menu that we want to link our submenu to. 
                                    //To link our submenu to a custom post type page we must specify - 
                                    //edit.php?post_type=my_post_type
            __('Exit Popup'), // Page title
            __('Exit Popup'), // Menu title
            'manage_options', // Capability - see: http://codex.wordpress.org/Roles_and_Capabilities#Capabilities
            'wpmt_exit_popup', // Submenu ID – Unique id of the submenu.
            'wpmt_exit_popup_page' // render output function
        );
    
    if (isset($_GET['page']) and $_GET['page'] == 'wpmt_exit_popup') {
        if (isset($_REQUEST['action']) and 'save' == $_REQUEST['action']) {
          if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'security_nonce')) {
            foreach ($wpmt_exit_fields as $field) {
                if (isset($_REQUEST[$field])) {
                    update_option($field, $_REQUEST[$field]);
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
function wpmt_exit_popup_page(){
?>
    <style type="text/css">
        .wp_themeSkin iframe{background: #FFFFFF;}
        .exit-popup p{margin: 0 0 1em;}
    </style>
    <div class="wrap">
        <div class="opwrap" style="margin-top: 10px;" >
            <div class="icon32" id="icon-options-general"></div>
            <h2 class="wraphead">Exit Popup Settings</h2>
            <?php
            if (isset($_REQUEST['saved']))
                echo '<div id="message" class="updated fade"><p><strong>Exit popup settings saved.</strong></p></div>';
            ?>
            <form method="post">
                <?php wp_nonce_field('security_nonce'); ?>
                <input type="hidden" name="default_exit_init" value="1" />
                <table class="exit-popup form-table">
                    <tr>
                        <td>
                            <b><label for="exit_popup_status_1">Exit pop-up</label></b>
                        </td>
                        <td>
                            <input type="radio" name="exit_popup_status" id="exit_popup_status_1" value="1" 
                                <?php echo (intval(get_option('exit_popup_status')) == 1) ? "checked" : ""; ?> />
                            <label for="exit_popup_status_1">Enable</label>&nbsp;
                            <input type="radio" name="exit_popup_status" id="exit_popup_status_0" value="0" 
                                <?php echo (intval(get_option('exit_popup_status')) != 1) ? "checked" : ""; ?> />
                            <label for="exit_popup_status_0">Disable</label>
                        <td>
                    </tr>
                    <tr>
                        <td>
                            <b><label for="exit_popup_home_1">Pop-up at home page</label></b>
                        </td>
                        <td>
                            <input type="radio" name="exit_popup_home" id="exit_popup_home_1" value="1" 
                                <?php echo (intval(get_option('exit_popup_home')) == 1) ? "checked" : ""; ?> />
                            <label for="exit_popup_home_1">Enable</label>&nbsp;
                            <input type="radio" name="exit_popup_home" id="exit_popup_home_0" value="0" 
                                <?php echo (intval(get_option('exit_popup_home')) != 1) ? "checked" : ""; ?> />
                            <label for="exit_popup_home_0">Disable</label>
                        <td>
                    </tr>
                    <tr>
                        <td valign="top"><b>Display pop-up</b></td>
                        <td>
                            Per <input type="text" name="default_exit_pop_days" 
                                       value="<?php echo intval(get_option('default_exit_pop_days')); ?>" size="3" /> day
                        <td>
                    </tr>
                    <tr>
                        <td valign="top"><b>Content pop-up</b></td>
                        <td>
                            <?php $default_exit_embed_content = get_option('default_exit_embed_content'); ?>
                            <p>
                                <input type="radio" name="default_exit_embed_content" id="embed_content_0" value="0" 
                                    <?php echo ($default_exit_embed_content == 0) ? "checked" : ""; ?> /> 
                                <label for="embed_content_0">Embed page:</label><br />
                                <select name="default_exit_content_popup" class="wpmt-chosen-select">
                                    <?php 
                                    $default_exit_content_popup = get_option('default_exit_content_popup');
                                    query_posts(array(
                                        'post_type' => 'page',
                                        'posts_per_page' => -1,
                                    ));
                                    while(have_posts()) : the_post();
                                        if($default_exit_content_popup == get_the_ID())
                                            echo '<option value="' . get_the_ID() . '" selected>' . get_the_title() . '</option>';
                                        else
                                            echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                                    endwhile;
                                    wp_reset_query();
                                    ?>
                                </select>
                            </p>
                            <p>
                                <input type="radio" name="default_exit_embed_content" id="embed_content_1" value="1" 
                                    <?php echo ($default_exit_embed_content == 1) ? "checked" : ""; ?> /> 
                                <label for="embed_content_1">Embed URL:</label><br />
                                <input type="text" name="default_exit_content_url" value="<?php echo get_option('default_exit_content_url'); ?>" style="width: 100%;" />
                            </p>
                            <p>
                                <input type="radio" name="default_exit_embed_content" id="embed_content_2" value="2" 
                                    <?php echo ($default_exit_embed_content == 2) ? "checked" : ""; ?> /> 
                                <label for="embed_content_2">Use content below:</label>
                            </p>
                            <?php
                                $custom_content = stripslashes(get_option('default_exit_custom_content'));
                                wp_editor($custom_content, 'default_exit_custom_content', array(
                                    'textarea_name' => 'default_exit_custom_content',
                                ));
                            ?>
                        <td>
                    </tr>
                    <tr>
                        <td><b><label for="default_exit_width">Width</label></b></td>
                        <td><input type="text" name="default_exit_width" id="default_exit_width" 
                                   value="<?php echo get_option('default_exit_width'); ?>" size="8" />px<td>
                    </tr>
                    <tr>
                        <td><b><label for="default_exit_height">Height</label></b></td>
                        <td><input type="text" name="default_exit_height" id="default_exit_height" 
                                   value="<?php echo get_option('default_exit_height'); ?>" size="8" />px<td>
                    </tr>
                    <tr>
                        <td><b><label for="default_exit_speed">Speed</label></b></td>
                        <td><input type="text" name="default_exit_speed" id="default_exit_speed" 
                                   value="<?php echo get_option('default_exit_speed'); ?>" size="8" />ms<td>
                    </tr>
                    <tr>
                        <td>
                            <b><label for="exit_overlayClose_1">Overlay Close</label></b>
                        </td>
                        <td>
                            <input type="radio" name="default_exit_overlayClose" id="exit_overlayClose_1" value="1" 
                                <?php echo (intval(get_option('default_exit_overlayClose')) == 1) ? "checked" : ""; ?> />
                            <label for="exit_overlayClose_1">Yes</label>&nbsp;
                            <input type="radio" name="default_exit_overlayClose" id="exit_overlayClose_0" value="0" 
                                <?php echo (intval(get_option('default_exit_overlayClose')) != 1) ? "checked" : ""; ?> />
                            <label for="exit_overlayClose_0">No</label>
                        <td>
                    </tr>
                </table>
                <div class="submit">
                    <input name="save" type="submit" value="Save changes" class="button button-large button-primary" />
                    <input type="hidden" name="action" value="save" />
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">/* <![CDATA[ */
        jQuery(function($){
            $(".submit input[type='submit']").click(function(){
                tinyMCE.triggerSave();
            });
        });
        /* ]]> */
    </script>
<?php
}
###############################################################################
function exit_add_box(){
    global $exit_meta_box;
    foreach ($exit_meta_box['page'] as $page) {
        add_meta_box($exit_meta_box['id'], $exit_meta_box['title'], 'exit_show_box', $page, $exit_meta_box['context'], $exit_meta_box['priority']);
    }
}
function exit_show_box() {
    global $post;
    $post_id = $post->ID;
    $post_tmp = $post;
    echo '<input type="hidden" name="exit_secure_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
?>
    <style type="text/css">
        .wp_themeSkin iframe{background: #FFFFFF;}
        .exit-popup {width: 100%;}
        .exit-popup p{margin: 0 0 1em;}
    </style>
    <table class="exit-popup">
        <tr>
            <td><b>Status</b></td>
            <td>
                <?php $exit_active = intval(get_post_meta($post->ID, 'exit_active', true)); ?>
                <input type="radio" name="exit_active" id="exit_active_0" value="0" 
                    <?php echo ($exit_active != 1) ? "checked" : ""; ?> />
                <label for="exit_active_0">Inactive</label>&nbsp;
                <input type="radio" name="exit_active" id="exit_active_1" value="1" 
                    <?php echo ($exit_active == 1) ? "checked" : ""; ?> />
                <label for="exit_active_1">Active</label>
            <td>
        </tr>
        <tr>
            <td><b>Settings</b></td>
            <td>
                <?php 
                $exit_using_settings = intval(get_post_meta($post->ID, 'exit_using_settings', true)); ?>
                <input type="radio" name="exit_using_settings" id="exit_using_settings_0" value="0" 
                    <?php echo ($exit_using_settings != 1) ? "checked" : ""; ?> />
                <label for="exit_using_settings_0">Use general settings</label>&nbsp;
                <input type="radio" name="exit_using_settings" id="exit_using_settings_1" value="1" 
                    <?php echo ($exit_using_settings == 1) ? "checked" : ""; ?> />
                <label for="exit_using_settings_1">Custom settings</label>
            <td>
        </tr>
    </table>
    <table id="exit_tbl_custom_settings" class="exit-popup" style="width: 100%; <?php echo ($exit_using_settings != 1) ? "display: none;" : ""; ?>">
        <tr>
            <td valign="top"><b>Display pop-up</b></td>
            <td>
                <?php $exit_pop_days = intval(get_post_meta($post->ID, 'exit_pop_days', true)); ?>
                Per <input type="text" name="exit_pop_days" 
                           value="<?php echo ($exit_pop_days == 0) ? get_option('default_exit_pop_days') : $exit_pop_days; ?>" size="3" /> day
            <td>
        </tr>
        <tr>
            <td valign="top"><b>Content pop-up</b></td>
            <td>
                <?php 
                $exit_embed_content = get_post_meta($post->ID, 'exit_embed_content', true); 
                if($exit_embed_content == "") {
                    $exit_embed_content = get_option('default_exit_embed_content');
                }
                ?>
                <p>
                    <input type="radio" name="exit_embed_content" id="embed_content_0" value="0" 
                        <?php echo ($exit_embed_content == 0) ? "checked" : ""; ?> /> 
                    <label for="embed_content_0">Embed page:</label><br />
                    <select name="exit_content_popup" class="wpmt-chosen-select">
                        <?php 
                        $exit_content_popup = get_post_meta($post_id, 'exit_content_popup', true);
                        query_posts(array(
                            'post_type' => 'page',
                            'posts_per_page' => -1,
                        ));
                        while(have_posts()) : the_post();
                            if($exit_content_popup == get_the_ID())
                                echo '<option value="' . get_the_ID() . '" selected>' . get_the_title() . '</option>';
                            else
                                echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                        endwhile;
                        wp_reset_query();
                        ?>
                    </select>
                </p>
                <p>
                    <input type="radio" name="exit_embed_content" id="embed_content_1" value="1" 
                        <?php echo ($exit_embed_content == 1) ? "checked" : ""; ?> /> 
                    <label for="embed_content_1">Embed URL:</label><br />
                    <input type="text" name="exit_content_url" 
                           value="<?php echo get_post_meta($post_id, 'exit_content_url', true);  ?>" style="width: 100%;" />
                </p>
                <p>
                    <input type="radio" name="exit_embed_content" id="embed_content_2" value="2" 
                        <?php echo ($exit_embed_content == 2) ? "checked" : ""; ?> /> 
                    <label for="embed_content_2">Use content below:</label>
                </p>
                <?php
                    $exit_custom_content = get_post_meta($post_id, 'exit_custom_content', true);
                    wp_editor($exit_custom_content, 'exit_custom_content', array(
                        'textarea_name' => 'exit_custom_content',
                        'tinymce' => array(
                            "textarea_rows" => 5
                        )
                    ));
                ?>
            <td>
        </tr>
        <tr>
            <td><b><label for="exit_width">Width</label></b></td>
            <td>
                <?php $exit_width = get_post_meta($post_id, 'exit_width', true);  ?>
                <input type="text" name="exit_width" id="exit_width" 
                       value="<?php echo ($exit_width == "") ? get_option('default_exit_width') : $exit_width; ?>" size="8" />px
            <td>
        </tr>
        <tr>
            <td><b><label for="exit_height">Height</label></b></td>
            <td>
                <?php $exit_height = get_post_meta($post_id, 'exit_height', true);  ?>
                <input type="text" name="exit_height" id="exit_height" 
                       value="<?php echo ($exit_height == "") ? get_option('default_exit_height') : $exit_height; ?>" size="8" />px
            <td>
        </tr>
        <tr>
            <td><b><label for="exit_speed">Speed</label></b></td>
            <td>
                <?php $exit_speed = get_post_meta($post_id, 'exit_speed', true);  ?>
                <input type="text" name="exit_speed" id="exit_speed" 
                       value="<?php echo ($exit_speed == "") ? get_option('default_exit_speed') : $exit_speed; ?>" size="8" />ms
            <td>
        </tr>
        <tr>
            <td><b>Overlay Close</b></td>
            <td>
                <?php 
                $exit_overlayClose = get_post_meta($post_id, 'exit_overlayClose', true); 
                if($exit_overlayClose == ""){
                    $exit_overlayClose = intval(get_option('default_exit_overlayClose'));
                }
                ?>
                <input type="radio" name="exit_overlayClose" id="exit_overlayClose_1" value="1" 
                    <?php echo ($exit_overlayClose == 1) ? "checked" : ""; ?> />
                <label for="exit_overlayClose_1">Yes</label>&nbsp;
                <input type="radio" name="exit_overlayClose" id="exit_overlayClose_0" value="0" 
                    <?php echo ($exit_overlayClose != 1) ? "checked" : ""; ?> />
                <label for="exit_overlayClose_0">No</label>
            <td>
        </tr>
    </table>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $("input[name='exit_using_settings']").change(function(){
                if($(this).val() == 1){
                    $("#exit_tbl_custom_settings").show();
                }else{
                    $("#exit_tbl_custom_settings").hide();
                }
            });
            $("#publish").click(function(){
                tinyMCE.triggerSave();
            });
        });
    </script>
<?php
    $post = $post_tmp;
}
function exit_save_data($post_id) {
    global $exit_meta_box;
    // verify nonce
    if (!wp_verify_nonce($_POST['exit_secure_meta_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    foreach ($exit_meta_box['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];
        if (isset($_POST[$field['id']]) && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}
###############################################################################
function wpmt_exit_popup_frontend_init(){
    add_action('wp_head', 'wpmt_exit_add_vars');
    add_action( 'wp_ajax_nopriv_' . $_REQUEST['action'], $_REQUEST['action'] );  
    add_action( 'wp_ajax_' . $_REQUEST['action'], $_REQUEST['action'] ); 
}
function wpmt_exit_add_vars(){
    $popup_type = get_option('wpmt_popup_type');
    $popup_type = ($popup_type != 'fancybox') ? 'colorbox' : $popup_type;
    $exit_popup_home = intval(get_option('exit_popup_home'));
    $exit_pop_days = intval(get_option('default_exit_pop_days'));
    $exit_embed_content = get_option('default_exit_embed_content'); 
    $exit_content = get_option('default_exit_content_popup');
    if($exit_embed_content == 1){
        $exit_content = get_option('default_exit_content_url');
    }elseif($exit_embed_content == 2){
        $exit_content = 0;
    }
    $exit_width = get_option('default_exit_width');
    $exit_height = get_option('default_exit_height');
    $exit_speed = get_option('default_exit_speed');
    $exit_overlayClose = intval(get_option('default_exit_overlayClose')); 
    $ajaxUrl = get_bloginfo('siteurl') . '/wp-admin/admin-ajax.php';
                
    if(is_single() or is_page()){
        $post_id = get_the_ID();
        if(get_post_meta($post_id, 'exit_active', true) == 1){
            $exit_popup_cookieName = "exit_popup_" . $post_id;
            
            if(get_post_meta($post_id, 'exit_using_settings', true) == 1){
                $exit_pop_days = intval(get_post_meta($post_id, 'exit_pop_days', true));
                $exit_embed_content = get_post_meta($post_id, 'exit_embed_content', true); 
                $exit_content = get_post_meta($post_id, 'exit_content_popup', true);
                if($exit_embed_content == 1){
                    $exit_content = get_post_meta($post_id, 'exit_content_url', true);
                }elseif($exit_embed_content == 2){
                    $exit_content = $post_id;
                }
                $exit_width = get_post_meta($post_id, 'exit_width', true);
                $exit_height = get_post_meta($post_id, 'exit_height', true);
                $exit_speed = get_post_meta($post_id, 'exit_speed', true); 
                $exit_overlayClose = get_post_meta($post_id, 'exit_overlayClose', true);
            }
            
            echo <<<HTML
<script type="text/javascript">
    var exit_popup_type = '$popup_type';
    var exit_popup_cookieName = '$exit_popup_cookieName';
    var exit_popup_days = $exit_pop_days;
    var exit_popup_embed = $exit_embed_content;
    var exit_popup_content = '$exit_content';
    var exit_popup_width = $exit_width;
    var exit_popup_height = $exit_height;
    var exit_popup_speed = $exit_speed;
    var exit_popup_overlayClose = $exit_overlayClose;
    var exit_popup_ajaxUrl = '$ajaxUrl';
</script>
HTML;
        }
    } else if (is_home() or is_front_page()){
        if($exit_popup_home == 1){
            $exit_popup_cookieName = "exit_popup_home";
            echo <<<HTML
<script type="text/javascript">
    var exit_popup_type = '$popup_type';
    var exit_popup_cookieName = '$exit_popup_cookieName';
    var exit_popup_days = $exit_pop_days;
    var exit_popup_embed = $exit_embed_content;
    var exit_popup_content = '$exit_content';
    var exit_popup_width = $exit_width;
    var exit_popup_height = $exit_height;
    var exit_popup_speed = $exit_speed;
    var exit_popup_overlayClose = $exit_overlayClose;
    var exit_popup_ajaxUrl = '$ajaxUrl';
</script>
HTML;
        }
    }
}
function wpmt_get_exit_popup_content(){
    $post_id = intval($_REQUEST['post_id']);
    $post = get_post($post_id);
    echo stripslashes($post->post_content);
    exit();
}
function wpmt_get_exit_popup_custom_content(){
    $post_id = intval($_REQUEST['post_id']);
    if($post_id > 0){
        echo stripslashes(get_post_meta($post_id, 'exit_custom_content', true));
    }else{
        echo stripslashes(get_option('default_exit_custom_content'));
    }
    exit();
}