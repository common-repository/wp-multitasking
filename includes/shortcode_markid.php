<?php

add_shortcode("markid", "wpmt_markid");
add_action('init', 'wpmt_markid_shortcode_button_init');

function wpmt_markid($atts) {
    extract(shortcode_atts(array(
                "id" => '',
                "title" => ''
            ), $atts ));
    if (!empty($title)) {
        return "<span id=\"".esc_html($id)."\" class=\"mark\">".esc_html($title)."</span>";
    }
    return "<span id=\"".esc_html($id)."\"></span>";
}

function wpmt_markid_shortcode_button_init() {
    //Abort early if the user will never see TinyMCE
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
        return;

    //Add a callback to regiser our tinymce plugin   
    add_filter("mce_external_plugins", "wpmt_markid_register_tinymce_plugin");

    // Add a callback to add our button to the TinyMCE toolbar
    add_filter('mce_buttons', 'wpmt_markid_add_tinymce_button');
}

//This callback registers our plug-in
function wpmt_markid_register_tinymce_plugin($plugin_array) {
    $plugin_array['button'] = WPMT_PLUGIN_URL . 'js/shortcode_markid.js';
    return $plugin_array;
}

//This callback adds our button to the toolbar
function wpmt_markid_add_tinymce_button($buttons) {
    //Add the button ID to the $button array
    $buttons[] = "button";
    return $buttons;
}