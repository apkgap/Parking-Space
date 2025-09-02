<?php
/**
 * Utilities Module
 * Contains utility functions and helpers for the customize functionality
 */

/**
 * Enqueue parking scripts with AJAX URL
 */
function enqueue_parking_scripts() {
    wp_enqueue_script('space-parking', get_template_directory_uri() . '/customize/js/space-parking.js', array('jquery'), null, true);
    
    wp_localize_script('space-parking', 'ajaxurl_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_parking_scripts');

/**
 * Get formatted date for display
 */
function get_formatted_date($date, $format = 'Y-m-d') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Get formatted time for display
 */
function get_formatted_time($time, $format = 'H:i') {
    if (empty($time)) {
        return '';
    }
    
    $timestamp = is_numeric($time) ? $time : strtotime($time);
    return date($format, $timestamp);
}

/**
 * Sanitize and validate booking data
 */
function sanitize_booking_data($data) {
    $sanitized = array();
    
    if (isset($data['service_type'])) {
        $sanitized['service_type'] = sanitize_text_field($data['service_type']);
    }
    
    if (isset($data['date'])) {
        $sanitized['date'] = sanitize_text_field($data['date']);
    }
    
    if (isset($data['time'])) {
        $sanitized['time'] = sanitize_text_field($data['time']);
    }
    
    if (isset($data['location'])) {
        $sanitized['location'] = sanitize_text_field($data['location']);
    }
    
    if (isset($data['notes'])) {
        $sanitized['notes'] = sanitize_textarea_field($data['notes']);
    }
    
    return $sanitized;
}

/**
 * Generate unique order code
 */
function generate_order_code($prefix = 'ORD') {
    $timestamp = time();
    $random = wp_rand(1000, 9999);
    return $prefix . '-' . $timestamp . '-' . $random;
}

/**
 * Check if user can access booking feature
 */
function can_user_make_booking($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    // Allow administrators and service customers
    $allowed_roles = array('administrator', 'service-customer');
    $user_roles = $user->roles;
    
    return !empty(array_intersect($allowed_roles, $user_roles));
}

/**
 * Get booking status display text
 */
function get_booking_status_text($status) {
    $statuses = array(
        'pending' => 'รอดำเนินการ',
        'confirmed' => 'ยืนยันแล้ว',
        'in_progress' => 'กำลังดำเนินการ',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก'
    );
    
    return isset($statuses[$status]) ? $statuses[$status] : $status;
}

/**
 * Get vehicle type display name
 */
function get_vehicle_type_name($vehicle_type_id) {
    if (!$vehicle_type_id) {
        return '';
    }
    
    $vehicle_type = get_post($vehicle_type_id);
    return $vehicle_type ? $vehicle_type->post_title : '';
}

/**
 * Format currency for display
 */
function format_currency($amount, $currency = 'THB') {
    if (!is_numeric($amount)) {
        return $amount;
    }
    
    $formatted = number_format($amount, 2);
    
    switch ($currency) {
        case 'THB':
            return $formatted . ' ฿';
        case 'USD':
            return '$' . $formatted;
        default:
            return $formatted . ' ' . $currency;
    }
}

/**
 * Log booking activity
 */
function log_booking_activity($booking_id, $activity, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $log_entry = array(
        'booking_id' => $booking_id,
        'activity' => $activity,
        'user_id' => $user_id,
        'timestamp' => current_time('mysql'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
    );
    
    // Store in post meta or custom table
    add_post_meta($booking_id, '_booking_activity_log', $log_entry);
}
?>
