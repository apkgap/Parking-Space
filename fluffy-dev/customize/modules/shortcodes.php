<?php
/**
 * Shortcodes Module
 * Contains all custom shortcodes for the application
 */

/**
 * Login/Register buttons shortcode
 */
function login_register_buttons_shortcode($atts = []) {
    $atts = shortcode_atts([
        'welcome_text' => '%s',
        'login_text'   => 'Login',
        'register_text' => 'Register',
        'logout_text'  => 'Logout',
    ], $atts, 'login_register_buttons');

    ob_start();

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_roles = (array) $current_user->roles;
        $is_privileged_user = in_array('administrator', $user_roles, true) || in_array('customer-service', $user_roles, true);

        $welcome_text = sprintf(
            esc_html($atts['welcome_text']),
            esc_html($current_user->display_name)
        );
        ?>
        <div class="auth-user-logged-in flex items-center gap-2 md:gap-4">
            <?php if ($is_privileged_user): ?>
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="auth-dashboard-button duration-200 cursor-pointer flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user w-6 h-6 text-gray-700">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <p class="auth-welcome-message text-gray-700 font-medium ml-2 hidden md:flex text-sm"><?php echo $welcome_text; ?></p>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                        <li><a href="<?php echo esc_url(home_url('/dashboard/')); ?>">Dashboard</a></li>
                        <li><a href="<?php echo esc_url(home_url('/service/')); ?>">Service Control</a></li>
                        <li><a href="<?php echo esc_url(home_url('/profile/')); ?>">Profile</a></li>
                        <li><a href="<?php echo esc_url(home_url('/create-driver-job/')); ?>">Create Driver Job</a></li>
                        <li class="mt-2 border-t pt-2">
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="text-red-500 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out mr-2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <?php echo esc_html($atts['logout_text']); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="auth-dashboard-button duration-200 cursor-pointer flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user w-4 h-4 text-gray-700">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <p class="auth-welcome-message text-gray-700 font-medium ml-2 hidden md:flex text-sm"><?php echo $welcome_text; ?></p>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                        <li><a href="<?php echo esc_url(home_url('/profile/')); ?>">Profile</a></li>
                        <li class="mt-2 border-t pt-2">
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="text-red-500 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out mr-2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <?php echo esc_html($atts['logout_text']); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    } else {
        ?>
        <div class="auth-buttons-container flex items-center gap-3 md:gap-4" role="navigation" aria-label="<?php esc_attr_e('Account Actions', 'text-domain'); ?>">
            <a href="<?php echo esc_url(get_home_url() . '/login'); ?>" 
               class="auth-login-button flex items-center p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
               aria-label="<?php esc_attr_e('Login to your account', 'text-domain'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-in w-5 h-5 md:w-4 md:h-4 text-gray-700 hover:text-gray-900 transition-colors duration-200">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <span class="ml-1 text-gray-700 hover:text-gray-900 transition-colors duration-200 hidden md:flex text-sm"><?php echo esc_html($atts['login_text']); ?></span>
            </a>
            <?php if (get_option('users_can_register')) : ?>
                <a href="<?php echo esc_url(get_home_url() . '/register'); ?>" 
                   class="auth-register-button flex items-center p-2 px-4 rounded-full bg-primary bg-opacity-10 hover:bg-opacity-20 transition-colors duration-200"
                   aria-label="<?php esc_attr_e('Create a new account', 'text-domain'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus w-5 h-5 md:w-4 md:h-4 text-white hover:text-white-focus transition-colors duration-200">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span class="ml-1 text-white hover:text-white-focus transition-colors duration-200 hidden md:flex text-sm"><?php echo esc_html($atts['register_text']); ?></span>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    return ob_get_clean();
}
add_shortcode('login_register_buttons', 'login_register_buttons_shortcode');

/**
 * Booking buttons shortcode
 */
function my_booking_buttons_shortcode() {
    ob_start();
    
    $parkcar_icon = get_template_directory_uri() . '/customize/img/parked-car.svg';
    $vehicle = get_template_directory_uri() . '/customize/img/vehicle.svg';
    ?>
    <div class="button-container">
        <button id="bk-primary" class="booking-button primary-button" data-url="carpark">
            <img src="<?php echo $parkcar_icon; ?>"  alt="จองที่จอดรถ">
            จองที่จอดรถ
        </button>
        <button id="bk-secondary" class="booking-button secondary-button" data-url="carrent">
            <img src="<?php echo $vehicle; ?>" alt="จองรถส่วนตัว">
            จองรถส่วนตัว
        </button>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('booking_buttons', 'my_booking_buttons_shortcode');

/**
 * Custom loading box
 */
function my_custom_loading_box() {
    ?>
    <div id="box-loading" class="box-loading" style="display: none;">
        <div class="loader"></div>
        <div class="blur"></div>
    </div>
    <?php
}
add_action('wp_footer', 'my_custom_loading_box');
?>
