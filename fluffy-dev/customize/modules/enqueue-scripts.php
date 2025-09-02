<?php
/**
 * Script and Style Enqueue Module
 * Handles all CSS and JavaScript enqueuing for the customize functionality
 */

function enqueue_daisyui_and_tailwind() {
    // Enqueue DaisyUI main stylesheet
    wp_enqueue_style(
        'daisyui',
        'https://cdn.jsdelivr.net/npm/daisyui@5',
        array(),
        '5.0.0'
    );
    
    // Enqueue DaisyUI lofi theme only
    wp_enqueue_style(
        'daisyui-lofi-theme',
        'https://cdn.jsdelivr.net/npm/daisyui@5/dist/themes/lofi.css',
        array('daisyui'),
        '5.0.0'
    );

    // Enqueue Tailwind CSS Browser script
    wp_enqueue_script(
        'tailwindcss-browser',
        get_template_directory_uri() . '/js/tailwind-4.js',
        array(),
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_daisyui_and_tailwind');

function enqueue_customize_file() {
    $theme_dir = get_template_directory();
    
    // Get file modification times for versioning
    $css_version = filemtime($theme_dir . '/customize/css/customize.css');
    $js_version = filemtime($theme_dir . '/customize/js/customize.js');
    $thjs_version = filemtime($theme_dir . '/customize/js/thai-date.js');
    $space_parking_version = filemtime($theme_dir . '/customize/js/space-parking.js');
    $tabien_popup_version = filemtime($theme_dir . '/customize/js/tabien-popup.js');
    
    // Enqueue styles
    wp_enqueue_style('customize-styles', get_template_directory_uri() . '/customize/css/customize.css', array(), $css_version);
    wp_enqueue_style('picker-styles', get_template_directory_uri() . '/customize/css/picker.min.css', array());
    wp_enqueue_style('flatpickr-styles', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array());
    wp_enqueue_style('select2-styles', get_template_directory_uri() . '/customize/css/select2.min.css', array());
    
    // Enqueue scripts
    wp_enqueue_script('customize-script', get_template_directory_uri() . '/customize/js/customize.js', array('jquery'), $js_version, true);
    wp_enqueue_script('picker-script', get_template_directory_uri() . '/customize/js/picker.min.js', array('jquery'), true);
    wp_enqueue_script('flatpickr-script', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), true);
    wp_enqueue_script('thaidate-script', get_template_directory_uri() . '/customize/js/thai-date.js', array('jquery'), $thjs_version, true);
    wp_enqueue_script('select2-script', get_template_directory_uri() . '/customize/js/select2.min.js', array('jquery'), $thjs_version, true);
    wp_enqueue_script('space-parking-script', get_template_directory_uri() . '/customize/js/space-parking.js', array('jquery'), $space_parking_version, true);
    wp_enqueue_script('tabien-popup-script', get_template_directory_uri() . '/customize/js/tabien-popup.js', array('jquery'), $tabien_popup_version, true);
    
    // Add WordPress global variables for login check
    wp_localize_script('tabien-popup-script', 'wpApiSettings', array(
        'apiUrl' => home_url('/wp-json/wp/v2/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'userId' => get_current_user_id()
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_customize_file');

function remove_fluffy_styles() {
    wp_dequeue_style('fluffy-style');
    wp_deregister_style('fluffy-style');
}
add_action('wp_enqueue_scripts', 'remove_fluffy_styles', 100);

function enqueue_parking_scripts() {
    wp_enqueue_script('space-parking', get_template_directory_uri() . '/customize/js/space-parking.js', array('jquery'), null, true);
    
    // Send ajaxurl to JS
    wp_localize_script('space-parking', 'ajaxurl_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_parking_scripts');
?>
