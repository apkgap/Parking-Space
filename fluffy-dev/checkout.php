<?php
/**
 * Template Name: Checkout
 *
 * This template displays the checkout details, including any fines for late returns.
 */

get_header(); // Include theme header
?>

<div class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
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
                            <h2 class="card-title text-3xl font-bold">Scan to Check-out</h2>
                        </div>
                    </div>
                    <div class="qr-scanner-body card-body items-center text-center p-8">
                        <p class="text-base-content/70 mb-4">Position the customer's QR code inside the frame to scan for checkout.</p>
                        <div id="qr-reader" class="qr-reader-widget w-full max-w-xs rounded-lg overflow-hidden border-4 border-primary shadow-inner"></div>
                        <div id="qr-reader-results" class="qr-reader-status text-error font-semibold h-6 mt-4"></div>
                    </div>
                </div>

                <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
                <script>
                    function onScanSuccess(decodedText, decodedResult) {
                        console.log(`Code matched = ${decodedText}`, decodedResult);
                        html5QrcodeScanner.clear();
                        window.location.href = `<?php echo home_url('/checkout/'); ?>?order_code=${decodedText}`;
                    }

                    function onScanFailure(error) {
                        // console.warn(`Code scan error = ${error}`);
                    }

                    let html5QrcodeScanner = new Html5QrcodeScanner(
                        "qr-reader", { fps: 10, qrbox: { width: 250, height: 250 } },
                        /* verbose= */
                        false);

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
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key' => 'booking_status',
                            'value' => 'checked-in', // Only query for checked-in bookings
                            'compare' => 'IN'
                        ]
                    ]
                ];

                // Prioritize lookup by Post ID if 'id' is present
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $args['p'] = intval($_GET['id']);
                } // Otherwise, look up by order code if 'code' or 'order_code' is present
                else if (isset($_GET['code']) && !empty($_GET['code'])) {
                    $args['meta_query'][] = [
                        'key' => 'order_code',
                        'value' => sanitize_text_field($_GET['code']),
                        'compare' => '='
                    ];
                } else if (isset($_GET['order_code']) && !empty($_GET['order_code'])) {
                    $args['meta_query'][] = [
                        'key' => 'order_code',
                        'value' => sanitize_text_field($_GET['order_code']),
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

                // Ensure we are only querying for bookings that are already checked-in
                $args['meta_query'][] = [
                    'key' => 'booking_status',
                    'value' => 'checked-in',
                    'compare' => '='
                ];

                $latest_booking_query = new WP_Query($args);

                if ($latest_booking_query->have_posts()) {
                    while ($latest_booking_query->have_posts()) {
                        $latest_booking_query->the_post();

                        // Fetch all required fields
                        $order_code = get_field('order_code');
                        $customer_name = get_field('fullname_park');
                        $customer_tel = get_field('phone_park');
                        $vehicle_info = get_field('vehicle_info');

                        $start_date_str = get_field('start_date');
                        $end_date_str = get_field('end_date');
                        $booking_status = get_field('booking_status');
                        $check_in_time = get_field('tracking_check_in');
                        $check_out_time = get_field('tracking_check_out');
                        $qr_code_data = get_field('qr_code_data');
                        $addon_services = get_field('addon_services');

                        // Fine calculation logic
                        $end_date = new DateTime($end_date_str);
                        $now = new DateTime();
                        $is_late = $now > $end_date;
                        $fine = 0;
                        $overdue_minutes = 0;

                        if ($is_late && $booking_status !== 'checked-out') {
                            $interval = $now->diff($end_date);
                            $overdue_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                            $fine = $overdue_minutes; // 1 baht per minute
                        }

                        // Display success message on redirect
                        if (isset($_GET['checkout_status']) && $_GET['checkout_status'] === 'success') {
                            echo '<div class="alert alert-success max-w-2xl mx-auto mb-4"><span class="font-semibold">Checkout confirmed successfully!</span></div>';
                        }
        ?>
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

                        <div class="card bg-base-100 shadow-xl max-w-4xl mx-auto overflow-hidden border border-base-200">
                            <div class="card-body p-6">
                                <!-- Status Header -->
                                <div class="mb-8 text-center">
                                    <h2 class="text-2xl font-bold text-base-content mb-2">Checkout Details</h2>
                                    <p class="text-base-content/70">Order Code: <?php echo esc_html($order_code); ?></p>
                                </div>

                                <!-- Status Bar -->
                                <div class="w-full max-w-4xl mx-auto mb-10">
                                    <ul class="steps w-full">
                                        <?php foreach ($steps as $step) : ?>
                                            <li <?php echo $step['status']; ?>><?php echo esc_html($step['label']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <?php
                                $badge_details = get_booking_status_badge_details($booking_status, $check_in_time, $check_out_time);
                                ?>
                                <div class="current-status-display text-center mb-8">
                                    <div class="status-badge badge <?php echo esc_attr($badge_details['badge_class']); ?> badge-lg gap-2 p-6 w-full">
                                        <?php echo $badge_details['badge_icon']; ?>
                                        <span><?php echo esc_html($badge_details['badge_text']); ?></span>
                                    </div>
                                    <?php echo $badge_details['status_time_html']; ?>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Customer Info -->
                                    <div class="card bg-base-200/30 rounded-xl p-5 shadow-sm">
                                        <h3 class="text-lg font-semibold text-base-content mb-4 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Customer Information
                                        </h3>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-base-content/70">Name:</span>
                                                <span class="font-semibold text-base-content"><?php echo esc_html($customer_name ?: 'N/A'); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-base-content/70">Telephone:</span>
                                                <span class="font-semibold text-base-content"><?php echo esc_html($customer_tel ?: 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Booking Details -->
                                    <div class="card bg-base-200/30 rounded-xl p-5 shadow-sm">
                                        <h3 class="text-lg font-semibold text-base-content mb-4 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Booking Details
                                        </h3>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-base-content/70">Check-in Date:</span>
                                                <span class="font-mono text-sm text-base-content"><?php echo $start_date_str ? esc_html(date('d/m/Y H:i', strtotime($start_date_str))) : 'N/A'; ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-base-content/70">Expected Checkout:</span>
                                                <span class="font-mono text-sm text-base-content"><?php echo $end_date_str ? esc_html(date('d/m/Y H:i', strtotime($end_date_str))) : 'N/A'; ?></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-base-content/70">Actual Check-in:</span>
                                                <span class="font-mono text-sm text-base-content"><?php echo $check_in_time ? esc_html(date('d/m/Y H:i', strtotime($check_in_time))) : 'N/A'; ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Vehicle Info -->
                                    <div class="card bg-base-200/30 rounded-xl p-5 shadow-sm">
                                        <h3 class="text-lg font-semibold text-base-content mb-4 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 01_5.25 3h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Vehicle Information
                                        </h3>
                                        <div class="space-y-3">
                                            <?php if ($vehicle_info && is_array($vehicle_info)) : ?>
                                                <?php foreach ($vehicle_info as $index => $vehicle) : ?>
                                                    <?php 
                                                        $license_plate = isset($vehicle['license_plate']) ? $vehicle['license_plate'] : 'N/A';
                                                        $vehicle_type = 'N/A';
                                                        if (isset($vehicle['vehicle_type_relation']) && is_object($vehicle['vehicle_type_relation'])) {
                                                            $vehicle_type = $vehicle['vehicle_type_relation']->post_title;
                                                        }
                                                    ?>
                                                    <div class="vehicle-item-<?php echo $index; ?>">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-medium text-base-content/70">License Plate:</span>
                                                            <span class="font-mono text-lg font-bold tracking-wider text-base-content"><?php echo esc_html($license_plate); ?></span>
                                                        </div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-medium text-base-content/70">Vehicle Type:</span>
                                                            <span class="font-semibold text-base-content"><?php echo esc_html($vehicle_type); ?></span>
                                                        </div>
                                                    </div>
                                                    <?php if ($index < count($vehicle_info) - 1) : ?>
                                                        <div class="divider my-2"></div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <p>No vehicle information available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Addon Services -->
                                    <?php if ($addon_services) : ?>
                                        <div class="card bg-base-200/30 rounded-xl p-5 shadow-sm">
                                            <h3 class="text-lg font-semibold text-base-content mb-4 flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                                Addon Services
                                            </h3>
                                            <ul class="list-disc list-inside space-y-1">
                                                <?php foreach ($addon_services as $service) : ?>
                                                    <li class="text-base-content/90"><?php echo esc_html($service['service_name']); ?> - <?php echo esc_html($service['service_price']); ?> THB</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Fine Details -->
                                <?php if ($is_late && $booking_status !== 'checked-out') : ?>
                                    <div class="card bg-error/10 border border-error/20 rounded-xl p-6 mt-8">
                                        <h3 class="text-xl font-bold text-error mb-4 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Late Checkout Details
                                        </h3>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center py-2 border-b border-base-200">
                                                <span class="font-medium text-base-content/70">Expected Checkout:</span>
                                                <span class="font-mono"><?php echo esc_html(date('d/m/Y H:i', strtotime($end_date_str))); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-b border-base-200">
                                                <span class="font-medium text-base-content/70">Actual Checkout:</span>
                                                <span class="font-mono"><?php echo $now->format('d/m/Y H:i'); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-b border-base-200">
                                                <span class="font-medium text-base-content/70">Overdue Time:</span>
                                                <span class="font-mono"><?php echo floor($overdue_minutes / 60); ?> hrs <?php echo $overdue_minutes % 60; ?> mins</span>
                                            </div>
                                            <div class="flex justify-between items-center pt-4">
                                                <span class="text-lg font-bold text-error">Fine to Pay:</span>
                                                <span class="text-2xl font-bold text-error"><?php echo number_format($fine); ?> THB</span>
                                            </div>
                                            <p class="text-sm text-base-content/50 text-center mt-2">(Fine is calculated at 1 THB per minute)</p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Checkout Action -->
                                <?php if ($booking_status !== 'checked-out') : ?>
                                    <?php if ($booking_status === 'checked-in') : ?>
                                        <div class="checkout-action-box mt-10 pt-6 border-t border-base-200 text-center">
                                            <button type="button" 
                                                    class="check-out-button btn btn-accent btn-lg px-8 py-4 text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 gap-3 rounded-xl"
                                                    data-booking-id="<?php echo get_the_ID(); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Confirm Check-out
                                            </button>
                                            <p class="text-sm text-base-content/60 mt-3">This action cannot be undone</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="checkout-action-box mt-10 pt-6 border-t border-base-200 text-center">
                                        <div class="inline-flex items-center gap-2 px-6 py-3 bg-success/10 text-success rounded-full font-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Booking has been checked out
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
        <?php
                    }
                    wp_reset_postdata();
                } else {
                    // If no booking is found
                    echo '<div role="alert" class="alert alert-warning max-w-lg mx-auto"><svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span>No checked-in booking found with the provided details.</span></div>';
                }
            }
        } else {
            // If user is not logged in
            echo '<div role="alert" class="alert alert-error max-w-lg mx-auto"><svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span><strong>Please Log In:</strong> You must be logged in to access this page. <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="link">Click here to log in.</a></span></div>';
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


?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const payFineBtn = document.getElementById('pay-fine-btn');
        if (payFineBtn) {
            payFineBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const bookingId = this.dataset.bookingId;
                const fine = this.dataset.fine;

                // Show loading indicator if you have one
                document.body.insertAdjacentHTML('beforeend', '<div id="loading-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;"><span class="loading loading-spinner loading-lg"></span></div>');

                const formData = new FormData();
                formData.append('action', 'initiate_payment');
                formData.append('booking_id', bookingId);
                formData.append('fine', fine);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.body.insertAdjacentHTML('beforeend', data.data.redirect_form);
                        } else {
                            alert('Error: ' + data.data);
                            document.getElementById('loading-overlay').remove();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred. Please try again.');
                        document.getElementById('loading-overlay').remove();
                    });
            });
        }
    });
</script>
