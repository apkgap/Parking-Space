<?php 

function enqueue_car_rental_scripts() {
    if (is_page('carrent')) {
        wp_enqueue_style('car-rental-style', get_template_directory_uri() . '/customize/css/carrent.css');
        wp_enqueue_script('car-rental-script', get_template_directory_uri() . '/customize/js/carrent.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_car_rental_scripts'); 

add_action('rest_api_init', function () {
    register_rest_route('carrent/', '/car-locations', array(
        'methods'  => 'GET',
        'callback' => 'get_car_location_list',
        'permission_callback' => '__return_true'
    ));
});

function get_car_location_list() {
    $args = array(
        'post_type'      => 'package_rent',
        'post_status'    => 'publish',
        'posts_per_page' => -1
    );
    $posts = get_posts($args);
    $locations = array();

    foreach ($posts as $post) {
        $origin = get_field('origin', $post->ID);
        $destination = get_field('destination', $post->ID);
        
        // Add origin locations
        if (!empty($origin)) {
            $origin_locations = explode(',', $origin);
            foreach ($origin_locations as $location) {
                $location = trim($location);
                if (!empty($location) && !in_array($location, array_column($locations, 'title'))) {
                    $locations[] = array(
                        'id' => sanitize_title($location),
                        'title' => $location
                    );
                }
            }
        }
        
        // Add destination locations
        if (!empty($destination)) {
            $destination_locations = explode(',', $destination);
            foreach ($destination_locations as $location) {
                $location = trim($location);
                if (!empty($location) && !in_array($location, array_column($locations, 'title'))) {
                    $locations[] = array(
                        'id' => sanitize_title($location),
                        'title' => $location
                    );
                }
            }
        }
    }

    // Sort locations alphabetically
    usort($locations, function($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    return rest_ensure_response($locations);
}

function handle_ajax_carrent() {
    wp_enqueue_script(
        'ajax-carrent',
        get_template_directory_uri() . '/customize/js/ajax-carrent.js',
        array('jquery'),
        '1.0.0',
        null, true
    );

    wp_localize_script(
        'ajax-carrent',
        'ajax_carrent_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('carrent_ajax_nonce'),
        )
    );
}
add_action('wp_enqueue_scripts', 'handle_ajax_carrent');


// PHP Backend - Improved AJAX Handler
add_action('wp_ajax_insert_car_rent_acf', 'ajax_insert_car_rent_acf');
add_action('wp_ajax_nopriv_insert_car_rent_acf', 'ajax_insert_car_rent_acf');

function ajax_insert_car_rent_acf() {
    // Security check
    if (!check_ajax_referer('carrent_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    // Validate required fields
    $fields = isset($_POST['fields']) ? $_POST['fields'] : array();
    
    if (empty($fields)) {
        wp_send_json_error(['message' => 'No data provided']);
        return;
    }

    // Validate required personal information
    if (!isset($fields['personal_information']['full_name']) || 
        !isset($fields['personal_information']['email']) || 
        !isset($fields['personal_information']['line_id_whatsapp']) ||
        !isset($fields['personal_information']['phone_number']) ||
        empty($fields['personal_information']['full_name']) || 
        empty($fields['personal_information']['email']) || 
        empty($fields['personal_information']['line_id_whatsapp']) ||
        empty($fields['personal_information']['phone_number'])) {
        wp_send_json_error(['message' => 'Please fill in all required personal information']);
        return;
    }

    // Validate email format
    if (!is_email($fields['personal_information']['email'])) {
        wp_send_json_error(['message' => 'Please enter a valid email address']);
        return;
    }

    // Validate outbound trip required fields
    $outbound_trip = isset($fields['travel_information_outbound_trip']) ? $fields['travel_information_outbound_trip'] : array();
    if (empty($outbound_trip['depart_start_location']) ||
        empty($outbound_trip['depart_start_location_note']) ||
        empty($outbound_trip['depart_destination']) ||
        empty($outbound_trip['depart_destination_note']) ||
        empty($outbound_trip['depart_date']) ||
        empty($outbound_trip['depart_time'])) {
        wp_send_json_error(['message' => 'Please fill in all required travel information']);
        return;
    }

    // Validate return trip fields if it's a round trip
    if (isset($fields['trip_type']) && $fields['trip_type'] == 1) {
        $return_fields = isset($fields['travel_information_return_trip']) ? $fields['travel_information_return_trip'] : array();
        if (empty($return_fields['return_start_location']) ||
            empty($return_fields['return_start_location_note']) ||
            empty($return_fields['return_destination']) ||
            empty($return_fields['return_destination_note']) ||
            empty($return_fields['return_date']) ||
            empty($return_fields['return_time'])) {
            wp_send_json_error(['message' => 'Please fill in all required return trip information']);
            return;
        }
    }

    // Validate booking details
    if (!isset($fields['booking_details']['car_type']) ||
        !isset($fields['booking_details']['adult_count']) ||
        !isset($fields['booking_details']['child_count']) ||
        empty($fields['booking_details']['car_type']) ||
        empty($fields['booking_details']['adult_count']) ||
        empty($fields['booking_details']['child_count'])) {
        wp_send_json_error(['message' => 'Please fill in all required booking details']);
        return;
    }

    // Validate flight information
    if (!isset($fields['flight_information']['arrival_flight_no']) ||
        !isset($fields['flight_information']['arrival_flight_time']) ||
        !isset($fields['flight_information']['departure_flight_no']) ||
        !isset($fields['flight_information']['departure_flight_time']) ||
        empty($fields['flight_information']['arrival_flight_no']) ||
        empty($fields['flight_information']['arrival_flight_time']) ||
        empty($fields['flight_information']['departure_flight_no']) ||
        empty($fields['flight_information']['departure_flight_time'])) {
        wp_send_json_error(['message' => 'Please fill in all required flight information']);
        return;
    }

    // Sanitize and prepare data
    $sanitized_fields = sanitize_car_rental_fields($fields);
    
    // Generate unique booking reference
    $booking_ref = 'CR' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Create post
    $post_data = array(
        'post_type'   => 'car_rentals',
        'post_status' => 'publish',
        'post_title'  => sprintf(
            'Car Rental Booking - %s (%s)', 
            $sanitized_fields['personal_information']['full_name'],
            $booking_ref
        ),
        'post_content' => sprintf(
            'Booking Reference: %s\nCustomer: %s\nEmail: %s\nPhone: %s\nLine ID/WhatsApp: %s',
            $booking_ref,
            $sanitized_fields['personal_information']['full_name'],
            $sanitized_fields['personal_information']['email'],
            $sanitized_fields['personal_information']['phone_number'],
            $sanitized_fields['personal_information']['line_id_whatsapp']
        )
    );

    $post_id = wp_insert_post($post_data);

    if (!$post_id || is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Unable to create booking. Please try again.']);
        return;
    }

    // Save ACF fields
    if (function_exists('update_field')) {
        foreach ($sanitized_fields as $field_name => $value) {
            $result = update_field($field_name, $value, $post_id);
            if (!$result) {
                error_log("Failed to save ACF field: {$field_name} for post: {$post_id}");
            }
        }
        
        // Save booking reference as separate field (if you have this field in ACF)
        // update_field('booking_reference', $booking_ref, $post_id);
        
        // Save booking timestamp (if you have this field in ACF)
        // update_field('booking_timestamp', current_time('mysql'), $post_id);
    }

    // Log successful booking
    error_log("Car rental booking created: Post ID {$post_id}, Reference: {$booking_ref}");

    // Send confirmation email
    send_car_rental_confirmation_email($sanitized_fields, $booking_ref, $post_id);

    wp_send_json_success([
        'message' => 'Booking created successfully!',
        'post_id' => $post_id,
        'booking_reference' => $booking_ref
    ]);
}

function sanitize_car_rental_fields($fields) {
    $sanitized = array();
    
    // Personal Information
    if (isset($fields['personal_information'])) {
        $sanitized['personal_information'] = array(
            'full_name' => isset($fields['personal_information']['full_name']) ? sanitize_text_field($fields['personal_information']['full_name']) : '',
            'email' => isset($fields['personal_information']['email']) ? sanitize_email($fields['personal_information']['email']) : '',
            'line_id_whatsapp' => isset($fields['personal_information']['line_id_whatsapp']) ? sanitize_text_field($fields['personal_information']['line_id_whatsapp']) : '',
            'phone_number' => isset($fields['personal_information']['phone_number']) ? sanitize_text_field($fields['personal_information']['phone_number']) : ''
        );
    }
    
    // Selected Package Information (from service-carrent)
    // รับข้อมูลจาก hidden inputs ที่ส่งมาจาก form
    if (isset($_POST['selected_package_id']) && !empty($_POST['selected_package_id'])) {
        $sanitized['selected_package'] = array(
            'package_id' => intval($_POST['selected_package_id']),
            'route' => isset($_POST['selected_package_route']) ? sanitize_text_field($_POST['selected_package_route']) : '',
            'car_type' => isset($_POST['selected_package_car_type']) ? sanitize_text_field($_POST['selected_package_car_type']) : '',
            'price' => isset($_POST['selected_package_price']) ? sanitize_text_field($_POST['selected_package_price']) : ''
        );
    }
    
    // Travel Information - Outbound
    if (isset($fields['travel_information_outbound_trip'])) {
        $sanitized['travel_information_outbound_trip'] = array(
            'depart_start_location' => isset($fields['travel_information_outbound_trip']['depart_start_location']) ? sanitize_text_field($fields['travel_information_outbound_trip']['depart_start_location']) : '',
            'depart_start_location_note' => isset($fields['travel_information_outbound_trip']['depart_start_location_note']) ? sanitize_textarea_field($fields['travel_information_outbound_trip']['depart_start_location_note']) : '',
            'depart_destination' => isset($fields['travel_information_outbound_trip']['depart_destination']) ? sanitize_text_field($fields['travel_information_outbound_trip']['depart_destination']) : '',
            'depart_destination_note' => isset($fields['travel_information_outbound_trip']['depart_destination_note']) ? sanitize_textarea_field($fields['travel_information_outbound_trip']['depart_destination_note']) : '',
            'depart_date' => isset($fields['travel_information_outbound_trip']['depart_date']) ? sanitize_text_field($fields['travel_information_outbound_trip']['depart_date']) : '',
            'depart_time' => isset($fields['travel_information_outbound_trip']['depart_time']) ? sanitize_text_field($fields['travel_information_outbound_trip']['depart_time']) : ''
        );
    }
    
    // Trip Type
    if (isset($fields['trip_type'])) {
        $sanitized['trip_type'] = intval($fields['trip_type']);
    }
    
    // Travel Information - Return
    if (isset($fields['travel_information_return_trip'])) {
        $sanitized['travel_information_return_trip'] = array(
            'return_start_location' => isset($fields['travel_information_return_trip']['return_start_location']) ? sanitize_text_field($fields['travel_information_return_trip']['return_start_location']) : '',
            'return_start_location_note' => isset($fields['travel_information_return_trip']['return_start_location_note']) ? sanitize_textarea_field($fields['travel_information_return_trip']['return_start_location_note']) : '',
            'return_destination' => isset($fields['travel_information_return_trip']['return_destination']) ? sanitize_text_field($fields['travel_information_return_trip']['return_destination']) : '',
            'return_destination_note' => isset($fields['travel_information_return_trip']['return_destination_note']) ? sanitize_textarea_field($fields['travel_information_return_trip']['return_destination_note']) : '',
            'return_date' => isset($fields['travel_information_return_trip']['return_date']) ? sanitize_text_field($fields['travel_information_return_trip']['return_date']) : '',
            'return_time' => isset($fields['travel_information_return_trip']['return_time']) ? sanitize_text_field($fields['travel_information_return_trip']['return_time']) : ''
        );
    }
    
    // Booking Details
    if (isset($fields['booking_details'])) {
        $sanitized['booking_details'] = array(
            'car_type' => isset($fields['booking_details']['car_type']) ? sanitize_text_field($fields['booking_details']['car_type']) : '',
            'adult_count' => isset($fields['booking_details']['adult_count']) ? intval($fields['booking_details']['adult_count']) : 0,
            'child_count' => isset($fields['booking_details']['child_count']) ? intval($fields['booking_details']['child_count']) : 0,
            'remark_booking' => isset($fields['booking_details']['remark_booking']) ? sanitize_textarea_field($fields['booking_details']['remark_booking']) : ''
        );
    }
    
    // Flight Information
    if (isset($fields['flight_information'])) {
        $sanitized['flight_information'] = array(
            'arrival_flight_no' => isset($fields['flight_information']['arrival_flight_no']) ? sanitize_text_field($fields['flight_information']['arrival_flight_no']) : '',
            'arrival_flight_time' => isset($fields['flight_information']['arrival_flight_time']) ? sanitize_text_field($fields['flight_information']['arrival_flight_time']) : '',
            'departure_flight_no' => isset($fields['flight_information']['departure_flight_no']) ? sanitize_text_field($fields['flight_information']['departure_flight_no']) : '',
            'departure_flight_time' => isset($fields['flight_information']['departure_flight_time']) ? sanitize_text_field($fields['flight_information']['departure_flight_time']) : '',
            'discount_code' => isset($fields['flight_information']['discount_code']) ? sanitize_text_field($fields['flight_information']['discount_code']) : '',
            'remark_flight' => isset($fields['flight_information']['remark_flight']) ? sanitize_textarea_field($fields['flight_information']['remark_flight']) : ''
        );
    }
    
    return $sanitized;
}

function send_car_rental_confirmation_email($fields, $booking_ref, $post_id) {
    // Check if required fields exist before accessing them
    if (!isset($fields['personal_information']['email']) || !isset($fields['personal_information']['full_name'])) {
        error_log('Missing required personal information for email sending');
        return;
    }
    
    $customer_email = $fields['personal_information']['email'];
    $customer_name = $fields['personal_information']['full_name'];
    
    // Admin email (ใส่อีเมลของ admin ที่ต้องการรับการแจ้งเตือน)
    $admin_email = get_option('admin_email');
    
    $subject = "การยืนยันการจองรถเช่า - {$booking_ref}";
    
    // Create beautiful HTML email template
    $message = generate_car_rental_email_template($fields, $booking_ref, $post_id);
    
    // Email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>'
    );
    
    // Send email to customer
    $customer_sent = wp_mail($customer_email, $subject, $message, $headers);
    
    // Send email to admin
    $admin_subject = "การจองรถเช่าใหม่ - {$booking_ref}";
    $admin_sent = wp_mail($admin_email, $admin_subject, $message, $headers);
    
    // Log email results
    if ($customer_sent) {
        error_log("Confirmation email sent to customer: {$customer_email}");
    } else {
        error_log("Failed to send confirmation email to customer: {$customer_email}");
    }
    
    if ($admin_sent) {
        error_log("Notification email sent to admin: {$admin_email}");
    } else {
        error_log("Failed to send notification email to admin: {$admin_email}");
    }
}

function generate_car_rental_email_template($fields, $booking_ref, $post_id) {
    $site_name = get_bloginfo('name');
    $site_url = get_bloginfo('url');
    $admin_email = get_option('admin_email');
    
    // Extract all data fields
    $full_name = isset($fields['personal_information']['full_name']) ? esc_html($fields['personal_information']['full_name']) : '';
    $email = isset($fields['personal_information']['email']) ? esc_html($fields['personal_information']['email']) : '';
    $phone = isset($fields['personal_information']['phone_number']) ? esc_html($fields['personal_information']['phone_number']) : '';
    $line_id = isset($fields['personal_information']['line_id_whatsapp']) ? esc_html($fields['personal_information']['line_id_whatsapp']) : '';
    
    // Package information
    $route = isset($fields['selected_package']['route']) ? esc_html($fields['selected_package']['route']) : '';
    $car_type_package = isset($fields['selected_package']['car_type']) ? esc_html($fields['selected_package']['car_type']) : '';
    $price = isset($fields['selected_package']['price']) ? esc_html($fields['selected_package']['price']) : '';
    
    // Outbound trip
    $depart_start_location = isset($fields['travel_information_outbound_trip']['depart_start_location']) ? esc_html($fields['travel_information_outbound_trip']['depart_start_location']) : '';
    $depart_start_location_note = isset($fields['travel_information_outbound_trip']['depart_start_location_note']) ? esc_html($fields['travel_information_outbound_trip']['depart_start_location_note']) : '';
    $depart_destination = isset($fields['travel_information_outbound_trip']['depart_destination']) ? esc_html($fields['travel_information_outbound_trip']['depart_destination']) : '';
    $depart_destination_note = isset($fields['travel_information_outbound_trip']['depart_destination_note']) ? esc_html($fields['travel_information_outbound_trip']['depart_destination_note']) : '';
    $depart_date = isset($fields['travel_information_outbound_trip']['depart_date']) ? esc_html($fields['travel_information_outbound_trip']['depart_date']) : '';
    $depart_time = isset($fields['travel_information_outbound_trip']['depart_time']) ? esc_html($fields['travel_information_outbound_trip']['depart_time']) : '';
    
    // Return trip
    $is_round_trip = isset($fields['trip_type']) && $fields['trip_type'] == 1;
    $return_start_location = isset($fields['travel_information_return_trip']['return_start_location']) ? esc_html($fields['travel_information_return_trip']['return_start_location']) : '';
    $return_start_location_note = isset($fields['travel_information_return_trip']['return_start_location_note']) ? esc_html($fields['travel_information_return_trip']['return_start_location_note']) : '';
    $return_destination = isset($fields['travel_information_return_trip']['return_destination']) ? esc_html($fields['travel_information_return_trip']['return_destination']) : '';
    $return_destination_note = isset($fields['travel_information_return_trip']['return_destination_note']) ? esc_html($fields['travel_information_return_trip']['return_destination_note']) : '';
    $return_date = isset($fields['travel_information_return_trip']['return_date']) ? esc_html($fields['travel_information_return_trip']['return_date']) : '';
    $return_time = isset($fields['travel_information_return_trip']['return_time']) ? esc_html($fields['travel_information_return_trip']['return_time']) : '';
    
    // Booking details
    $car_type = isset($fields['booking_details']['car_type']) ? esc_html($fields['booking_details']['car_type']) : '';
    $adult_count = isset($fields['booking_details']['adult_count']) ? esc_html($fields['booking_details']['adult_count']) : '';
    $child_count = isset($fields['booking_details']['child_count']) ? esc_html($fields['booking_details']['child_count']) : '';
    $remark_booking = isset($fields['booking_details']['remark_booking']) ? esc_html($fields['booking_details']['remark_booking']) : '';
    
    // Flight information
    $arrival_flight_no = isset($fields['flight_information']['arrival_flight_no']) ? esc_html($fields['flight_information']['arrival_flight_no']) : '';
    $arrival_flight_time = isset($fields['flight_information']['arrival_flight_time']) ? esc_html($fields['flight_information']['arrival_flight_time']) : '';
    $departure_flight_no = isset($fields['flight_information']['departure_flight_no']) ? esc_html($fields['flight_information']['departure_flight_no']) : '';
    $departure_flight_time = isset($fields['flight_information']['departure_flight_time']) ? esc_html($fields['flight_information']['departure_flight_time']) : '';
    $discount_code = isset($fields['flight_information']['discount_code']) ? esc_html($fields['flight_information']['discount_code']) : '';
    $remark_flight = isset($fields['flight_information']['remark_flight']) ? nl2br(esc_html($fields['flight_information']['remark_flight'])) : '';
    
    $html = '<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Order Confirmation</title>
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
                  <td colspan="2" style="padding:16px; font-weight:bold; color:#111827;">สรุปรายการสั่งซื้อ</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">หมายเลขออเดอร์</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $booking_ref . '</td>
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
                  <td style="padding:12px 16px; color:#666;">เส้นทาง</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $route . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">ประเภทรถ</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $car_type . '</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">จำนวนผู้ใหญ่</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $adult_count . ' คน</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; color:#666;">จำนวนเด็ก</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $child_count . ' คน</td>
                </tr>';
    
    if (!empty($price)) {
        $html .= '<tr>
                  <td style="padding:12px 16px; color:#666;">ราคา</td>
                  <td align="right" style="padding:12px 16px; color:#111;">' . $price . '</td>
                </tr>';
    }
    
    $html .= '              </table>
            </td>
          </tr>


          <!-- Address + ETA -->
          <tr>
            <td class="px-24" style="padding:8px 24px 24px;">
              <table role="presentation" width="100%">
                <tr>
                  <td class="stack" width="50%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลการเดินทาง - เที่ยวขาไป</div>
                    <div style="font-size:13px; color:#555; line-height:1.5;">
                      ต้นทาง: ' . $depart_start_location . '<br>
                      รายละเอียดต้นทาง: ' . $depart_start_location_note . '<br>
                      ปลายทาง: ' . $depart_destination . '<br>
                      รายละเอียดปลายทาง: ' . $depart_destination_note . '<br>
                      วันที่: ' . $depart_date . '<br>
                      เวลา: ' . $depart_time . '
                    </div>
                  </td>
                  <td class="stack" width="5%">&nbsp;</td>';
    
    if ($is_round_trip) {
        $html .= '<td class="stack" width="45%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลการเดินทาง - เที่ยวขากลับ</div>
                    <div style="font-size:13px; color:#555; line-height:1.5;">
                      ต้นทาง: ' . $return_start_location . '<br>
                      รายละเอียดต้นทาง: ' . $return_start_location_note . '<br>
                      ปลายทาง: ' . $return_destination . '<br>
                      รายละเอียดปลายทาง: ' . $return_destination_note . '<br>
                      วันที่: ' . $return_date . '<br>
                      เวลา: ' . $return_time . '
                    </div>
                  </td>';
    } else {
        $html .= '<td class="stack" width="45%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลเพิ่มเติม</div>
                    <div style="font-size:13px; color:#555; line-height:1.5;">
                      หมายเหตุการจอง:<br>
                      ' . $remark_booking . '
                    </div>
                  </td>';
    }
    
    $html .= '                </tr>
              </table>
            </td>
          </tr>
          
          <!-- Flight Information -->
          <tr>
            <td class="px-24" style="padding:8px 24px 24px;">
              <table role="presentation" width="100%">
                <tr>
                  <td class="stack" width="100%" valign="top" style="padding:16px; border:1px solid #e5e7eb; border-radius:6px;">
                    <div style="font-weight:bold; color:#111; margin-bottom:6px;">ข้อมูลเที่ยวบิน</div>
                    <div style="font-size:13px; color:#555; line-height:1.5;">
                      เที่ยวบินขาเข้า: ' . $arrival_flight_no . ' เวลา ' . $arrival_flight_time . '<br>
                      เที่ยวบินขาออก: ' . $departure_flight_no . ' เวลา ' . $departure_flight_time . '<br>';
    
    if (!empty($discount_code)) {
        $html .= 'โค้ดส่วนลด: ' . $discount_code . '<br>';
    }
    
    if (!empty($remark_flight)) {
        $html .= 'หมายเหตุเที่ยวบิน:<br>' . $remark_flight . '';
    }
    
    $html .= '                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>


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


