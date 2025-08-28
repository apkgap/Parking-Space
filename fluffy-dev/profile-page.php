<?php
/**
 * Template Name: Customer Profile Page
 * Description: Beautiful profile page with parking booking history
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$current_user = wp_get_current_user();
get_header();
?>

<div class="min-h-screen bg-white">
    <!-- Profile Header -->
    <div class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <div class="w-20 h-20 md:w-24 md:h-24 lg:w-32 lg:h-32 bg-gradient-to-r from-sky-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                        <?php echo strtoupper(substr($current_user->display_name, 0, 2)); ?>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 md:w-8 md:h-8 lg:w-10 lg:h-10 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900 lg:text-3xl"><?php echo esc_html($current_user->display_name); ?></h1>
                    <p class="text-gray-600 mt-1"><?php echo esc_html($current_user->user_email); ?></p>
                    <div class="inline-flex items-left mt-2 space-x-4 flex-col lg:flex-row lg:items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-sky-100 text-sky-800">
                            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ผู้ใช้งานยืนยันแล้ว
                        </span>
                        <span class="text-sm text-gray-500">สมาชิกตั้งแต่ <?php echo date('d/m/Y', strtotime($current_user->user_registered)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Booking History Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0">
                    <h2 class="text-xl font-semibold text-gray-900">ประวัติการจองที่จอดรถ</h2>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full md:w-auto space-y-3 sm:space-y-0 sm:space-x-3">
                        <div class="relative w-full sm:w-auto">
                            <input type="text" id="search-bookings" placeholder="ค้นหาการจอง..." 
                                   class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <button id="search-button" class="w-full sm:w-auto bg-primary hover:bg-primary text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none transition-colors">
                            ค้นหา
                        </button>
                        <select id="filter-status" class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <option value="">ทุกสถานะ</option>
                            <option value="confirmed">ยืนยันแล้ว</option>
                            <option value="checked-in">เช็คอิน</option>
                            <option value="checked-out">เช็คเอาท์</option>
                            <option value="cancel">ยกเลิก</option>
                            <option value="none">ไม่มีข้อมูล</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-bookings" class="flex flex-col items-center justify-center py-8 hidden">
                <div class="w-8 h-8 border-4 border-sky-200 border-t-sky-600 rounded-full animate-spin mb-4"></div>
                <div class="text-gray-600 text-sm font-medium">กำลังโหลดข้อมูล...</div>
            </div>

            <!-- Bookings List -->
            <div id="bookings-container" class="hidden">
                <div id="bookings-list" class="divide-y divide-gray-200">
                    <!-- Booking items will be inserted here -->
                </div>
                
                <!-- Pagination - New Responsive Design -->
                <div id="pagination-container" class="bg-white rounded-b-lg">
                    <!-- Mobile & Tablet View -->
                    <div class="flex flex-col space-y-4 p-4 lg:hidden">
                        <!-- Results Info -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600">
                                หน้า <span class="font-semibold text-sky-600" id="current-page-mobile">1</span> 
                                จาก <span class="font-semibold text-sky-600" id="total-pages-mobile">1</span>
                                (<span class="font-medium" id="total-results-mobile">0</span> รายการ)
                            </p>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="flex justify-center space-x-3">
                            <button id="prev-mobile" class="flex items-center px-4 py-2 text-sm font-medium text-sky-700 bg-white border border-sky-300 rounded-lg hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                ก่อนหน้า
                            </button>
                            
                            <button id="next-mobile" class="flex items-center px-4 py-2 text-sm font-medium text-sky-700 bg-white border border-sky-300 rounded-lg hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                ถัดไป
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Page Numbers for Mobile -->
                        <div class="flex justify-center">
                            <div id="page-numbers-mobile" class="flex space-x-1">
                                <!-- Mobile page numbers will be inserted here -->
                            </div>
                        </div>
                    </div>

                    <!-- Desktop View -->
                    <div id="desktop-pagination" class="hidden items-center justify-between p-6 lg:flex">
                        <!-- Results Info -->
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-8 h-8 bg-sky-100 rounded-full">
                                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    แสดง <span class="text-sky-600" id="showing-from">1</span>-<span class="text-sky-600" id="showing-to">3</span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    จากทั้งหมด <span class="font-medium" id="total-results">0</span> รายการ
                                </p>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="flex items-center space-x-4">
                            <!-- Previous Button -->
                            <button id="prev-desktop" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                ก่อนหน้า
                            </button>

                            <!-- Page Numbers -->
                            <nav class="flex items-center space-x-1" aria-label="Pagination">
                                <div id="page-numbers" class="flex space-x-1">
                                    <!-- Page numbers will be inserted here -->
                                </div>
                            </nav>

                            <!-- Next Button -->
                            <button id="next-desktop" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm">
                                ถัดไป
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div id="empty-state" class="hidden text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">ไม่มีการจอง</h3>
                    <p class="mt-1 text-sm text-gray-500">คุณยังไม่มีประวัติการจองที่จอดรถ</p>
                    <div class="mt-6">
                        <a href="<?php echo home_url('/carpark'); ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            จองที่จอดรถใหม่
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Detail Modal -->
<div id="booking-modal" class="fixed z-[9998] flex items-center justify-center inset-0 bg-opacity-40 backdrop-blur-sm cursor-pointer hidden">
    <div class="relative mx-auto p-5 w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">รายละเอียดการจอง</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Content -->
            <div id="modal-content" class="mt-4">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
// Initialize profile page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ProfileManager !== 'undefined') {
        ProfileManager.init();
    }
});
</script>