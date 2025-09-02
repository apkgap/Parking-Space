<?php
/**
 * Modular Customize Main File
 * This file replaces the monolithic customize.php with a modular approach
 */

// Include control files (existing functionality)
require_once get_template_directory() . '/customize/control/carrent.php';
require_once get_template_directory() . '/customize/control/signup.php';
require_once get_template_directory() . '/customize/control/signin.php';
require_once get_template_directory() . '/customize/control/ajax-forgotpassword.php';
require_once get_template_directory() . '/customize/control/service-carrent.php';
require_once get_template_directory() . '/customize/control/add-driver-job.php';
require_once get_template_directory() . '/customize/control/service-management.php';
require_once get_template_directory() . '/customize/control/check-fight.php';
require_once get_template_directory() . '/customize/control/customer-profile.php';

// Include modular components
require_once get_template_directory() . '/customize/modules/enqueue-scripts.php';
require_once get_template_directory() . '/customize/modules/user-roles.php';
require_once get_template_directory() . '/customize/modules/ajax-handlers.php';
require_once get_template_directory() . '/customize/modules/api-endpoints.php';
require_once get_template_directory() . '/customize/modules/booking-endpoints.php';
require_once get_template_directory() . '/customize/modules/shortcodes.php';
require_once get_template_directory() . '/customize/modules/utilities.php';

/**
 * Initialize customize functionality
 */
function initialize_customize_modules() {
    // Any initialization code that needs to run after all modules are loaded
    do_action('customize_modules_loaded');
}
add_action('init', 'initialize_customize_modules');

/**
 * Add customize-specific body classes
 */
function add_customize_body_classes($classes) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $classes[] = 'user-logged-in';
        $classes[] = 'user-role-' . implode(' user-role-', $user->roles);
    }
    
    return $classes;
}
add_filter('body_class', 'add_customize_body_classes');
?>
