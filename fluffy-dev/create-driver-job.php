<?php
/**
 * Template Name: Create Driver Job
 * Description: A page template for creating a new driver job assignment.
 */

// Access control: only logged-in admins can view this page
if ( ! is_user_logged_in() ) {
    // Redirect to login and then back to this page after login
    auth_redirect();
    exit;
}

if ( ! current_user_can('administrator') ) {
    wp_die(__('คุณไม่มีสิทธิ์เข้าถึงหน้านี้', 'text-domain'), __('Forbidden', 'text-domain'), array('response' => 403));
}

get_header(); ?>

<div class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .box-loading {
            z-index: 999;
            left: 0;
        }
    </style>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 text-center mb-10">ระบบจ่ายงานรถรับส่ง</h1>
        
        <div class="max-w-2xl mx-auto">
            <div class="bg-white px-4 py-6 sm:px-6 lg:px-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">ข้อมูลการจ่ายงาน</h2>
                <form action="" method="post" id="driver-job-form" class="space-y-6 relative">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-group p-4 bg-white border border-gray-200 rounded-lg">
                            <label for="booking-order" class="block text-sm font-medium text-gray-700 mb-1">ค้นหารายการจอง</label>
                            <select id="booking-order" name="booking-order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></select>
                        </div>
                        <div class="input-group p-4 bg-white border border-gray-200 rounded-lg">
                            <label for="taxi_user" class="block text-sm font-medium text-gray-700 mb-1">เลือกคนขับ</label>
                            <select id="taxi_user" name="taxi_user" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></select>
                        </div>
                        <div class="input-group p-4 bg-white border border-gray-200 rounded-lg col-span-2">
                            <label for="status_job" class="block text-sm font-medium text-gray-700 mb-1">สถานะงาน</label>
                            <select id="status_job" name="status_job" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 special-mark">
                                <option value="pending">รอดำเนินการ</option>
                                <option value="scheduled">กำหนดคิวแล้ว</option>
                                <option value="dispatched">มอบหมายแล้ว</option>
                                <option value="progress">กำลังดำเนินการ</option>
                                <option value="completed">เสร็จสิ้น</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="input-group p-4 bg-white border border-gray-200 rounded-lg col-span-2">
                            <label for="plate_driver" class="block text-sm font-medium text-gray-700 mb-1">ทะเบียนรถคนขับ</label>
                            <input type="text" name="plate_driver" id="plate_driver" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 special-mark" placeholder="กข-1234 กรุงเทพมหานคร">
                        </div>
                    </div>
                    
                    <!-- Add-on Information Section -->
                    <div id="addon-information-section" class="space-y-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-4">ข้อมูลบริการเสริม</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-medium text-gray-700">บริการรับ - ส่งสนามบิน</span>
                                    <input type="checkbox" id="service_pickup" class="toggle toggle-primary" checked="checked" readonly />
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-medium text-gray-700">บริการรับจอด - รับส่งรถ สนามบิน</span>
                                    <input type="checkbox" id="service_valet" class="toggle toggle-primary" checked="checked" readonly />
                                </div>
                            </div>
                        </div>
                        <!-- Sending Flight Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-semibold text-gray-800 border-b border-gray-200 pb-2">ข้อมูลการเดินทาง (รับ-ส่ง)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เที่ยวบินขาไป</label>
                                    <input type="text" id="sending_flight_depart" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาขาไป</label>
                                    <input type="text" id="sending_flight_depart_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เที่ยวบินขากลับ</label>
                                    <input type="text" id="sending_flight_arrival" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาขากลับ</label>
                                    <input type="text" id="sending_flight_arrival_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">จุดรับ</label>
                                    <input type="text" id="sending_flight_pickup_point" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาจุดรับ</label>
                                    <input type="text" id="sending_flight_pickup_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">จุดส่ง</label>
                                    <input type="text" id="sending_flight_dropoff_point" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาจุดส่ง</label>
                                    <input type="text" id="sending_flight_dropoff_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ผู้ใหญ่</label>
                                    <input type="text" id="sending_flight_adult_count" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เด็ก</label>
                                    <input type="text" id="sending_flight_child_count" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">กระเป๋า</label>
                                    <input type="text" id="sending_flight_baggage_count" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                                    <input type="text" id="sending_flight_pickup_note" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Parking Car Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-semibold text-gray-800 border-b border-gray-200 pb-2">ข้อมูลการเดินทาง (ฝากจอด)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เที่ยวบินขาไป</label>
                                    <input type="text" id="parking_car_depart" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาขาไป</label>
                                    <input type="text" id="parking_car_depart_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เที่ยวบินขากลับ</label>
                                    <input type="text" id="parking_car_arrival" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาขากลับ</label>
                                    <input type="text" id="parking_car_arrival_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">จุดรับรถ</label>
                                    <input type="text" id="parking_car_pickup_point" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาจุดรับรถ</label>
                                    <input type="text" id="parking_car_pickup_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">จุดส่งคืนรถ</label>
                                    <input type="text" id="parking_car_dropoff_point" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เวลาจุดส่งคืนรถ</label>
                                    <input type="text" id="parking_car_dropoff_time" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                                <div class="input-group md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                                    <input type="text" id="parking_car_pickup_note" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="w-full w-auto bg-primary hover:bg-primary-dark text-white py-3 px-4 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200 cursor-pointer lg:w-auto sm:w-full md:w-auto">
                            สร้างและจ่ายงาน
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="booking-modal" class="fixed inset-0 z-[999] flex items-center justify-center bg-transparent hidden">
    <div id="booking-modal-background" class="absolute inset-0 bg-opacity-40 backdrop-blur-sm cursor-pointer"></div>
    <div id="booking-modal-content-wrapper" class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 transform transition-all opacity-0 translate-y-4" onclick="event.stopPropagation()">
        <button id="booking-modal-close" type="button" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl font-bold">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="text-center mb-6">
            <h3 id="booking-modal-title" class="text-xl font-semibold text-gray-800">รายละเอียดการจอง</h3>
            <p id="booking-modal-subtitle" class="text-sm text-gray-600 mt-2">ข้อมูลสรุปของการจองที่เลือก</p>
        </div>
        <div id="booking-modal-body" class="max-h-[60vh] overflow-y-auto p-2">
            <!-- Dynamic content goes here -->
        </div>
    </div>
</div>

<!-- Custom Alert Popups -->
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

<?php get_footer(); ?>