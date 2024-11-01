<?php

if(intval(get_option('exit_popup_status')) == 1 or intval(get_option('welcome_popup_status')) == 1){
    add_action('wp_head', 'wpmt_popup_add_styles');
    add_action('wp_footer', 'wpmt_popup_add_scripts');
}

function wpmt_popup_add_styles() {
    $WPMT_PLUGIN_URL = WPMT_PLUGIN_URL;
    $popup_type = get_option('wpmt_popup_type');
    $colorbox_type = intval(get_option('wpmt_colorbox_type'));
    $colorbox_type = (!in_array($colorbox_type, [1,2,3,4,5])) ? 3 : $colorbox_type;

    if (is_single() or is_page()) {
        $post_id = get_the_ID();
        $exit_active = get_post_meta($post_id, 'exit_active', true);
        $welcome_active = get_post_meta($post_id, 'wcome_active', true);
        if ($exit_active == 1 or $welcome_active == 1) {
            if ($popup_type != "fancybox") {
                echo <<<HTML
<link rel="stylesheet" type="text/css" media="screen" href="{$WPMT_PLUGIN_URL}libraries/colorbox/example{$colorbox_type}/colorbox.css?ver=1.6.4" />
HTML;
            } else {
                echo <<<HTML
<link rel="stylesheet" type="text/css" media="screen" href="{$WPMT_PLUGIN_URL}libraries/fancybox/jquery.fancybox.min.css?ver=3.2.10" />
HTML;
            }
        }
    } else if (is_home() or is_front_page()) {
        $exit_popup_home = intval(get_option('exit_popup_home'));
        $welcome_popup_home = intval(get_option('welcome_popup_home'));
        if ($exit_popup_home == 1 or $welcome_popup_home == 1) {
            if ($popup_type != "fancybox") {
                echo <<<HTML
<link rel="stylesheet" type="text/css" media="screen" href="{$WPMT_PLUGIN_URL}libraries/colorbox/example{$colorbox_type}/colorbox.css?ver=1.6.4" />
HTML;
            } else {
                echo <<<HTML
<link rel="stylesheet" type="text/css" media="screen" href="{$WPMT_PLUGIN_URL}libraries/fancybox/jquery.fancybox.min.css?ver=3.2.10" />
HTML;
            }
        }
    }
}

function wpmt_popup_add_scripts(){
    $popup_type = get_option('wpmt_popup_type');
    if(is_single() or is_page()){
        $exit_active = get_post_meta(get_the_ID(), 'exit_active', true);
        if($exit_active == 1){
            if ($popup_type != 'fancybox') {
                wp_enqueue_script('wpmt-colorbox', WPMT_PLUGIN_URL . 'libraries/colorbox/jquery.colorbox-min.js', array('jquery'), '1.6.4', true);
            } else {
                wp_enqueue_script('wpmt-fancybox', WPMT_PLUGIN_URL . 'libraries/fancybox/jquery.fancybox.min.js', array('jquery'), '3.2.10', true);
            }
            wp_enqueue_script('wpmt-common', WPMT_PLUGIN_URL . 'js/common.multitasking.js', array('jquery'), WPMT_VER, true);
            wp_enqueue_script('wpmt-floating-popup', WPMT_PLUGIN_URL . 'js/floating.popup.js', array('jquery'), WPMT_VER, true);
        }
    }elseif (is_home() or is_front_page()) {
        $exit_popup_home = intval(get_option('exit_popup_home'));
        $welcome_popup_home = intval(get_option('welcome_popup_home'));
        if ($exit_popup_home == 1 or $welcome_popup_home == 1) {            
            if ($popup_type != 'fancybox') {
                wp_enqueue_script('wpmt-colorbox', WPMT_PLUGIN_URL . 'libraries/colorbox/jquery.colorbox-min.js', array('jquery'), '1.6.4', true);
            } else {
                wp_enqueue_script('wpmt-fancybox', WPMT_PLUGIN_URL . 'libraries/fancybox/jquery.fancybox.min.js', array('jquery'), '3.2.10', true);
            }
            wp_enqueue_script('wpmt-common', WPMT_PLUGIN_URL . 'js/common.multitasking.js', array('jquery'), WPMT_VER, true);
            wp_enqueue_script('wpmt-floating-popup', WPMT_PLUGIN_URL . 'js/floating.popup.js', array('jquery'), WPMT_VER, true);
        }
    }
}