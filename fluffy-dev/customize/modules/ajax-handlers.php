<?php
/**
 * AJAX Handlers Module
 * Contains all AJAX request handlers for customize functionality
 */

/**
 * Handle parking space selection AJAX
 */
function handle_parking_space_selection() {
    check_ajax_referer('parking_nonce', 'nonce');
    
    $space_id = sanitize_text_field($_POST['space_id']);
    $date = sanitize_text_field($_POST['date']);
    
    // Process parking space selection logic here
    $response = array(
        'success' => true,
        'space_id' => $space_id,
        'date' => $date,
        'message' => 'Parking space selected successfully'
    );
    
    wp_send_json($response);
}
add_action('wp_ajax_parking_space_selection', 'handle_parking_space_selection');
add_action('wp_ajax_nopriv_parking_space_selection', 'handle_parking_space_selection');

/**
 * Handle booking form submission
 */
function handle_booking_submission() {
    check_ajax_referer('booking_nonce', 'nonce');
    
    $booking_data = array(
        'service_type' => sanitize_text_field($_POST['service_type']),
        'date' => sanitize_text_field($_POST['date']),
        'time' => sanitize_text_field($_POST['time']),
        'user_id' => get_current_user_id()
    );
    
    // Process booking submission logic here
    $response = array(
        'success' => true,
        'booking_id' => wp_generate_uuid4(),
        'message' => 'Booking submitted successfully'
    );
    
    wp_send_json($response);
}
add_action('wp_ajax_booking_submission', 'handle_booking_submission');
add_action('wp_ajax_nopriv_booking_submission', 'handle_booking_submission');

/**
 * Handle user profile updates
 */
function handle_profile_update() {
    check_ajax_referer('profile_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }
    
    $user_id = get_current_user_id();
    $profile_data = array(
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        'phone' => sanitize_text_field($_POST['phone'])
    );
    
    // Update user meta
    foreach ($profile_data as $key => $value) {
        update_user_meta($user_id, $key, $value);
    }
    
    wp_send_json_success('Profile updated successfully');
}
add_action('wp_ajax_profile_update', 'handle_profile_update');

/**
 * Handle service availability check
 */
function handle_service_availability() {
    check_ajax_referer('availability_nonce', 'nonce');
    
    $service_type = sanitize_text_field($_POST['service_type']);
    $date = sanitize_text_field($_POST['date']);
    $time = sanitize_text_field($_POST['time']);
    
    // Check availability logic here
    $available = true; // Placeholder logic
    
    $response = array(
        'available' => $available,
        'message' => $available ? 'Service available' : 'Service not available'
    );
    
    wp_send_json($response);
}
add_action('wp_ajax_service_availability', 'handle_service_availability');
add_action('wp_ajax_nopriv_service_availability', 'handle_service_availability');
?>
