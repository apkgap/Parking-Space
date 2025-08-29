<?php

require_once get_template_directory() . '/customize/control/carrent.php';
require_once get_template_directory() . '/customize/control/signup.php';
require_once get_template_directory() . '/customize/control/signin.php';
require_once get_template_directory() . '/customize/control/ajax-forgotpassword.php';
require_once get_template_directory() . '/customize/control/service-carrent.php';
require_once get_template_directory() . '/customize/control/add-driver-job.php';
require_once get_template_directory() . '/customize/control/service-management.php';
require_once get_template_directory() . '/customize/control/check-fight.php';
require_once get_template_directory() . '/customize/control/customer-profile.php';
// echo get_template_directory() . '/customize/control/ajax-forgotpassword.php';

// Create custom role 'service-customer'
add_action( 'init', function() {
    add_role( 'service-customer', 'Service Customer', array(
        'read'         => true,
        'edit_posts'   => true,
        'delete_posts' => true,
        'upload_files' => true,
    ) );
} );

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

    // // Enqueue Tailwind CSS Browser script
    wp_enqueue_script(
        'tailwindcss-browser',
        get_template_directory_uri() . '/js/tailwind-4.js',
        array(),
        true
    );
}

// Hook the function to wp_enqueue_scripts
add_action('wp_enqueue_scripts', 'enqueue_daisyui_and_tailwind');


function enqueue_customize_file() {
    $css_file = get_template_directory() . '/customize/css/customize.css';
    $js_file = get_template_directory() . '/customize/js/customize.js';
    $thaidatejs_file = get_template_directory() . '/customize/js/thai-date.js';
    $space_parking = get_template_directory() . '/customize/js/space-parking.js';
    $tabien_popup = get_template_directory() . '/customize/js/tabien-popup.js';

    $css_version = filemtime($css_file);
    $js_version = filemtime($js_file);
    $thjs_version = filemtime($thaidatejs_file);
    $space_parking_version = filemtime($space_parking);
    $tabien_popup_version = filemtime($tabien_popup);

    wp_enqueue_style('customize-styles', get_template_directory_uri() . '/customize/css/customize.css', array(), $css_version);
    wp_enqueue_style('picker-styles', get_template_directory_uri() . '/customize/css/picker.min.css', array());
    wp_enqueue_style('flatpickr-styles', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array());
    wp_enqueue_script('customize-script', get_template_directory_uri() . '/customize/js/customize.js', array('jquery'), $js_version, true);
    wp_enqueue_script('picker-script', get_template_directory_uri() . '/customize/js/picker.min.js', array('jquery'), true);
    wp_enqueue_script('flatpickr-script', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), true);
    wp_enqueue_script('thaidate-script', get_template_directory_uri() . '/customize/js/thai-date.js', array('jquery'), $thjs_version, true);
    wp_enqueue_style('select2-styles',  get_template_directory_uri() . '/customize/css/select2.min.css', array());  
    wp_enqueue_script('select2-script', get_template_directory_uri() . '/customize/js/select2.min.js', array('jquery'), $thjs_version, true);
    wp_enqueue_script('space-parking-script', get_template_directory_uri() . '/customize/js/space-parking.js', array('jquery'), $space_parking_version, true);
    wp_enqueue_script('tabien-popup-script', get_template_directory_uri() . '/customize/js/tabien-popup.js', array('jquery'), $tabien_popup_version, true);
    
    // Add WordPress global variables for login check
    wp_localize_script('tabien-popup-script', 'wpApiSettings', array(
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'userId' => get_current_user_id()
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_customize_file');

function remove_fluffy_styles() {
    wp_dequeue_style( 'fluffy-style' ); // Replace 'fluffy-style' with the actual handle if different
    wp_deregister_style( 'fluffy-style' );
}
add_action( 'wp_enqueue_scripts', 'remove_fluffy_styles', 100 );

function login_register_buttons_shortcode($atts = []) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'welcome_text' => '%s', // %s will be replaced with display name
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
                        <!-- Feather icon for user -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user w-6 h-6 text-gray-700">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <!-- Text for desktop only -->
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
                        <!-- Feather icon for user -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user w-4 h-4 text-gray-700">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <!-- Text for desktop only -->
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
                <!-- Feather icon for log-in -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-in w-5 h-5 md:w-4 md:h-4 text-gray-700 hover:text-gray-900 transition-colors duration-200">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <!-- Text for desktop only -->
                <span class="ml-1 text-gray-700 hover:text-gray-900 transition-colors duration-200 hidden md:flex text-sm"><?php echo esc_html($atts['login_text']); ?></span>
            </a>
            <?php if (get_option('users_can_register')) : ?>
                <a href="<?php echo esc_url(get_home_url() . '/register'); ?>" 
                   class="auth-register-button flex items-center p-2 px-4 rounded-full bg-primary bg-opacity-10 hover:bg-opacity-20 transition-colors duration-200"
                   aria-label="<?php esc_attr_e('Create a new account', 'text-domain'); ?>">
                    <!-- Feather icon for user-plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus w-5 h-5 md:w-4 md:h-4 text-white hover:text-white-focus transition-colors duration-200">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <!-- Text for desktop only -->
                    <span class="ml-1 text-white hover:text-white-focus transition-colors duration-200 hidden md:flex text-sm"><?php echo esc_html($atts['register_text']); ?></span>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    return ob_get_clean();
}
add_shortcode('login_register_buttons', 'login_register_buttons_shortcode');

function my_booking_buttons_shortcode() {
    ob_start(); // Start output buffering
    
    $parkcar_icon = get_template_directory_uri() . '/customize/img/parked-car.svg';
    $vehicle = get_template_directory_uri() . '/customize/img/vehicle.svg';
    // Your HTML goes here
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

    return ob_get_clean(); // Return the buffered content
}
add_shortcode( 'booking_buttons', 'my_booking_buttons_shortcode' );

function my_custom_loading_box() {
    ?>
    <div id="box-loading" class="box-loading" style="display: none;">
        <div class="loader"></div>
        <div class="blur"></div>
    </div>
    <?php
}
add_action( 'wp_footer', 'my_custom_loading_box' );

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/vehicle_type', array(
        'methods' => 'GET',
        'callback' => 'get_custom_vehicle_type_data',
        'permission_callback' => '__return_true', // ให้ทุกคนเรียกดูได้ (เปลี่ยนตามความต้องการ)
    ));
});

function get_custom_vehicle_type_data($request) {
    $args = array(
        'post_type' => 'vehicle_type',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'max_seats' => get_field('max_seats'),
                'pricing_rate' => get_field('pricing_rate'),
            );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response($data);
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/parking_space', array(
        'methods' => 'GET',
        'callback' => 'get_parking_space_data',
        'permission_callback' => '__return_true', // ให้ทุกคนดูได้
        'args' => array(
            'location' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Filter parking spaces by location taxonomy',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'locate_id' => array(
                'required' => false,
                'type' => 'integer',
                'description' => 'Filter parking spaces by location ID',
                'sanitize_callback' => 'absint',
            ),
        ),
    ));
});

function get_parking_space_data($request) {
    $args = array(
        'post_type' => 'parking_space',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'asc',
    );
    
    // Get the location parameter from the request
    $location = $request->get_param('location');
    $locate_id = $request->get_param('locate_id');
    
    // If location parameter is provided, filter by location taxonomy
    if (!empty($location)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'location',
                'field'    => 'slug',
                'terms'    => $location,
            ),
        );
    }
    if (!empty($locate_id)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'location',
                'field'    => 'id',
                'terms'    => $locate_id,
            ),
        );
    }
    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $location_terms = get_the_terms(get_the_ID(), 'location');
            $location_name = !is_wp_error($location_terms) && !empty($location_terms) ? $location_terms[0]->name : '';
            $location_slug = !is_wp_error($location_terms) && !empty($location_terms) ? $location_terms[0]->slug : '';
            $location_id = !is_wp_error($location_terms) && !empty($location_terms) ? $location_terms[0]->term_id : '';
            
            $data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'parking_id' => get_field('parking_id'),
                'location' => array(
                    'id' => $location_id,
                    'name' => $location_name,
                    'slug' => $location_slug                    
                ),
                'position' => array(
                    'x' => get_field('position')['position_x'] ?? null,
                    'y' => get_field('position')['position_y'] ?? null,
                ),
            );
        }
        wp_reset_postdata();
    }
return rest_ensure_response($data);
}

// New endpoint to list taxonomy terms
add_action('rest_api_init', function () {
register_rest_route('custom/v1', '/taxonomy_terms/(?P<taxonomy>[a-zA-Z0-9_-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_taxonomy_terms',
    'permission_callback' => '__return_true',
    'args' => array(
        'taxonomy' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The taxonomy to retrieve terms from',
        ),
    ),
));
});
function get_taxonomy_terms($request) {
    $taxonomy = $request['taxonomy'];
    
    // Check if taxonomy exists
    if (!taxonomy_exists($taxonomy)) {
        return new WP_Error('invalid_taxonomy', 'Invalid taxonomy', array('status' => 404));
    }
    
    // Get terms
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    if (is_wp_error($terms)) {
        return $terms;
    }
    
    // Format terms for response to match the expected format
    $formatted_terms = array();
    foreach ($terms as $term) {
        $formatted_terms[] = array(
            'id' => $term->term_id,
            'title' => $term->name,
        );
    }
    
    return rest_ensure_response($formatted_terms);
}



add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/parking_booking', array(
        'methods' => 'GET',
        'callback' => 'get_parking_booking_data',
        'permission_callback' => '__return_true',
    ));
    
    // User-specific parking bookings endpoint
    register_rest_route('custom/v1', '/user_parking_booking', array(
        'methods' => 'GET',
        'callback' => 'get_user_parking_booking_data',
        'permission_callback' => 'is_user_logged_in',
    ));
});

function get_parking_booking_data($request) {
    $per_page = $request->get_param('per_page') ? (int) $request->get_param('per_page') : 100;

    $args = array(
        'post_type'      => 'parking_booking',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    // Add search functionality
    if ( ! empty( $request['search'] ) ) {
        $args['s'] = sanitize_text_field( $request['search'] );
    }

    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $vehicle_info = [];
            if (have_rows('vehicle_info')) {
                while (have_rows('vehicle_info')) {
                    the_row();
                    $vehicle_info[] = array(
                        'vehicle_type_relation' => get_sub_field('vehicle_type_relation') ? get_sub_field('vehicle_type_relation')->ID : null,
                        'license_plate'         => get_sub_field('license_plate'),
                        'other_type_car'        => get_sub_field('other_type_car'),
                        'booked_parking_space'  => get_sub_field('booked_parking_space') ? get_sub_field('booked_parking_space')->ID : null,
                        'zone_parking'          => get_sub_field('zone_parking') ? get_sub_field('zone_parking')->ID : null,
                        'parking_slot'          => get_sub_field('parking_slot'),
                        'parking_slot_id'       => get_sub_field('parking_slot_id'),
                    );
                }
            }

            $customer_info = get_field('customer_info');
            $customer_user = get_field('customer_user');
            $booking_status = get_field('booking_status_relation');
            $tracking_check_status = get_field('tracking_check_status');
            $order_code = get_field('order_code');

            $location_data = null;
            $location = get_field('location_park');
            if ($location) {
                if (is_object($location)) {
                    $location_data = [
                        'id' => $location->term_id,
                        'name' => $location->name,
                        'slug' => $location->slug
                    ];
                } else {
                    $term = get_term($location, 'location'); // Replace 'location' with your taxonomy name
                    if ($term && !is_wp_error($term)) {
                        $location_data = [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug
                        ];
                    }
                }
            }


            $addon_information = get_field('addon_information');
            
            // Structure addon data according to ACF fields
            $addon_data = array();
            
            if ($addon_information) {
                $addon_data = array(
                    'service_pickup' => !empty($addon_information['service_pickup']),
                    'service_valet' => !empty($addon_information['service_valet']),
                    'sending_flight' => array(
                        'depart' => $addon_information['sending_flight']['depart'] ?? '',
                        'depart_time' => $addon_information['sending_flight']['depart_time'] ?? '',
                        'arrival' => $addon_information['sending_flight']['arrival'] ?? '',
                        'arrival_time' => $addon_information['sending_flight']['arrival_time'] ?? '',
                        'pickup_time' => $addon_information['sending_flight']['pickup_time'] ?? '',
                        'dropoff_time' => $addon_information['sending_flight']['dropoff_time'] ?? '',
                        'pickup_point' => $addon_information['sending_flight']['pickup_point'] ?? '',
                        'dropoff_point' => $addon_information['sending_flight']['dropoff_point'] ?? '',
                        'adult_count' => $addon_information['sending_flight']['adult_count'] ?? '',
                        'child_count' => $addon_information['sending_flight']['child_count'] ?? '',
                        'baggage_count' => $addon_information['sending_flight']['baggage_count'] ?? '',
                        'pickup_note' => $addon_information['sending_flight']['pickup_note'] ?? '',
                    ),
                    'parking_car' => array(
                        'depart' => $addon_information['parking_car']['depart'] ?? '',
                        'depart_time' => $addon_information['parking_car']['depart_time'] ?? '',
                        'arrival' => $addon_information['parking_car']['arrival'] ?? '',
                        'arrival_time' => $addon_information['parking_car']['arrival_time'] ?? '',
                        'pickup_time' => $addon_information['parking_car']['pickup_time'] ?? '',
                        'dropoff_time' => $addon_information['parking_car']['dropoff_time'] ?? '',
                        'pickup_point' => $addon_information['parking_car']['pickup_point'] ?? '',
                        'dropoff_point' => $addon_information['parking_car']['dropoff_point'] ?? '',
                        'pickup_note' => $addon_information['parking_car']['pickup_note'] ?? '',
                    ),
                );
            }

            $data[] = array(
                'id' => get_the_ID(),
                'order_code' => $order_code,
                'qrcode_svg' => get_field('qrcode_svg'),
                'qr_code_url' => get_field('qr_code_url'),                
                'title' => get_the_title(),
                'start_date' => get_field('start_date'),
                'start_time' => get_field('start_time'),
                'end_date'   => get_field('end_date'),
                'end_time'   => get_field('end_time'),
                'booking_status_relation' => $booking_status ? (is_object($booking_status) ? $booking_status->ID : $booking_status) : null,
                'tracking_check_status' => $tracking_check_status,
                'fullname_park' => get_field('fullname_park'),
                'email_park' => get_field('email_park'),
                'line_id_park' => get_field('line_id_park'),
                'phone_park' => get_field('phone_park'),
                'customer_info' => $customer_info,
                'customer_user' => $customer_user ? (is_object($customer_user) ? $customer_user->ID : $customer_user) : null,
                'vehicle_info'  => $vehicle_info,
                'location_park' => $location_data,
                'addon_information' => $addon_data,
            );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response($data);
}

function get_user_parking_booking_data($request) {
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
    }

    $per_page = $request->get_param('per_page') ? (int) $request->get_param('per_page') : 10;
    $page = $request->get_param('page') ? (int) $request->get_param('page') : 1;
    $status_filter = $request->get_param('status');

    $args = array(
        'post_type'      => 'parking_booking',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => 'customer_user',
                'value'   => $current_user_id,
                'compare' => '='
            )
        )
    );

    // Add search functionality
    if (!empty($request['search'])) {
        $args['s'] = sanitize_text_field($request['search']);
    }

    // Add status filter
    if (!empty($status_filter)) {
        $args['meta_query'][] = array(
            'key'     => 'tracking_check_status',
            'value'   => $status_filter,
            'compare' => '='
        );
    }

    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $vehicle_info = [];
            if (have_rows('vehicle_info')) {
                while (have_rows('vehicle_info')) {
                    the_row();
                    $vehicle_info[] = array(
                        'vehicle_type_relation' => get_sub_field('vehicle_type_relation') ? get_sub_field('vehicle_type_relation')->ID : null,
                        'license_plate'         => get_sub_field('license_plate'),
                        'other_type_car'        => get_sub_field('other_type_car'),
                        'booked_parking_space'  => get_sub_field('booked_parking_space') ? get_sub_field('booked_parking_space')->ID : null,
                        'zone_parking'          => get_sub_field('zone_parking') ? get_sub_field('zone_parking')->ID : null,
                        'parking_slot'          => get_sub_field('parking_slot'),
                        'parking_slot_id'       => get_sub_field('parking_slot_id'),
                    );
                }
            }

            $customer_info = get_field('customer_info');
            $customer_user = get_field('customer_user');
            $booking_status = get_field('booking_status_relation');
            $tracking_check_status = get_field('tracking_check_status');
            $order_code = get_field('order_code');

            $location_data = null;
            $location = get_field('location_park');
            if ($location) {
                if (is_object($location)) {
                    $location_data = [
                        'id' => $location->term_id,
                        'name' => $location->name,
                        'slug' => $location->slug
                    ];
                } else {
                    $term = get_term($location, 'location');
                    if ($term && !is_wp_error($term)) {
                        $location_data = [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug
                        ];
                    }
                }
            }

            $addon_information = get_field('addon_information');
            
            $addon_data = array();
            
            if ($addon_information) {
                $addon_data = array(
                    'service_pickup' => !empty($addon_information['service_pickup']),
                    'service_valet' => !empty($addon_information['service_valet']),
                    'sending_flight' => array(
                        'depart' => $addon_information['sending_flight']['depart'] ?? '',
                        'depart_time' => $addon_information['sending_flight']['depart_time'] ?? '',
                        'arrival' => $addon_information['sending_flight']['arrival'] ?? '',
                        'arrival_time' => $addon_information['sending_flight']['arrival_time'] ?? '',
                        'pickup_time' => $addon_information['sending_flight']['pickup_time'] ?? '',
                        'dropoff_time' => $addon_information['sending_flight']['dropoff_time'] ?? '',
                        'pickup_point' => $addon_information['sending_flight']['pickup_point'] ?? '',
                        'dropoff_point' => $addon_information['sending_flight']['dropoff_point'] ?? '',
                        'adult_count' => $addon_information['sending_flight']['adult_count'] ?? '',
                        'child_count' => $addon_information['sending_flight']['child_count'] ?? '',
                        'baggage_count' => $addon_information['sending_flight']['baggage_count'] ?? '',
                        'pickup_note' => $addon_information['sending_flight']['pickup_note'] ?? '',
                    ),
                    'parking_car' => array(
                        'depart' => $addon_information['parking_car']['depart'] ?? '',
                        'depart_time' => $addon_information['parking_car']['depart_time'] ?? '',
                        'arrival' => $addon_information['parking_car']['arrival'] ?? '',
                        'arrival_time' => $addon_information['parking_car']['arrival_time'] ?? '',
                        'pickup_time' => $addon_information['parking_car']['pickup_time'] ?? '',
                        'dropoff_time' => $addon_information['parking_car']['dropoff_time'] ?? '',
                        'pickup_point' => $addon_information['parking_car']['pickup_point'] ?? '',
                        'dropoff_point' => $addon_information['parking_car']['dropoff_point'] ?? '',
                        'pickup_note' => $addon_information['parking_car']['pickup_note'] ?? '',
                    ),
                );
            }

            $data[] = array(
                'id' => get_the_ID(),
                'order_code' => $order_code,
                'qrcode_svg' => get_field('qrcode_svg'),
                'qr_code_url' => get_field('qr_code_url'),                
                'title' => get_the_title(),
                'start_date' => get_field('start_date'),
                'start_time' => get_field('start_time'),
                'end_date'   => get_field('end_date'),
                'end_time'   => get_field('end_time'),
                'booking_status_relation' => $booking_status,
                'tracking_check_status' => $tracking_check_status,
                'fullname_park' => get_field('fullname_park'),
                'email_park' => get_field('email_park'),
                'line_id_park' => get_field('line_id_park'),
                'phone_park' => get_field('phone_park'),
                'customer_info' => $customer_info,
                'customer_user' => $customer_user ? (is_object($customer_user) ? $customer_user->ID : $customer_user) : null,
                'vehicle_info'  => $vehicle_info,
                'location_park' => $location_data,
                'addon_information' => $addon_data,
            );
        }
        wp_reset_postdata();
    }

    $response = array(
        'data' => $data,
        'total' => $query->found_posts,
        'pages' => $query->max_num_pages,
        'current_page' => $page,
        'per_page' => $per_page
    );

    return rest_ensure_response($response);
}

function enqueue_parking_scripts() {
    // เปลี่ยนชื่อไฟล์ JS เป็น space-parking.js
    wp_enqueue_script('space-parking', get_template_directory_uri() . '/customize/js/space-parking.js', array('jquery'), null, true);

    // ส่งค่า ajaxurl ไปยัง JS
    wp_localize_script('space-parking', 'ajaxurl_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_footer', 'enqueue_parking_scripts');

function wert4425() {  
    $args = [
        'post_type' => 'parking_booking',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];

    $query = new WP_Query($args);

    // เก็บ start_date ทั้งหมดใน array
    $start_dates = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // ดึงค่า ACF field start_date และ end_date
            $start_date = get_field('start_date');
            $end_date = get_field('end_date');
            
            // เพิ่มลงใน array
            $start_dates[] = $start_date;
            
            // แสดงข้อมูลแต่ละรายการ
            echo '<div class="parking-booking-item">';
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p>Start Date: ' . $start_date . '</p>';
            echo '<p>End Date: ' . $end_date . '</p>';
            echo '<p>Post ID: ' . get_the_ID() . '</p>';
            echo '</div>';
        }
        
        // Reset post data หลังจาก custom query
        wp_reset_postdata();
        
        // แสดง start_date ทั้งหมดใน array
        echo '<h4>Start Dates ทั้งหมด:</h4>';
        echo '<pre>';
        print_r($start_dates);
        echo '</pre>';
        
    } else {
        echo 'ไม่พบข้อมูล parking booking';
    }
}
// add_action('init', 'wert4425');


function get_occupied_parking_spaces($start_datetime_str, $end_datetime_str, $locationChoose) {
    // 1. ตรวจสอบค่าว่างของสตริงอินพุต
    if (empty($start_datetime_str)) {
        return ['error' => 'start_datetime_str is empty'];
    }
    if (empty($end_datetime_str)) {
        return ['error' => 'end_datetime_str is empty'];
    }
    if (empty($locationChoose)) {
        return ['error' => 'locationChoose is empty'];
    }

    // 2. แปลงสตริงอินพุตให้เป็น DateTime objects
    try {
        $start_dt = new DateTime($start_datetime_str);
        $end_dt = new DateTime($end_datetime_str);
    } catch (Exception $e) {
        return ['error' => 'Invalid date/time format for input parameters: ' . $e->getMessage()];
    }

    // ตรวจสอบความถูกต้องของ DateTime objects ที่สร้างได้
    if (!$start_dt || !$end_dt) { // ปกติแล้วถ้ามี Exception จะไม่ถึงตรงนี้ แต่มีไว้เพื่อความปลอดภัย
        return ['error' => 'Invalid date/time format after conversion.'];
    }

    $occupied_space_ids = [];

    // 3. สร้าง WP_Query เพื่อดึงข้อมูลการจอง (ส่วนนี้ยังคงเดิม)
    $args = [
        'post_type'      => 'parking_booking',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'     => 'location_park',
                'value'   => $locationChoose,
                'compare' => 'LIKE',
            ],
        ],
    ];
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // 4. ดึงข้อมูลวันที่และเวลาของการจอง
            $booking_start_date = get_field('start_date'); // สมมติว่ารูปแบบคือ YYYY-MM-DD
            $booking_end_date   = get_field('end_date');   // สมมติว่ารูปแบบคือ YYYY-MM-DD
            $booking_start_time = get_field('start_time') ?: '00:00:00'; // สมมติว่ารูปแบบคือ HH:MM:SS
            $booking_end_time   = get_field('end_time') ?: '23:59:59';   // สมมติว่ารูปแบบคือ HH:MM:SS

            // 5. รวมวันที่และเวลาเพื่อสร้าง DateTime objects ของการจอง
            try {
                $booking_start_dt = new DateTime($booking_start_date . ' ' . $booking_start_time);
                $booking_end_dt   = new DateTime($booking_end_date . ' ' . $booking_end_time);
            } catch (Exception $e) {
                // หากแปลงไม่ได้ ให้ข้ามการจองนี้ไป
                error_log("Failed to parse booking date/time for post ID " . get_the_ID() . ": " . $e->getMessage());
                continue;
            }
            $real_start_dt = $booking_start_dt->format('Y-m-d H:i:s');
            $real_end_dt = $booking_end_dt->format('Y-m-d H:i:s');
            // $m_name = $start_dt ->format('Y-m-d H:i:s') . ' - ' . $end_dt->format('Y-m-d H:i:s');
            // $occupied_spaces[] = $real_start_dt . ' - ' . $real_end_dt;

            if ($booking_start_dt <= $end_dt && $booking_end_dt >= $start_dt) {
                // $occupied_spaces[] = get_the_ID(); // เก็บ ID ของโพสต์ที่มีการจองทับซ้อน
                $vehicle_info_rows = get_field('vehicle_info'); // ดึงข้อมูลจาก Repeater Field 'vehicle_info'

                if ($vehicle_info_rows) {
                    foreach ($vehicle_info_rows as $row) {
                        if (isset($row['booked_parking_space']) && is_object($row['booked_parking_space'])) {
                            $booked_parking_space_post = $row['booked_parking_space'];
                            $parking_space_id = $booked_parking_space_post->ID;
                            $occupied_space_ids[] = $parking_space_id;
                        }
                    }
                }
            }
        }
    
        wp_reset_postdata();
    }

    // 8. ลบ ID ที่ซ้ำกันและส่งคืนผลลัพธ์
    $occupied_space_ids = array_unique($occupied_space_ids);

    // ดึงข้อมูลรายละเอียดแต่ละช่องจอด
    $occupied_spaces = [];
    foreach ($occupied_space_ids as $space_id) {
        $parking_space_tax = get_the_terms( $space_id, 'location' );
        $position = get_field('position', $space_id);
        $occupied_spaces[] = [
            'id' => $space_id,
            'title' => get_the_title($space_id),
            'parking_id' => get_field('parking_id', $space_id),
            'position' => [
                'x' => $position['position_x'] ?? '',
                'y' => $position['position_y'] ?? '',
            ],
            'location_park_tax' => [
                'id' => $parking_space_tax[0]->term_id,
                'name' => $parking_space_tax[0]->name,
                'slug' => $parking_space_tax[0]->slug,
            ],
        ];
    }

    return $occupied_spaces;
}

add_action('wp_ajax_check_parking_availability', 'check_parking_availability');
add_action('wp_ajax_nopriv_check_parking_availability', 'check_parking_availability');
function check_parking_availability() {
    // รับข้อมูลจาก AJAX
    $start_datetime = sanitize_text_field($_POST['startDateTime'] ?? '');
    $end_datetime = sanitize_text_field($_POST['endDateTime'] ?? '');
    $locationChoose = sanitize_text_field($_POST['locationChoose'] ?? '');

    if (empty($start_datetime) || empty($end_datetime)) {
        wp_send_json_error('กรุณาระบุวันที่และเวลาให้ครบถ้วน');
        return;
    }

    $result = get_occupied_parking_spaces($start_datetime, $end_datetime, $locationChoose);

    if (isset($result['error'])) {
        wp_send_json_error($result['error']);
    } else {
        wp_send_json_success($result);
    }
}

add_action('wp_ajax_insert_parking_booking', 'insert_parking_booking');
add_action('wp_ajax_nopriv_insert_parking_booking', 'insert_parking_booking');

add_action('init', function () {
    $autoload = trailingslashit( WP_PLUGIN_DIR ) . 'yp-qrcode/vendor/autoload.php';
    if ( file_exists( $autoload ) ) {
        require_once $autoload;
    } else {
        error_log('Composer autoload not found: ' . $autoload);
    }
});

// ========= 2) ฟังก์ชันสร้าง QR เป็น SVG ด้วย endroid/qr-code =========
function generate_booking_qr_code_png($booking_id, $order_code) {
    // ensure autoload
    if ( ! class_exists('\Endroid\QrCode\Builder\Builder') ) {
        $autoload = trailingslashit( WP_PLUGIN_DIR ) . 'yp-qrcode/vendor/autoload.php';
        if ( file_exists( $autoload ) ) {
            require_once $autoload;
        } else {
            error_log('Composer autoload not found: ' . $autoload);
            return false;
        }
    }

    $booking_url = add_query_arg(
        [
            'id'   => (string) $booking_id,
            'code' => (string) $order_code,
        ],
        home_url( '/check-in/' )
    );

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) ) {
        error_log( 'Uploads dir error: ' . $uploads['error'] );
        return false;
    }
    $dir_path = trailingslashit( $uploads['basedir'] ) . 'qr_codes';
    $dir_url  = trailingslashit( $uploads['baseurl'] ) . 'qr_codes';
    if ( ! file_exists( $dir_path ) && ! wp_mkdir_p( $dir_path ) ) {
        error_log( 'Failed to create dir: ' . $dir_path );
        return false;
    }

    $filename   = 'qr_' . sanitize_file_name( (string) $order_code ) . '.png';
    $file_path  = trailingslashit( $dir_path ) . $filename;
    $public_url = trailingslashit( $dir_url ) . $filename;

    try {
        $result = \Endroid\QrCode\Builder\Builder::create()
            ->writer(new \Endroid\QrCode\Writer\PngWriter())
            ->data($booking_url)
            ->size(300)
            ->margin(10)
            ->build();

        // Save the QR code to a file
        $result->saveToFile($file_path);
        @chmod($file_path, 0644);

        // Return the public URL of the QR code
        return $public_url;
    } catch (\Throwable $e) {
        error_log('QR PNG generation failed: ' . $e->getMessage());
        return false;
    }
}

function insert_parking_booking() {
    if (empty($_POST['bookingData'])) {
        wp_send_json_error('ไม่มีข้อมูลที่ส่งมา');
    }

    $data = json_decode(stripslashes($_POST['bookingData']), true);

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($data['startDate']) || empty($data['endDate'])) {
        wp_send_json_error('ข้อมูลไม่ครบถ้วน');
    }
    $profile = $data['profile'];

    // Generate a 6-digit order code
    $order_code = mt_rand(100000, 999999);

    // สร้าง post ใหม่
    $post_id = wp_insert_post([
        'post_type'   => 'parking_booking',
        'post_status' => 'publish',
        'post_title'  => 'Booking ' . ($data['startDate'] ?? '') . ' - ' . ($data['endDate'] ?? '') . ' - ' . ($profile['fullname'] ?? '') . ' #' . $order_code,
    ]);

    if (is_wp_error($post_id) || !$post_id) {
        wp_send_json_error('ไม่สามารถสร้างโพสต์ได้');
    }

    // Save the order code to an ACF field
    update_field('order_code', $order_code, $post_id);

    $qr = generate_booking_qr_code_png( $post_id, $order_code );

    // Save ACF ถ้าสำเร็จ
        update_field('qr_code_url', esc_url_raw($qr), $post_id);

    // บันทึกฟิลด์ ACF หลัก
    update_field('start_date', $data['startDate'], $post_id);
    update_field('end_date', $data['endDate'], $post_id);
    
    // บันทึกข้อมูลโปรไฟล์
    update_field('location_park', $data['location'], $post_id);
    update_field('tracking_check_status', 'confirmed', $post_id);

    update_field('fullname_park', sanitize_text_field($profile['fullname'] ?? ''), $post_id);
    update_field('email_park', sanitize_email($profile['email'] ?? ''), $post_id);
    update_field('phone_park', sanitize_text_field($profile['phone'] ?? ''), $post_id);
    update_field('line_id_park', sanitize_text_field($profile['lineid'] ?? ''), $post_id);
    
    update_field('start_time', $data['startTime'] ?? '', $post_id);
    update_field('end_time', $data['endTime'] ?? '', $post_id);
    update_field('booking_status_relation', 1244, $post_id);
    update_field('price_percar', $data['pricePerCar'] ?? '', $post_id);
    update_field('cp_price', $data['totalPrice'] ?? '', $post_id);

    // Update customer_user if user is logged in
    if (is_user_logged_in()) {
        update_field('customer_user', get_current_user_id(), $post_id);
    }

    // ตัวอย่าง: บันทึกข้อมูลลูกค้า (กรณีไม่ได้เป็นสมาชิก)
    if (!empty($data['customer_name']) && !empty($data['customer_phone'])) {
        update_field('customer_info', [
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? '',
        ], $post_id);
    }

    // ตัวอย่าง: บันทึกข้อมูลลูกค้า (กรณีเป็นสมาชิก)
    if (!empty($data['customer_user'])) {
        update_field('customer_user', get_current_user_id(), $post_id); // เก็บเป็น id ของ current user
    }

    // บันทึก vehicle_type_info (repeater) 
    if (!empty($data['vehicleTypeQuantities'])) {
        $vehicle_type_info = [];
        foreach ($data['vehicleTypeQuantities'] as $vehicle) {
            $vehicle_type_info[] = [
                'quantity_cp' => intval($vehicle['quantity'] ?? 0),
                'tp_car'      => sanitize_text_field($vehicle['type'] ?? ''),
            ];
        }
        update_field('vehicle_type_info', $vehicle_type_info, $post_id);
    }

    // บันทึก vehicle_info (repeater) สำหรับข้อมูลรถแต่ละคัน
    if (!empty($data['selectedParkingSpaces'])) {
        $vehicle_info = [];
        foreach ($data['selectedParkingSpaces'] as $i => $space) {
            $vehicle_info[] = [
                'vehicle_type_relation' => $space['assignedVehicleType'], // ต้องเป็น post ID ของ vehicle_type
                'license_plate'         => $space['licensePlate'] ?? '',
                'other_type_car'        => $space['otherTypeCar'] ?? '',
                'booked_parking_space'  => $space['parking_post_id'] ?? '', // ต้องเป็น post ID ของ parking_space
                'zone_parking'          => $space['zone_parking'] ?? '',   // ต้องเป็น post ID ของ parking_zone
                'parking_slot'          => $space['id'] ?? '',
                'parking_slot_id'       => $space['parking_id'] ?? '',
            ];
            
            // Create tabien-rod post for each vehicle with license plate
            if (!empty($space['licensePlate'])) {
                // Check if a tabien-rod post with the same license plate already exists for the same user
                $existing_tabien = get_posts([
                    'post_type' => 'tabien-rod',
                    'post_status' => 'publish',
                    'meta_query' => [
                        [
                            'key' => 'tabienrod_number',
                            'value' => $space['licensePlate'],
                            'compare' => '='
                        ],
                        [
                            'key' => 'user-okpark',
                            'value' => get_current_user_id(),
                            'compare' => '='
                        ]
                    ],
                    'numberposts' => 1
                ]);
                
                // Only create new post if no duplicate exists
                if (empty($existing_tabien)) {
                    // Create new tabien-rod post
                    $tabien_post_id = wp_insert_post([
                        'post_type'   => 'tabien-rod',
                        'post_status' => 'publish',
                        'post_title'  => 'ทะเบียนรถ: ' . $space['licensePlate'] . ' - ' . $profile['fullname'],
                    ]);
                    
                    // If post created successfully, save ACF fields
                    if ($tabien_post_id && !is_wp_error($tabien_post_id)) {
                        update_field('tabienrod_number', sanitize_text_field($space['licensePlate']), $tabien_post_id);
                        update_field('user-okpark', get_current_user_id(), $tabien_post_id);
                    }
                }
            }
        }
        update_field('vehicle_info', $vehicle_info, $post_id);
    }

    // --- บันทึกบริการเสริม (addon_information) ---
    if (!empty($data['addon'])) {
        $addon = $data['addon'];
        update_field('addon_information', [
            'service_pickup' => !empty($addon['servicePickup']),
            'service_valet' => !empty($addon['serviceValet']),
            'sending_flight' => [
                'depart'        => $addon['sending']['flightDepart'] ?? '',
                'depart_time'   => $addon['sending']['flightDepartTime'] ?? '',
                'arrival'       => $addon['sending']['flightArrival'] ?? '',
                'arrival_time'  => $addon['sending']['flightArrivalTime'] ?? '',
                'pickup_time'   => $addon['sending']['pickupTime'] ?? '',
                'dropoff_time'  => $addon['sending']['dropoffTime'] ?? '',
                'pickup_point'  => $addon['sending']['pickupPoint'] ?? '',
                'dropoff_point' => $addon['sending']['dropoffPoint'] ?? '',
                'adult_count'   => $addon['sending']['adultCount'] ?? '',
                'child_count'   => $addon['sending']['childCount'] ?? '',
                'baggage_count' => $addon['sending']['baggageCount'] ?? '',
                'pickup_note'   => $addon['sending']['pickupNote'] ?? '',
            ],
            'parking_car' => [
                'depart'        => $addon['parking']['flightDepart'] ?? '',
                'depart_time'   => $addon['parking']['flightDepartTime'] ?? '',
                'arrival'       => $addon['parking']['flightArrival'] ?? '',
                'arrival_time'  => $addon['parking']['flightArrivalTime'] ?? '',
                'pickup_time'   => $addon['parking']['pickupTime'] ?? '',
                'dropoff_time'  => $addon['parking']['dropoffTime'] ?? '',
                'pickup_point'  => $addon['parking']['pickupPoint'] ?? '',
                'dropoff_point' => $addon['parking']['dropoffPoint'] ?? '',
                'pickup_note'   => $addon['parking']['pickupNote'] ?? '',
            ],
        ], $post_id);
    }

    // Send email confirmation
    if (!empty($profile['email'])) {
        // Generate email content
        $email_content = generate_parking_booking_email_template($data, $order_code, $post_id);
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        // Send email to customer
        wp_mail($profile['email'], 'ยืนยันการจองที่จอดรถ - ' . $order_code, $email_content, $headers);
        
        // Send email to admin
        wp_mail(get_option('admin_email'), 'มีการจองที่จอดรถใหม่ - ' . $order_code, $email_content, $headers);
    }

    wp_send_json_success([
        'message' => 'บันทึกสำเร็จ',
        'post_id' => $post_id,
        'order_code' => $order_code,
        'qr_code_url' => esc_url_raw($qr),
        'input_data' => $data // <<-- ส่งข้อมูล input กลับไป
    ]);
}

// ========= Parking Booking Email Template =========

function generate_parking_booking_email_template($booking_data, $order_code, $post_id) {
    $site_name = get_bloginfo('name');
    $site_url = get_bloginfo('url');
    $admin_email = get_option('admin_email');
    
    // Extract customer information
    $full_name = isset($booking_data['profile']['fullname']) ? esc_html($booking_data['profile']['fullname']) : '';
    $email = isset($booking_data['profile']['email']) ? esc_html($booking_data['profile']['email']) : '';
    $phone = isset($booking_data['profile']['phone']) ? esc_html($booking_data['profile']['phone']) : '';
    $line_id = isset($booking_data['profile']['lineid']) ? esc_html($booking_data['profile']['lineid']) : '';
    
    // Extract booking dates and times
    $start_date = isset($booking_data['startDate']) ? esc_html($booking_data['startDate']) : '';
    $start_time = isset($booking_data['startTime']) ? esc_html($booking_data['startTime']) : '';
    $end_date = isset($booking_data['endDate']) ? esc_html($booking_data['endDate']) : '';
    $end_time = isset($booking_data['endTime']) ? esc_html($booking_data['endTime']) : '';
    
    // Extract location information
    $location_name = '';
    if (!empty($booking_data['location'])) {
        $location_term = get_term($booking_data['location'], 'location');
        if ($location_term && !is_wp_error($location_term)) {
            $location_name = $location_term->name;
        }
    }
    
    // Extract addon information
    $service_pickup = isset($booking_data['addon']['servicePickup']) ? $booking_data['addon']['servicePickup'] : false;
    $service_valet = isset($booking_data['addon']['serviceValet']) ? $booking_data['addon']['serviceValet'] : false;
    
    // Sending flight information
    $sending_depart = isset($booking_data['addon']['sending']['flightDepart']) ? esc_html($booking_data['addon']['sending']['flightDepart']) : '';
    $sending_depart_time = isset($booking_data['addon']['sending']['flightDepartTime']) ? esc_html($booking_data['addon']['sending']['flightDepartTime']) : '';
    $sending_arrival = isset($booking_data['addon']['sending']['flightArrival']) ? esc_html($booking_data['addon']['sending']['flightArrival']) : '';
    $sending_arrival_time = isset($booking_data['addon']['sending']['flightArrivalTime']) ? esc_html($booking_data['addon']['sending']['flightArrivalTime']) : '';
    $sending_pickup_time = isset($booking_data['addon']['sending']['pickupTime']) ? esc_html($booking_data['addon']['sending']['pickupTime']) : '';
    $sending_dropoff_time = isset($booking_data['addon']['sending']['dropoffTime']) ? esc_html($booking_data['addon']['sending']['dropoffTime']) : '';
    $sending_pickup_point = isset($booking_data['addon']['sending']['pickupPoint']) ? esc_html($booking_data['addon']['sending']['pickupPoint']) : '';
    $sending_dropoff_point = isset($booking_data['addon']['sending']['dropoffPoint']) ? esc_html($booking_data['addon']['sending']['dropoffPoint']) : '';
    $sending_adult_count = isset($booking_data['addon']['sending']['adultCount']) ? esc_html($booking_data['addon']['sending']['adultCount']) : '';
    $sending_child_count = isset($booking_data['addon']['sending']['childCount']) ? esc_html($booking_data['addon']['sending']['childCount']) : '';
    $sending_baggage_count = isset($booking_data['addon']['sending']['baggageCount']) ? esc_html($booking_data['addon']['sending']['baggageCount']) : '';
    $sending_pickup_note = isset($booking_data['addon']['sending']['pickupNote']) ? esc_html($booking_data['addon']['sending']['pickupNote']) : '';
    
    // Parking car information
    $parking_depart = isset($booking_data['addon']['parking']['flightDepart']) ? esc_html($booking_data['addon']['parking']['flightDepart']) : '';
    $parking_depart_time = isset($booking_data['addon']['parking']['flightDepartTime']) ? esc_html($booking_data['addon']['parking']['flightDepartTime']) : '';
    $parking_arrival = isset($booking_data['addon']['parking']['flightArrival']) ? esc_html($booking_data['addon']['parking']['flightArrival']) : '';
    $parking_arrival_time = isset($booking_data['addon']['parking']['flightArrivalTime']) ? esc_html($booking_data['addon']['parking']['flightArrivalTime']) : '';
    $parking_pickup_time = isset($booking_data['addon']['parking']['pickupTime']) ? esc_html($booking_data['addon']['parking']['pickupTime']) : '';
    $parking_dropoff_time = isset($booking_data['addon']['parking']['dropoffTime']) ? esc_html($booking_data['addon']['parking']['dropoffTime']) : '';
    $parking_pickup_point = isset($booking_data['addon']['parking']['pickupPoint']) ? esc_html($booking_data['addon']['parking']['pickupPoint']) : '';
    $parking_dropoff_point = isset($booking_data['addon']['parking']['dropoffPoint']) ? esc_html($booking_data['addon']['parking']['dropoffPoint']) : '';
    $parking_pickup_note = isset($booking_data['addon']['parking']['pickupNote']) ? esc_html($booking_data['addon']['parking']['pickupNote']) : '';
    
    $html = '<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Booking Confirmation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    @media screen and (max-width:600px){
      .container{width:100% !important}
      .px-24{padding-left:16px !important; padding-right:16px !important}
      .py-32{padding-top:20px !important; padding-bottom:20px !important}
      .stack{display:block !important; width:100% !important}
      .text-center-sm{text-align:center !important}
    }
    a[x-apple-data-detectors]{color:inherit !important; text-decoration:none !important;}
    body {margin:0; padding:0; background:#f2f4f6; font-family:Arial, Helvetica, sans-serif;}
    table {border-collapse: collapse;}
  </style>
</head>
<body style="margin:0; padding:0; background:#f2f4f6; font-family:Arial, Helvetica, sans-serif;">

  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
      <td align="center" style="padding:0 12px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="container" style="width:600px; max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td bgcolor="#29b2e2" style="padding:18px 24px; color:#ffffff;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                  <td class="text-center-sm" style="font-weight:bold; font-size:20px;">' . $site_name . '</td>
                  <td align="right" class="text-center-sm">
                    <a href="' . $site_url . '" style="color:#ffffff; text-decoration:none; font-size:14px;">OK Parking ▸</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Title -->
           <tr>
            <td align="center" class="px-24 py-32" style="padding:28px 24px;">
              <img src="' . get_stylesheet_directory_uri() . '/customize/img/logo-okpark.jpg" width="60" height="60" alt="Success" style="display:block; margin:0 auto 12px; border-radius:32px;">
              <div style="font-size:22px; font-weight:bold; color:#333;">ขอบคุณสำหรับคำสั่งซื้อของคุณ!</div>
              <div style="font-size:13px; color:#666;">เราได้รับคำสั่งซื้อแล้วและกำลังจัดส่งให้คุณ</div>
            </td>
          </tr>

          <!-- Order summary -->
          <tr>
            <td class="px-24" style="padding:0 24px 8px;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb; border-radius:6px;">
                <tr>
                  <td colspan="2" style="padding:16px; font-weight:bold; color:#111827;">สรุปรายการจอง</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">หมายเลขออเดอร์</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $order_code . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">ชื่อผู้จอง</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $full_name . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">อีเมล</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $email . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">เบอร์โทรศัพท์</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $phone . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">LINE ID</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $line_id . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">สถานที่</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $location_name . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">วันที่เริ่มต้น</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $start_date . ' ' . $start_time . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">วันที่สิ้นสุด</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $end_date . ' ' . $end_time . '</td>
                </tr>';
    
    // Add addon services if selected
    if ($service_pickup || $service_valet) {
        $html .= '<tr>
                  <td style="padding:12px 16px; color:#666;">บริการเสริม</td>
                  <td align="right" style="padding:12px 16px; color:#111;">';
        
        if ($service_pickup) {
            $html .= 'บริการรับ-ส่งสนามบิน<br>';
        }
        
        if ($service_valet) {
            $html .= 'บริการรับ-ส่งรถ (Valet)';
        }
        
        $html .= '</td>
                </tr>';
    }
    
    $html .= '              </table>
            </td>
          </tr>';
    
    // Vehicle information section
    if (!empty($booking_data['selectedParkingSpaces']) && is_array($booking_data['selectedParkingSpaces'])) {
        $html .= '
          <!-- Vehicle Information -->
          <tr>
            <td class="px-24" style="padding:8px 24px 24px;">
              <table role="presentation" width="100%">
                <tr>
                  <td class="stack" width="100%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลรถ</div>';
        
        foreach ($booking_data['selectedParkingSpaces'] as $index => $space) {
            $license_plate = isset($space['licensePlate']) ? esc_html($space['licensePlate']) : '';
            $other_type_car = isset($space['otherTypeCar']) ? esc_html($space['otherTypeCar']) : '';
            $parking_slot_id = isset($space['id']) ? esc_html($space['id']) : '';
            
            $html .= '<div style="font-size:13px; color:#555; line-height:1.5; margin-top:10px; padding-top:10px; border-top:1px solid #eee;">
                      รถ #' . ($index + 1) . '<br>
                      ทะเบียนรถ: ' . $license_plate . '<br>
                      ประเภทรถ: ' . $other_type_car . '<br>
                      ช่องจอด: ' . $parking_slot_id . '
                    </div>';
        }

        $html .= '<div style="margin-top:20px; text-align:center;">
                        <div style="font-weight:bold; color:#111; margin-bottom:10px;">QR Code สำหรับการเข้า-ออก</div>
                        <div style="display:inline-block; padding:15px; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <img style="width:200px; height:200px;" alt="QR Code" src="' . esc_url_raw($qr) . '">
                        </div>
                        <div style="font-size:12px; color:#666; margin-top:8px;">กรุณาแสดง QR Code นี้ ณ จุดเข้า-ออก</div>
                    </div>';
        
        $html .= '                  </td>
                </tr>
              </table>
            </td>
          </tr>';
    }
    
    // Sending flight information section
    if (!empty($sending_depart) || !empty($sending_arrival)) {
        $html .= '
          <!-- Sending Flight Information -->
          <tr>
            <td class="px-24" style="padding:8px 24px 24px;">
              <table role="presentation" width="100%">
                <tr>
                  <td class="stack" width="100%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลเที่ยวบินขาไป</div>
                    <div style="font-size:13px; color:#555; line-height:1.5;">';
        
        if (!empty($sending_depart)) {
            $html .= 'เที่ยวบินขาออก: ' . $sending_depart . ' เวลา ' . $sending_depart_time . '<br>';
        }
        
        if (!empty($sending_arrival)) {
            $html .= 'เที่ยวบินขาเข้า: ' . $sending_arrival . ' เวลา ' . $sending_arrival_time . '<br>';
        }
        
        if (!empty($sending_pickup_time)) {
            $html .= 'เวลารับรถ: ' . $sending_pickup_time . '<br>';
        }
        
        if (!empty($sending_dropoff_time)) {
            $html .= 'เวลาส่งรถ: ' . $sending_dropoff_time . '<br>';
        }
        
        if (!empty($sending_pickup_point)) {
            $html .= 'จุดรับรถ: ' . $sending_pickup_point . '<br>';
        }
        
        if (!empty($sending_dropoff_point)) {
            $html .= 'จุดส่งรถ: ' . $sending_dropoff_point . '<br>';
        }
        
        if (!empty($sending_adult_count)) {
            $html .= 'จำนวนผู้ใหญ่: ' . $sending_adult_count . ' คน<br>';
        }
        
        if (!empty($sending_child_count)) {
            $html .= 'จำนวนเด็ก: ' . $sending_child_count . ' คน<br>';
        }
        
        if (!empty($sending_baggage_count)) {
            $html .= 'จำนวนกระเป๋า: ' . $sending_baggage_count . ' ใบ<br>';
        }
        
        if (!empty($sending_pickup_note)) {
            $html .= 'หมายเหตุการรับรถ:<br>' . nl2br($sending_pickup_note) . '<br>';
        }
        
        $html .= '                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>';
    }
    
    // Parking car information section
    if (!empty($parking_depart) || !empty($parking_arrival)) {
        $html .= '
          <!-- Parking Car Information -->
          <tr>
            <td class="px-24" style="padding:8px 24px 24px;">
              <table role="presentation" width="100%">
                <tr>
                  <td class="stack" width="100%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลเที่ยวบินขากลับ</div>
                    <div style="font-size:13px; color:#555; line-height:1.5;">';
        
        if (!empty($parking_depart)) {
            $html .= 'เที่ยวบินขาออก: ' . $parking_depart . ' เวลา ' . $parking_depart_time . '<br>';
        }
        
        if (!empty($parking_arrival)) {
            $html .= 'เที่ยวบินขาเข้า: ' . $parking_arrival . ' เวลา ' . $parking_arrival_time . '<br>';
        }
        
        if (!empty($parking_pickup_time)) {
            $html .= 'เวลารับรถ: ' . $parking_pickup_time . '<br>';
        }
        
        if (!empty($parking_dropoff_time)) {
            $html .= 'เวลาส่งรถ: ' . $parking_dropoff_time . '<br>';
        }
        
        if (!empty($parking_pickup_point)) {
            $html .= 'จุดรับรถ: ' . $parking_pickup_point . '<br>';
        }
        
        if (!empty($parking_dropoff_point)) {
            $html .= 'จุดส่งรถ: ' . $parking_dropoff_point . '<br>';
        }
        
        if (!empty($parking_pickup_note)) {
            $html .= 'หมายเหตุการรับรถ:<br>' . nl2br($parking_pickup_note) . '<br>';
        }
        
        $html .= '                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>';
    }
    
    $html .= '
          <!-- Promo block -->
          <tr>
            <td bgcolor="#29b2e2" align="center" style="padding:28px 24px; color:#ffffff;">
              <div style="font-size:18px; font-weight:bold; margin-bottom:10px;">ขอบคุณที่ใช้บริการของเรา!</div>
              <div style="font-size:14px; margin-bottom:15px;">หากมีข้อสงสัยกรุณาติดต่อ: ' . $admin_email . '</div>
              <a href="' . $site_url . '" 
                 style="display:inline-block; background:#1c8db8; color:#ffffff; text-decoration:none; 
                        padding:12px 22px; border-radius:4px; font-weight:bold; font-size:14px;">
                กลับไปที่เว็บไซต์
              </a>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:24px; background:#f7f7f7;">
              <div style="font-size:12px; color:#666;">
                <div><strong>' . $site_name . '</strong> • ' . $admin_email . '</div>
                <div style="margin-top:8px;">
                  <a href="' . $site_url . '/preferences" style="color:#29b2e2; text-decoration:none;">ตั้งค่าการรับอีเมล</a> • 
                  <a href="' . $site_url . '/unsubscribe" style="color:#29b2e2; text-decoration:none;">ยกเลิกรับข่าวสาร</a>
                </div>
              </div>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>';
    
    return $html;
}

// Test function for parking booking email template
function test_parking_booking_email() {
    // Sample booking data for testing
    $booking_data = [
        'profile' => [
            'fullname' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '0812345678',
            'lineid' => 'johndoe_line'
        ],
        'startDate' => '2024-06-15',
        'startTime' => '09:00',
        'endDate' => '2024-06-17',
        'endTime' => '18:00',
        'location' => 15, // Sample location ID
        'addon' => [
            'servicePickup' => true,
            'serviceValet' => false,
            'sending' => [
                'flightDepart' => 'TG123',
                'flightDepartTime' => '08:30',
                'flightArrival' => 'TG456',
                'flightArrivalTime' => '10:45',
                'pickupTime' => '07:00',
                'dropoffTime' => '11:30',
                'pickupPoint' => 'Home Address',
                'dropoffPoint' => 'Airport Terminal 1',
                'adultCount' => '2',
                'childCount' => '1',
                'baggageCount' => '3',
                'pickupNote' => 'Please wait at the lobby.'
            ],
            'parking' => [
                'flightDepart' => 'TG789',
                'flightDepartTime' => '16:30',
                'flightArrival' => 'TG012',
                'flightArrivalTime' => '18:45',
                'pickupTime' => '19:30',
                'dropoffTime' => '15:00',
                'pickupPoint' => 'Airport Terminal 2',
                'dropoffPoint' => 'Home Address',
                'pickupNote' => 'Call upon arrival.'
            ]
        ],
        'selectedParkingSpaces' => [
            [
                'licensePlate' => 'AB1234',
                'otherTypeCar' => 'Sedan',
                'parking_id' => 'P-101'
            ],
            [
                'licensePlate' => 'CD5678',
                'otherTypeCar' => 'SUV',
                'parking_id' => 'P-102'
            ]
        ]
    ];
    
    // Sample order code and post ID
    $order_code = 'ORD-20240615-001';
    $post_id = 123;
    
    // Generate the email template
    $email_html = generate_parking_booking_email_template($booking_data, $order_code, $post_id);
    
    // Output the email template
    echo $email_html;
}

// ========= 2C2P Payment Gateway Integration =========

// add_action('wp_ajax_initiate_payment', 'handle_2c2p_payment');
// add_action('wp_ajax_nopriv_initiate_payment', 'handle_2c2p_payment');

// function handle_2c2p_payment() {
//     if (!isset($_POST['booking_id']) || !isset($_POST['fine'])) {
//         wp_send_json_error('Missing payment data.');
//     }

//     $booking_id = intval($_POST['booking_id']);
//     $fine = floatval($_POST['fine']);
//     $order_code = get_field('order_code', $booking_id);

//     if (!$order_code) {
//         wp_send_json_error('Invalid booking.');
//     }

//     // --- 2C2P Configuration ---
//     // IMPORTANT: Replace with your actual Merchant ID and Secret Key
//     $merchant_id = get_field('2c2p_merchant_id', 'option');
//     $secret_key = get_field('2c2p_secret_key', 'option');
//     $payment_url = 'https://demo2.2c2p.com/2C2PFrontEnd/Redirect/V3/payment'; // Use production URL for live

//     // --- Payment Details ---
//     $version = '8.5';
//     $amount = str_pad(number_format($fine, 2, '', ''), 12, '0', STR_PAD_LEFT);
//     $currency = '764'; // THB currency code
//     $desc = 'Fine for booking ' . $order_code;
//     $invoice_no = $order_code . '-' . time(); // Unique invoice number

//     // --- Response URLs ---
//     $frontend_url = home_url('/payment-complete/');
//     $backend_url = home_url('/wp-json/custom/v1/payment_callback');

//     // --- Create Hash ---
//     $string_to_hash = $version . $merchant_id . $invoice_no . $amount . $currency . $desc . $frontend_url . $backend_url;
//     $hash = hash_hmac('sha256', $string_to_hash, $secret_key, false);

//     // --- Build Redirect Form ---
//     $form_fields = [
//         'version' => $version,
//         'merchant_id' => $merchant_id,
//         'invoice_no' => $invoice_no,
//         'amount' => $amount,
//         'currency' => $currency,
//         'transaction_ref' => $booking_id, // Pass booking ID for reference
//         'desc' => $desc,
//         'frontend_url' => $frontend_url,
//         'backend_url' => $backend_url,
//         'hash' => $hash,
//     ];

//     $form_html = '<form id="payment_form" method="POST" action="' . esc_url($payment_url) . '">';
//     foreach ($form_fields as $key => $value) {
//         $form_html .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
//     }
//     $form_html .= '</form>';
//     $form_html .= '<script>document.getElementById("payment_form").submit();</script>';

//     wp_send_json_success(['redirect_form' => $form_html]);
// }

// add_action('rest_api_init', function () {
//     register_rest_route('custom/v1', '/payment_callback', array(
//         'methods' => 'POST',
//         'callback' => 'handle_2c2p_callback',
//         'permission_callback' => '__return_true',
//     ));
// });

// function handle_2c2p_callback($request) {
//     $params = $request->get_params();
//     $booking_id = intval($params['transaction_ref'] ?? 0);
//     $status = $params['payment_status'] ?? '';

//     if ($booking_id && $status === '000') { // '000' indicates success
//         // Update booking status to 'paid'
//         update_field('payment_status', 'paid', $booking_id);
//         update_field('booking_status', 'checked-out', $booking_id);
//     }

//     // Log the response for debugging
//     error_log('2C2P Callback: ' . print_r($params, true));

//     return new WP_REST_Response('Callback received.', 200);
// }

// REST API: Tabien Rod endpoints
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/tabien_rod', array(
        'methods'  => 'GET',
        'callback' => 'get_tabien_rod_items',
        'permission_callback' => '__return_true',
        'args' => array(
            'page' => array('type' => 'integer','required' => false,'sanitize_callback' => 'absint'),
            'per_page' => array('type' => 'integer','required' => false,'sanitize_callback' => 'absint'),
            'search' => array('type' => 'string','required' => false,'sanitize_callback' => 'sanitize_text_field'),
            'user_id' => array('type' => 'integer','required' => false,'sanitize_callback' => 'absint'),
            'license_plate' => array('type' => 'string','required' => false,'sanitize_callback' => 'sanitize_text_field'),
        )
    ));

    register_rest_route('custom/v1', '/tabien_rod/(?P<id>\\d+)', array(
        'methods'  => 'GET',
        'callback' => 'get_tabien_rod_item',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array('type' => 'integer','required' => true,'sanitize_callback' => 'absint'),
        )
    ));

    register_rest_route('custom/v1', '/tabien_rod', array(
        'methods'  => 'POST',
        'callback' => 'create_tabien_rod_item',
        'permission_callback' => function () { return is_user_logged_in(); },
        'args' => array(
            'title' => array('type' => 'string','required' => true,'sanitize_callback' => 'sanitize_text_field'),
            'license_plate' => array('type' => 'string','required' => true,'sanitize_callback' => 'sanitize_text_field'),
        )
    ));
});

function format_tabien_rod_post($post) {
    $post_id = is_object($post) ? $post->ID : (int)$post;
    return array(
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'tabienrod_number' => get_field('tabienrod_number', $post_id),
        'user_okpark' => get_field('user-okpark', $post_id),
        'link' => get_permalink($post_id),
    );
}

function get_tabien_rod_items($request) {
    $paged = max(1, (int)($request['page'] ?: 1));
    $per_page = (int)($request['per_page'] ?: 10);

    $meta_query = array('relation' => 'AND');
    if (!empty($request['user_id'])) {
        $meta_query[] = array(
            'key' => 'user-okpark',
            'value' => (int)$request['user_id'],
            'compare' => '=',
        );
    }
    if (!empty($request['license_plate'])) {
        $meta_query[] = array(
            'key' => 'tabienrod_number',
            'value' => sanitize_text_field($request['license_plate']),
            'compare' => '=',
        );
    }

    $args = array(
        'post_type' => 'tabien-rod',
        'post_status' => 'publish',
        'paged' => $paged,
        'posts_per_page' => $per_page,
        's' => !empty($request['search']) ? sanitize_text_field($request['search']) : '',
    );
    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    $q = new WP_Query($args);
    $items = array_map('format_tabien_rod_post', $q->posts);

    return array(
        'items' => $items,
        'pagination' => array(
            'total' => (int)$q->found_posts,
            'pages' => (int)$q->max_num_pages,
            'page'  => $paged,
            'per_page' => $per_page,
        )
    );
}

function get_tabien_rod_item($request) {
    $post_id = (int)$request['id'];
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'tabien-rod' || $post->post_status !== 'publish') {
        return new WP_Error('not_found', 'ไม่พบข้อมูล', array('status' => 404));
    }
    return format_tabien_rod_post($post_id);
}

function create_tabien_rod_item($request) {
    $title = sanitize_text_field($request['title']);
    $license = sanitize_text_field($request['license_plate']);
    $current_user = get_current_user_id();

    // Duplicate check for same user and license plate
    $existing = get_posts(array(
        'post_type' => 'tabien-rod',
        'post_status' => 'publish',
        'numberposts' => 1,
        'meta_query' => array(
            array('key' => 'tabienrod_number','value' => $license,'compare' => '='),
            array('key' => 'user-okpark','value' => $current_user,'compare' => '=')
        )
    ));
    if (!empty($existing)) {
        return new WP_Error('duplicate', 'ทะเบียนนี้ถูกบันทึกไว้แล้วสำหรับผู้ใช้งานคนนี้', array('status' => 409));
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'tabien-rod',
        'post_status' => 'publish',
        'post_title' => 'ทะเบียนรถ: ' . $license . ' - ' . $title,
    ));
    if (is_wp_error($post_id) || !$post_id) {
        return new WP_Error('create_failed', 'ไม่สามารถสร้างโพสต์ได้', array('status' => 500));
    }

    update_field('tabienrod_number', $license, $post_id);
    update_field('user-okpark', $current_user, $post_id);

    return format_tabien_rod_post($post_id);
}


if ( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Theme Settings', // The title displayed in the admin menu
        'menu_title'    => 'Theme Settings', // The menu title
        'menu_slug'     => 'theme-settings', // The slug for the options page URL
        'capability'    => 'manage_options', // User capability required to access
        'redirect'      => false,            // Don't redirect to the first sub-page
        'position'      => 60,               // Position in the admin menu (e.g., below Posts)
        'icon_url'      => 'dashicons-admin-settings', // Dashicon for the menu item
    ));

    acf_add_options_sub_page(array(
        'page_title'  => 'Price Settings',
        'menu_title'  => 'Price',
        'parent_slug' => 'theme-settings',
    ));
}




add_action( 'rest_api_init', 'register_theme_settings_rest_route' );

function register_theme_settings_rest_route() {
    register_rest_route( 'mytheme/v1', '/settings', array(
        'methods' => 'GET',
        'callback' => 'get_theme_settings_data',
        'permission_callback' => '__return_true',
    ));

    register_rest_route( 'mytheme/v1', '/settings', array(
        'methods' => 'POST',
        'callback' => 'update_theme_settings_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => array(
            'site_tagline' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'New site tagline.',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'contact_email' => array(
                'required' => false,
                'type' => 'string',
                'format' => 'email',
                'description' => 'New contact email.',
                'sanitize_callback' => 'sanitize_email',
            ),
        ),
    ));
}

// ฟังก์ชันสำหรับ GET: ดึงข้อมูล ACF field จาก options page


function get_theme_settings_data( $request ) {
    $price_carrent  = get_field('price_carrent', 'option');
    $price_parking  = get_field('price_parking', 'option');

    // เพิ่ม field อื่นๆ ได้ที่นี่
    return [
        'price_carrent' => $price_carrent,
        'price_parking' => $price_parking,
    ];
}

// ฟังก์ชันสำหรับ POST: (ตัวอย่าง, สามารถปรับแต่งให้บันทึก field ที่ต้องการ)
function update_theme_settings_data( $request ) {
    $params = $request->get_params();

    // ตัวอย่าง: อัปเดต tagline และ contact_email (ถ้ามี)
    if ( isset( $params['site_tagline'] ) ) {
        update_field('site_tagline', sanitize_text_field($params['site_tagline']), 'option');
    }
    if ( isset( $params['contact_email'] ) ) {
        update_field('contact_email', sanitize_email($params['contact_email']), 'option');
    }

    // อัปเดต price_carrent และ price_parking ถ้าต้องการ
    if ( isset( $params['price_carrent'] ) ) {
        update_field('price_carrent', $params['price_carrent'], 'option');
    }
    if ( isset( $params['price_parking'] ) ) {
        update_field('price_parking', $params['price_parking'], 'option');
    }

    return [
        'success' => true,
        'data' => get_theme_settings_data( $request ),
    ];
}





?>

