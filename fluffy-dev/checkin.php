<?php
/**
 * Template Name: Check-in
 *
 * This template displays the check-in details and QR code for a booking.
 */

get_header(); // Include theme header
?>

<div class="checkin-page-wrapper min-h-screen bg-base-200 flex items-center justify-center px-[1.5em] py-[2em] sm:py-[5em]">
    <div class="checkin-container container mx-auto">

        <?php
        if (is_user_logged_in()) {

            // If there is no query string in the URL, show the QR Scanner
            if (empty($_SERVER['QUERY_STRING'])) {
                ?>
                <div class="qr-scanner-card card bg-base-100 shadow-xl max-w-lg mx-auto overflow-hidden border-2 border-base-300">
                    <div class="qr-scanner-header p-6 bg-primary text-primary-content">
                        <div class="qr-scanner-header-content flex items-center justify-center gap-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h-1m-1-6h.01M5 12H4m1 6.01V17m-1-5h.01M12 5a7 7 0 100 14 7 7 0 000-14z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <h2 class="card-title text-3xl font-bold">Scan to Check-in</h2>
                        </div>
                    </div>
                    <div class="qr-scanner-body card-body items-center text-center p-8">
                        <p class="text-base-content/70 mb-4">Position the customer's QR code inside the frame to scan.</p>
                        <div id="qr-reader" class="qr-reader-widget w-full max-w-xs rounded-lg overflow-hidden border-4 border-primary shadow-inner"></div>
                        <div id="qr-reader-results" class="qr-reader-status text-error font-semibold h-6 mt-4"></div>
                    </div>
                </div>

                <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
                <script>
                    function onScanSuccess(decodedText, decodedResult) {
                        // --- handle success --- 
                        console.log(`Code matched = ${decodedText}`, decodedResult);
                        // Stop scanning
                        html5QrcodeScanner.clear();
                        // Redirect to the check-in page with the order code
                        window.location.href = `<?php echo home_url('/check-in/'); ?>?order_code=${decodedText}`;
                    }

                    function onScanFailure(error) {
                        // --- handle failure --- 
                        // console.warn(`Code scan error = ${error}`);
                        const resultElement = document.getElementById('qr-reader-results');
                        // resultElement.textContent = 'Scanning...'; // You can add feedback here
                    }

                    let html5QrcodeScanner = new Html5QrcodeScanner(
                        "qr-reader", 
                        { fps: 10, qrbox: {width: 250, height: 250} }, 
                        /* verbose= */ false);
                    
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                </script>
                <?php
            } else {
                // If a query string exists, attempt to find and show booking details
                $current_user = wp_get_current_user();

                // Base query arguments
                $args = [
                    'post_type' => 'parking_booking',
                    'posts_per_page' => 1,
                ];

                // Prioritize lookup by Post ID if 'id' is present
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $args['p'] = intval($_GET['id']);
                } 
                if (isset($_GET['code']) && !empty($_GET['code'])) {
                    $args['meta_query'][] = [
                        'key' => 'order_code',
                        'value' => sanitize_text_field($_GET['code']),
                        'compare' => '='
                    ];
                }
                
                // Define roles that can view all bookings
                $power_roles = ['administrator', 'service-customer'];
                $is_power_user = false;
                foreach ($power_roles as $role) {
                    if (in_array($role, $current_user->roles)) {
                        $is_power_user = true;
                        break;
                    }
                }

                // Users who are not power users can only see their own bookings
                if (!$is_power_user) {
                    $args['author'] = $current_user->ID;
                }

                // Ensure we are only querying for bookings that are pending check-in
                // $args['meta_query'][] = [
                //     'key' => 'booking_status',
                //     'value' => 'pending',
                //     'compare' => '='
                // ];

                $latest_booking_query = new WP_Query($args);

                if ($latest_booking_query->have_posts()) {
                    while ($latest_booking_query->have_posts()) {
                        $latest_booking_query->the_post();


                        // Get all ACF fields
                        $order_code = get_field('order_code');
                        $customer_name = get_field('fullname_park');
                        $phone_park = get_field('phone_park');
                        $vehicle_info = get_field('vehicle_info');
                        $start_date = get_field('start_date');
                        $start_time = get_field('start_time');
                        $end_date = get_field('end_date');
                        $end_time = get_field('end_time');
                        $qr_code_url = get_field('qr_code_url');
                        $booking_status = get_field('tracking_check_status');                
                        $check_in_time = get_field('tracking_check_in');
                        $check_out_time = get_field('tracking_check_out');
                        $addon_info = get_field('addon_information');
                        $late_checkout_fine = get_field('late_checkout_fine');
                        $location_term = get_field('location_park');
                        $location_name = $location_term ? get_term($location_term)->name : '-';

                        ?>

                        <!-- Booking Details Card -->
                        <?php
                        // Define the steps for the status bar
                        $steps = [
                            'confirmed' => ['label' => 'ยืนยันการจอง', 'status' => ''],
                            'checked-in' => ['label' => 'เช็คอิน', 'status' => ''],
                            'checked-out' => ['label' => 'เช็คเอาท์', 'status' => ''],
                        ];

                        // Determine the current step index
                        $current_step_index = array_search($booking_status, array_keys($steps));

                        // Update status for each step
                        $step_keys = array_keys($steps);
                        foreach ($step_keys as $index => $key) {
                            if ($index < $current_step_index) {
                                $steps[$key]['status'] = 'data-content="✓" class="step step-primary"'; // Completed
                            } elseif ($index == $current_step_index) {
                                $steps[$key]['status'] = 'class="step step-primary"'; // Active
                            } else {
                                $steps[$key]['status'] = 'class="step"'; // Pending
                            }
                        }
                        ?>
                        <div class="w-full max-w-4xl mx-auto mb-8">
                            <ul class="steps w-full">
                                <?php foreach ($steps as $step) : ?>
                                    <li <?php echo $step['status']; ?>><?php echo esc_html($step['label']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="booking-details-card card bg-base-100 shadow-xl max-w-2xl mx-auto">
                            <div class="card-body">

                                <?php
                                $badge_details = get_booking_status_badge_details($booking_status, $check_in_time, $check_out_time);
                                // Override for pending check-in status which is specific to this page
                                if ($booking_status === 'confirmed') {
                                    $badge_details['badge_class'] = 'badge-warning';
                                    $badge_details['badge_text'] = 'รอเช็คอิน';
                                    $badge_details['badge_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                                    $badge_details['status_time_html'] = '';
                                }
                                ?>
                                <div class="current-status-display text-center mb-8">
                                    <div class="status-badge badge <?php echo esc_attr($badge_details['badge_class']); ?> badge-lg gap-2 p-6 w-full">
                                        <?php echo $badge_details['badge_icon']; ?>
                                        <span><?php echo esc_html($badge_details['badge_text']); ?></span>
                                    </div>
                                    <?php echo $badge_details['status_time_html']; ?>
                                </div>

                                <?php if ($late_checkout_fine && $booking_status === 'checked-out') : ?>
                                <div class="late-fine-display text-center mt-4 p-4 bg-error/10 rounded-lg">
                                    <p class="text-error text-lg font-semibold">ค่าปรับเช็คเอาท์ล่าช้า</p>
                                    <p class="text-error text-4xl font-bold tracking-tight"><?php echo number_format($late_checkout_fine, 2); ?> THB</p>
                                </div>
                                <?php endif; ?>

                                <div class="details-card-header text-center mb-6">
                                    <h1 class="text-3xl font-bold text-primary">Check-in Details</h1>
                                    <p class="text-base-content/70">Please present this QR code for scanning.</p>
                                </div>

                                <div class="details-qrcode-wrapper flex justify-center mb-8">
                                    <div class="details-qrcode-container p-4 bg-white border-4 border-primary rounded-2xl shadow-lg w-full max-w-[280px]">
                                        <?php if ($qr_code_url) : ?>
                                            <div class="qrcode-image-wrapper aspect-square box-qrcode">
                                                <img src="<?php echo esc_url($qr_code_url); ?>" alt="QR Code" style="width:200px; height:200px;">
                                            </div>
                                        <?php else : ?>
                                            <div class="qrcode-placeholder aspect-square flex items-center justify-center bg-base-200 rounded-lg">
                                                <p class="text-base-content/50">QR Code not available.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="details-content-section space-y-6">
                                    <!-- Booking Details -->
                                    <div class="info-block booking-info p-4 rounded-lg bg-base-200/50">
                                        <h3 class="font-bold text-lg mb-3 text-primary">ข้อมูลการจอง</h3>
                                        <div class="info-block-content space-y-2 text-base">
                                            <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">หมายเลขจอง:</strong><span class="col-span-2 font-mono"><?php echo esc_html($order_code); ?></span></div>
                                            <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">สถานที่:</strong><span class="col-span-2"><?php echo esc_html($location_name); ?></span></div>
                                            <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">เริ่มจอง:</strong><span class="col-span-2"><?php echo esc_html(date('d/m/Y', strtotime($start_date)) . ' ' . $start_time); ?></span></div>
                                            <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">สิ้นสุด:</strong><span class="col-span-2"><?php echo esc_html(date('d/m/Y', strtotime($end_date)) . ' ' . $end_time); ?></span></div>
                                        </div>
                                    </div>

                                    <!-- Customer & Vehicle Details -->
                                    <div class="info-grid-container grid md:grid-cols-2 gap-6">
                                        <!-- Customer Info -->
                                        <div class="info-block customer-info p-4 rounded-lg bg-base-200/50">
                                            <h3 class="font-bold text-lg mb-3 text-primary">ข้อมูลลูกค้า</h3>
                                            <div class="info-block-content space-y-2 text-base">
                                                <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">ชื่อ:</strong><span class="col-span-2"><?php echo esc_html($customer_name); ?></span></div>
                                                <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">โทรศัพท์:</strong><span class="col-span-2"><?php echo esc_html($phone_park); ?></span></div>
                                            </div>
                                        </div>

                                        <!-- Vehicle Info -->
                                        <div class="info-block vehicle-info p-4 rounded-lg bg-base-200/50">
                                            <h3 class="font-bold text-lg mb-3 text-primary">ข้อมูลยานพาหนะ</h3>
                                            <div class="info-block-content space-y-2 text-base">
                                                <?php if ($vehicle_info) : ?>
                                                    <?php foreach ($vehicle_info as $index => $vehicle) : ?>
                                                        <div class="vehicle-item-details <?php echo $index > 0 ? 'mt-4 pt-4 border-t border-base-300' : ''; ?>">
                                                            <div class="space-y-2">
                                                                <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">ทะเบียน:</strong><span class="col-span-2 font-semibold"><?php echo esc_html($vehicle['license_plate']); ?></span></div>
                                                                <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">ประเภท:</strong><span class="col-span-2"><?php echo esc_html($vehicle['vehicle_type_relation']->post_title); ?></span></div>
                                                                <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70">ช่องจอด:</strong><span class="col-span-2"><?php echo esc_html( $vehicle['parking_slot']); ?></span></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else : ?>
                                                    <p class="text-sm text-base-content/50">ไม่พบข้อมูลยานพาหนะ</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Addon Services -->
                                    <?php
                                    $services = [];

                                    // Check for Airport Pickup Service from the provided data structure.
                                    if (!empty($addon_info['service_pickup']) && isset($addon_info['sending_flight'])) {
                                        $pickup_details = $addon_info['sending_flight'];
                                        $details = [
                                            'เที่ยวบิน (ขาไป)' => $pickup_details['depart'] ?: '-',
                                            'เวลาเครื่องออก (ขาไป)' => $pickup_details['depart_time'] ?: '-',
                                            'เวลานัดรับ' => $pickup_details['pickup_time'] ?: '-',
                                            'จุดนัดรับ' => $pickup_details['pickup_point'] ?: '-',
                                            'เที่ยวบิน (ขากลับ)' => $pickup_details['arrival'] ?: '-',
                                            'เวลาเครื่องออก (ขากลับ)' => $pickup_details['arrival_time'] ?: '-',
                                            'เวลานัดส่ง' => $pickup_details['dropoff_time'] ?: '-',
                                            'จุดนัดส่ง' => $pickup_details['dropoff_point'] ?: '-',
                                            'จำนวนผู้ใหญ่' => $pickup_details['adult_count'] ?: '0',
                                            'จำนวนเด็ก' => $pickup_details['child_count'] ?: '0',
                                            'จำนวนสัมภาระ' => $pickup_details['baggage_count'] ?: '0',
                                            'หมายเหตุ' => $pickup_details['pickup_note'] ?: '-'
                                        ];

                                        $services[] = [
                                            'title' => 'บริการรับ-ส่งสนามบิน',
                                            'details' => array_filter($details, function($value) { return $value !== '' && $value !== '-'; })
                                        ];
                                    }

                                    // Check for Valet Service.
                                    if (!empty($addon_info['service_valet'])) {
                                        $services[] = [
                                            'title' => 'บริการรับจอด-รับส่งรถสนามบิน',
                                            'details' => [] // No details for valet in the provided structure yet
                                        ];
                                    }
                                    ?>
                                    <?php if (!empty($services)) : ?>
                                    <div class="info-block addon-info p-4 rounded-lg bg-base-200/50">
                                        <h3 class="font-bold text-lg mb-3 text-primary">บริการเสริม</h3>
                                        <div class="info-block-content text-base">
                                            <?php foreach ($services as $index => $service) : ?>
                                                <div class="service-item-details <?php echo $index > 0 ? 'mt-4 pt-4 border-t border-base-300' : ''; ?>">
                                                    <div class="flex items-center gap-2 font-semibold mb-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        <span><?php echo esc_html($service['title']); ?></span>
                                                    </div>
                                                    <?php if (!empty($service['details'])) : ?>
                                                        <div class="pl-7 space-y-1 text-sm">
                                                            <?php foreach ($service['details'] as $label => $value) : ?>
                                                                <div class="info-grid-row grid grid-cols-3 gap-2"><strong class="col-span-1 text-base-content/70"><?php echo esc_html($label); ?>:</strong><span class="col-span-2"><?php echo esc_html($value); ?></span></div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Status & Action -->
                                    <div class="section-divider divider pt-4"></div>
                                    <div class="status-action-section text-center space-y-4">
                                        <div class="status-display-wrapper flex justify-between align-center">
                                            <h3 class="font-semibold text-lg mb-2">สถานะการ Check-in</h3>
                                            <div class="status-badge badge <?php echo esc_attr($badge_details['badge_class']); ?> badge-lg gap-2 p-4">
                                                <?php echo $badge_details['badge_icon']; ?>
                                                <span><?php echo esc_html($badge_details['badge_text']); ?></span>
                                            </div>
                                            <?php echo $badge_details['status_time_html']; ?>
                                        </div>

                                        <?php 
                                        if ($booking_status == 'confirmed') : ?>
                                            <div class="checkin-action-box">
                                                <button type="button" 
                                                        class="check-in-button btn btn-primary btn-wide btn-lg shadow-lg hover:shadow-xl transition-shadow duration-300 gap-2"
                                                        data-booking-id="<?php echo get_the_ID(); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                                    Confirm Check-in
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($booking_status == 'checked-in') : ?>
                                            <div class="checkout-action-box mt-4">
                                                <a href="<?php echo home_url('/check-out/?order_code=' . $order_code); ?>" class="btn btn-accent btn-wide btn-lg shadow-lg hover:shadow-xl transition-shadow duration-300 gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H3m14 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                                    Go to Check-out
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <?php
                    } // end while
                    wp_reset_postdata();
                } else {
                    // No posts found or user does not have permission
                    ?>
                    <div role="alert" class="alert alert-error max-w-lg mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><strong>Error:</strong> Booking not found or you do not have permission to view it.</span>
                    </div>
                    <?php
                } // end if have_posts
            } // end if/else for order_code
        } else {
            // If user is not logged in
            ?>
                        <div role="alert" class="login-required-alert alert alert-warning max-w-lg mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" class="checkin-box" /></svg>
                            <span class="checkin-box"><strong class="checkin-box">Access Denied:</strong> You must be logged in to view this page.</span>
                        </div>
            <?php
        }
        ?>

    </div>
</div>

<div id="success-alert" class="fixed inset-0 z-[1000] flex items-center justify-center bg-transparent hidden">
    <div class="absolute inset-0 bg-opacity-40 backdrop-blur-sm cursor-pointer"></div>
    <div class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full p-8 transform transition-all opacity-0 translate-y-4 flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2 text-center">สำเร็จ!</h3>
        <p id="success-message" class="text-gray-600 text-center mb-6">บันทึกข้อมูลเรียบร้อยแล้ว</p>
        <button id="success-ok" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white rounded-md font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
            ตกลง
        </button>
    </div>
</div>

<div id="error-alert" class="fixed inset-0 z-[1000] flex items-center justify-center bg-transparent hidden">
    <div class="absolute inset-0 bg-opacity-40 backdrop-blur-sm cursor-pointer"></div>
    <div class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full p-8 transform transition-all opacity-0 translate-y-4 flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-red-100 flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2 text-center">ขออภัย!</h3>
        <p id="error-message" class="text-gray-600 text-center mb-6">เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง</p>
        <button id="error-retry" class="w-full py-3 bg-red-500 hover:bg-red-600 text-white rounded-md font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
            ลองใหม่
        </button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="fixed z-[9998] flex items-center justify-center inset-0 bg-opacity-40 backdrop-blur-sm cursor-pointer" style="display: none;">
    <div class="md-box bg-base-100 shadow-xl rounded-lg p-6 max-w-sm mx-auto">
        <h3 id="confirmation-title" class="font-bold text-lg">ยืนยันการดำเนินการ</h3>
        <p id="confirmation-message" class="py-4">คุณแน่ใจหรือไม่?</p>
        <div class="modal-action flex justify-end space-x-2">
            <button id="confirm-cancel-btn" class="btn">ยกเลิก</button>
            <button id="confirm-ok-btn" class="btn btn-primary">ยืนยัน</button>
        </div>
    </div>
</div>

<?php
get_footer(); // Include theme footer


