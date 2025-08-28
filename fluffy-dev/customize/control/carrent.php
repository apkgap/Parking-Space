<?php 

function enqueue_car_rental_scripts() {
    wp_enqueue_style('car-rental-style', get_template_directory_uri() . '/customize/css/carrent.css');
    wp_enqueue_script('car-rental-script', get_template_directory_uri() . '/customize/js/carrent.js', array('jquery'), null, true);
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
        'post_type'      => 'car_location',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids'
    );
    $posts = get_posts($args);
    $data = array();

    foreach ($posts as $post_id) {
        $data[] = array(
            'id'    => $post_id,
            'title' => get_the_title($post_id)
        );
    }

    return rest_ensure_response($data);
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
    if (empty($fields['personal_information']['full_name']) || 
        empty($fields['personal_information']['email']) || 
        empty($fields['personal_information']['phone_number']) ||
        empty($fields['personal_information']['line_id_whatsapp'])) {
        wp_send_json_error(['message' => 'Please fill in all required personal information']);
        return;
    }

    // Validate email format
    if (!is_email($fields['personal_information']['email'])) {
        wp_send_json_error(['message' => 'Please enter a valid email address']);
        return;
    }

    // Validate outbound trip required fields
    if (empty($fields['travel_information_outbound_trip']['depart_start_location']) ||
        empty($fields['travel_information_outbound_trip']['depart_start_location_note']) ||
        empty($fields['travel_information_outbound_trip']['depart_destination']) ||
        empty($fields['travel_information_outbound_trip']['depart_destination_note']) ||
        empty($fields['travel_information_outbound_trip']['depart_date']) ||
        empty($fields['travel_information_outbound_trip']['depart_time'])) {
        wp_send_json_error(['message' => 'Please fill in all required travel information']);
        return;
    }

    // Validate return trip fields if it's a round trip
    if (isset($fields['trip_type']) && $fields['trip_type'] == 1) {
        $return_fields = $fields['travel_information_return_trip'] ?? array();
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
    if (empty($fields['booking_details']['car_type']) ||
        empty($fields['booking_details']['adult_count']) ||
        empty($fields['booking_details']['child_count'])) {
        wp_send_json_error(['message' => 'Please fill in all required booking details']);
        return;
    }

    // Validate flight information
    if (empty($fields['flight_information']['arrival_flight_no']) ||
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
            'full_name' => sanitize_text_field($fields['personal_information']['full_name']),
            'email' => sanitize_email($fields['personal_information']['email']),
            'line_id_whatsapp' => sanitize_text_field($fields['personal_information']['line_id_whatsapp']),
            'phone_number' => sanitize_text_field($fields['personal_information']['phone_number'])
        );
    }
    
    // Selected Package Information (from service-carrent)
    // รับข้อมูลจาก hidden inputs ที่ส่งมาจาก form
    if (isset($_POST['selected_package_id']) && !empty($_POST['selected_package_id'])) {
        $sanitized['selected_package'] = array(
            'package_id' => intval($_POST['selected_package_id']),
            'route' => sanitize_text_field($_POST['selected_package_route']),
            'car_type' => sanitize_text_field($_POST['selected_package_car_type']),
            'price' => sanitize_text_field($_POST['selected_package_price'])
        );
    }
    
    // Travel Information - Outbound
    if (isset($fields['travel_information_outbound_trip'])) {
        $sanitized['travel_information_outbound_trip'] = array(
            'depart_start_location' => sanitize_text_field($fields['travel_information_outbound_trip']['depart_start_location']),
            'depart_start_location_note' => sanitize_textarea_field($fields['travel_information_outbound_trip']['depart_start_location_note']),
            'depart_destination' => sanitize_text_field($fields['travel_information_outbound_trip']['depart_destination']),
            'depart_destination_note' => sanitize_textarea_field($fields['travel_information_outbound_trip']['depart_destination_note']),
            'depart_date' => sanitize_text_field($fields['travel_information_outbound_trip']['depart_date']),
            'depart_time' => sanitize_text_field($fields['travel_information_outbound_trip']['depart_time'])
        );
    }
    
    // Trip Type
    if (isset($fields['trip_type'])) {
        $sanitized['trip_type'] = intval($fields['trip_type']);
    }
    
    // Travel Information - Return
    if (isset($fields['travel_information_return_trip'])) {
        $sanitized['travel_information_return_trip'] = array(
            'return_start_location' => sanitize_text_field($fields['travel_information_return_trip']['return_start_location']),
            'return_start_location_note' => sanitize_textarea_field($fields['travel_information_return_trip']['return_start_location_note']),
            'return_destination' => sanitize_text_field($fields['travel_information_return_trip']['return_destination']),
            'return_destination_note' => sanitize_textarea_field($fields['travel_information_return_trip']['return_destination_note']),
            'return_date' => sanitize_text_field($fields['travel_information_return_trip']['return_date']),
            'return_time' => sanitize_text_field($fields['travel_information_return_trip']['return_time'])
        );
    }
    
    // Booking Details
    if (isset($fields['booking_details'])) {
        $sanitized['booking_details'] = array(
            'car_type' => sanitize_text_field($fields['booking_details']['car_type']),
            'adult_count' => intval($fields['booking_details']['adult_count']),
            'child_count' => intval($fields['booking_details']['child_count']),
            'remark_booking' => sanitize_textarea_field($fields['booking_details']['remark_booking'])
        );
    }
    
    // Flight Information
    if (isset($fields['flight_information'])) {
        $sanitized['flight_information'] = array(
            'arrival_flight_no' => sanitize_text_field($fields['flight_information']['arrival_flight_no']),
            'arrival_flight_time' => sanitize_text_field($fields['flight_information']['arrival_flight_time']),
            'departure_flight_no' => sanitize_text_field($fields['flight_information']['departure_flight_no']),
            'departure_flight_time' => sanitize_text_field($fields['flight_information']['departure_flight_time']),
            'discount_code' => sanitize_text_field($fields['flight_information']['discount_code']),
            'remark_flight' => sanitize_textarea_field($fields['flight_information']['remark_flight'])
        );
    }
    
    return $sanitized;
}

function send_car_rental_confirmation_email($fields, $booking_ref, $post_id) {
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
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 800px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { text-align: center; padding: 20px 0; border-bottom: 3px solid #007cba; margin-bottom: 30px; }
            .header h1 { color: #007cba; margin: 0; font-size: 28px; }
            .booking-ref { background-color: #007cba; color: white; padding: 15px; text-align: center; border-radius: 5px; margin-bottom: 30px; font-size: 18px; font-weight: bold; }
            .section { margin-bottom: 30px; }
            .section-title { background-color: #f8f9fa; padding: 12px 15px; border-left: 4px solid #007cba; font-weight: bold; font-size: 16px; margin-bottom: 15px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f8f9fa; font-weight: bold; color: #555; width: 30%; }
            td { color: #333; }
            tr:hover { background-color: #f8f9fa; }
            .trip-section { background-color: #f0f8ff; padding: 20px; border-radius: 5px; margin: 15px 0; }
            .footer { text-align: center; padding: 20px 0; border-top: 2px solid #eee; margin-top: 40px; color: #666; }
            .status-badge { display: inline-block; padding: 5px 10px; background-color: #28a745; color: white; border-radius: 3px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . $site_name . '</h1>
                <p>ยืนยันการจองรถเช่า</p>
            </div>
            
            <div class="booking-ref">
                รหัสการจอง: ' . $booking_ref . '
                <br><span class="status-badge">ยืนยันแล้ว</span>
            </div>
            
            <div class="section">
                <div class="section-title">ข้อมูลส่วนตัว</div>
                <table>
                    <tr><th>ชื่อ - นามสกุล</th><td>' . esc_html($fields['personal_information']['full_name']) . '</td></tr>
                    <tr><th>อีเมล</th><td>' . esc_html($fields['personal_information']['email']) . '</td></tr>
                    <tr><th>เบอร์โทรศัพท์</th><td>' . esc_html($fields['personal_information']['phone_number']) . '</td></tr>
                    <tr><th>Line ID / WhatsApp</th><td>' . esc_html($fields['personal_information']['line_id_whatsapp']) . '</td></tr>
                </table>
            </div>';
    
    // Selected Package Information (if available)
    if (isset($fields['selected_package']) && !empty($fields['selected_package'])) {
        $html .= '
            <div class="section">
                <div class="section-title">แพ็คเกจที่เลือก</div>
                <table>
                    <tr><th>เส้นทาง</th><td>' . esc_html($fields['selected_package']['route']) . '</td></tr>
                    <tr><th>ประเภทรถ</th><td>' . esc_html($fields['selected_package']['car_type']) . '</td></tr>
                    <tr><th>ราคา</th><td>' . esc_html($fields['selected_package']['price']) . '</td></tr>
                </table>
            </div>';
    }
    
    // Outbound Trip
    $html .= '
            <div class="section">
                <div class="section-title">ข้อมูลการเดินทาง - เที่ยวขาไป</div>
                <div class="trip-section">
                    <table>
                        <tr><th>สถานที่ต้นทาง</th><td>' . esc_html($fields['travel_information_outbound_trip']['depart_start_location']) . '</td></tr>
                        <tr><th>รายละเอียดต้นทาง</th><td>' . esc_html($fields['travel_information_outbound_trip']['depart_start_location_note']) . '</td></tr>
                        <tr><th>สถานที่ปลายทาง</th><td>' . esc_html($fields['travel_information_outbound_trip']['depart_destination']) . '</td></tr>
                        <tr><th>รายละเอียดปลายทาง</th><td>' . esc_html($fields['travel_information_outbound_trip']['depart_destination_note']) . '</td></tr>
                        <tr><th>วันที่เดินทาง</th><td>' . esc_html($fields['travel_information_outbound_trip']['depart_date']) . '</td></tr>
                        <tr><th>เวลานัดหมาย</th><td>' . esc_html($fields['travel_information_outbound_trip']['depart_time']) . '</td></tr>
                    </table>
                </div>
            </div>';
    
    // Return Trip (if round trip)
    if (isset($fields['trip_type']) && $fields['trip_type'] == 1 && isset($fields['travel_information_return_trip'])) {
        $html .= '
            <div class="section">
                <div class="section-title">ข้อมูลการเดินทาง - เที่ยวขากลับ</div>
                <div class="trip-section">
                    <table>
                        <tr><th>สถานที่ต้นทาง</th><td>' . esc_html($fields['travel_information_return_trip']['return_start_location']) . '</td></tr>
                        <tr><th>รายละเอียดต้นทาง</th><td>' . esc_html($fields['travel_information_return_trip']['return_start_location_note']) . '</td></tr>
                        <tr><th>สถานที่ปลายทาง</th><td>' . esc_html($fields['travel_information_return_trip']['return_destination']) . '</td></tr>
                        <tr><th>รายละเอียดปลายทาง</th><td>' . esc_html($fields['travel_information_return_trip']['return_destination_note']) . '</td></tr>
                        <tr><th>วันที่เดินทาง</th><td>' . esc_html($fields['travel_information_return_trip']['return_date']) . '</td></tr>
                        <tr><th>เวลานัดหมาย</th><td>' . esc_html($fields['travel_information_return_trip']['return_time']) . '</td></tr>
                    </table>
                </div>
            </div>';
    }
    
    // Booking Details
    $html .= '
            <div class="section">
                <div class="section-title">รายละเอียดการจอง</div>
                <table>
                    <tr><th>ประเภทรถ</th><td>' . esc_html($fields['booking_details']['car_type']) . '</td></tr>
                    <tr><th>จำนวนผู้ใหญ่</th><td>' . esc_html($fields['booking_details']['adult_count']) . ' คน</td></tr>
                    <tr><th>จำนวนเด็ก</th><td>' . esc_html($fields['booking_details']['child_count']) . ' คน</td></tr>';
    
    if (!empty($fields['booking_details']['remark_booking'])) {
        $html .= '<tr><th>หมายเหตุ</th><td>' . esc_html($fields['booking_details']['remark_booking']) . '</td></tr>';
    }
    
    $html .= '
                </table>
            </div>';
    
    // Flight Information
    $html .= '
            <div class="section">
                <div class="section-title">ข้อมูลเที่ยวบิน</div>
                <table>
                    <tr><th>เที่ยวบินขาเข้า</th><td>' . esc_html($fields['flight_information']['arrival_flight_no']) . '</td></tr>
                    <tr><th>เวลาขาเข้า</th><td>' . esc_html($fields['flight_information']['arrival_flight_time']) . '</td></tr>
                    <tr><th>เที่ยวบินขาออก</th><td>' . esc_html($fields['flight_information']['departure_flight_no']) . '</td></tr>
                    <tr><th>เวลาขาออก</th><td>' . esc_html($fields['flight_information']['departure_flight_time']) . '</td></tr>';
    
    if (!empty($fields['flight_information']['discount_code'])) {
        $html .= '<tr><th>โค้ดส่วนลด</th><td>' . esc_html($fields['flight_information']['discount_code']) . '</td></tr>';
    }
    
    if (!empty($fields['flight_information']['remark_flight'])) {
        $html .= '<tr><th>หมายเหตุ</th><td>' . nl2br(esc_html($fields['flight_information']['remark_flight'])) . '</td></tr>';
    }
    
    $html .= '
                </table>
            </div>
            
            <div class="footer">
                <p><strong>ขอบคุณที่ใช้บริการของเรา!</strong></p>
                <p>หากมีข้อสงสัยกรุณาติดต่อ: ' . $admin_email . '</p>
                <p><a href="' . $site_url . '">' . $site_name . '</a></p>
                <p><small>อีเมลนี้ส่งอัตโนมัติ กรุณาอย่าตอบกลับ</small></p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}


