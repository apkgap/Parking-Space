<?php
/**
 * Booking API Endpoints Module
 * Handles parking and car rental booking API endpoints
 */

/**
 * Register parking booking endpoints
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/parking_booking', array(
        'methods' => 'GET',
        'callback' => 'get_parking_booking_data',
        'permission_callback' => '__return_true',
    ));
    
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

    if (!empty($request['search'])) {
        $args['s'] = sanitize_text_field($request['search']);
    }

    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $data[] = format_booking_data();
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

    if (!empty($request['search'])) {
        $args['s'] = sanitize_text_field($request['search']);
    }

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
            $data[] = format_booking_data();
        }
        wp_reset_postdata();
    }

    $response = array(
        'data' => $data,
        'pagination' => array(
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page
        )
    );

    return rest_ensure_response($response);
}

/**
 * Format booking data for API response
 */
function format_booking_data() {
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

    return array(
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
?>
