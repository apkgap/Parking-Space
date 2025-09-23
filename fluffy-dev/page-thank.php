<?php
/*
 Template Name: Thank You Page
 This template is used to display a thank you page after a successful booking and payment.
 It includes information about the booking, confirmation, and next steps for the user.
*/

get_header();

// Get booking reference and email from URL parameters
$booking_ref = isset($_GET['ref']) ? sanitize_text_field($_GET['ref']) : '';
$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
$order_code = isset($_GET['order_code']) ? sanitize_text_field($_GET['order_code']) : '';

// Determine booking type based on reference format
$is_car_rental = !empty($booking_ref) && strpos($booking_ref, 'CR') === 0;
$is_parking = !empty($order_code);
?>

<div id="ty-page" class="min-h-screen flex items-center py-10">
    <div class="max-w-full w-full mx-auto">
        <!-- Header -->
        <div class="container max-w-7xl mx-auto p-0 flex flex-col items-center justify-center">
            <div class="head-step_box w-full">
                <ul class="steps steps-horizontal w-full">
                    <li class="step">
                        <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                        การจอง
                    </li>
                    <li class="step">
                        <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg></span>
                        ชำระเงิน
                    </li>
                    <li class="step step-primary">
                        <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></span>
                        ยืนยันการจอง
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="main-thanks" class="rounded-2xl p-8">
            
            <!-- Success Icon -->
            <div class="flex justify-center mb-4 ssi-con">
                <div class="rounded-full w-16 h-16 flex items-center justify-center shadow-lg" style="background:#22c55e;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
            </div>
            
            <!-- Success Message -->
            <div class="text-center mb-8 ssi-con-ttext">
                <h2 class="text-2xl font-bold text-green-600 thai-font mb-2">การชำระเงินของคุณสำเร็จแล้ว!</h2>
                <p class="text-gray-600 thai-font text-base mb-1">ระบบกำลังตรวจสอบข้อมูลการจองของคุณ</p>
                <p class="text-red-500 text-xs thai-font">หากไม่ได้รับแปลงข้อมูลกรุณาแจ้งเจ้าหน้าที่ก่อนเวลาบันทึก 12 ชั่วโมง</p>
            </div>
            
            <!-- Booking Info -->
            <div class="mb-6 space-y-3" style="border-bottom:1px solid #e5e7eb;">
                <div class="container-m" style="max-width:500px;margin:auto;">
                <?php if ($is_car_rental): ?>
                <div class="flex items-center gap-2 rounded-lg px-3 py-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span class="text-gray-800 thai-font flex-1">หมายเลขการจองรถเช่าของคุณ</span>
                    <span class="font-bold text-lg text-cyan-600"><?php echo esc_html($booking_ref); ?></span>
                </div>
                <?php elseif ($is_parking): ?>
                <div class="flex items-center gap-2 rounded-lg px-3 py-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span class="text-gray-800 thai-font flex-1">หมายเลขการจองที่จอดรถของคุณ</span>
                    <span class="font-bold text-lg text-cyan-600"><?php echo esc_html($order_code); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($email)): ?>
                <div class="flex items-center gap-2 rounded-lg px-3 py-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-gray-800 thai-font flex-1">อีเมลยืนยันถูกส่งไปที่</span>
                    <div class="relative">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded font-semibold"><?php echo esc_html($email); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                </div>
            </div>
            
            <!-- LINE Section -->
            <div class="rounded-lg p-4 mb-6" style="border-bottom:1px solid #e5e7eb;">
                <div class="container-m" style="
                    max-width: 500px;
                    margin: auto;
                    text-align: center;
                    display: flex;
                    justify-content: center;
                    flex-direction: column;
                    align-items: center;
                ">
                <p class="text-sm thai-font mb-2 font-semibold text-green-700">**กรุณาแอด <span class="text-green-600 font-bold">LINE Official Account</span> ของเรา**</p>
                <p class="text-xs text-gray-600 thai-font mb-4">เพื่อไม่พลาดทุกข้อบิดตอนการใช้งาน</p>
                
                <div class="text-left mb-4 space-y-2">
                    <div class="flex items-start gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1"></div>
                        <span class="text-sm thai-font text-green-900">แจ้งเตือนการคอนเฟิร์มก่อนดรต</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1"></div>
                        <span class="text-sm thai-font text-green-900">ล็อคใช้เครานท์</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1"></div>
                        <span class="text-sm thai-font text-green-900">การแจ้งเตือนค่าบริบท (ถ้ามี)</span>
                    </div>
                </div>
                
                <button id="line-icon" class="flex items-center gap-2 text-white px-4 py-2 rounded-lg shadow transition thai-font font-semibold text-base mx-auto" style="background:#22c55e;" onclick="openLine()">
                    <svg id="Capa_1" enable-background="new 0 0 512 512" height="34" viewBox="0 0 512 512" width="34" xmlns="http://www.w3.org/2000/svg"><g><path d="m435.699 77.37c-48.153-37.963-111.971-58.87-179.699-58.87s-131.546 20.907-179.699 58.87c-49.203 38.791-76.301 90.688-76.301 146.13s27.098 107.339 76.301 146.13c48.153 37.963 111.971 58.87 179.699 58.87 15.219 0 30.373-1.069 45.25-3.187v53.187c0 4.611 2.12 8.965 5.75 11.809 2.667 2.089 5.931 3.192 9.251 3.191 1.198 0 2.404-.144 3.592-.437 34.694-8.559 82.733-43.684 119.539-87.403 21.631-25.695 38.836-53.226 51.137-81.828 14.253-33.145 21.481-66.902 21.481-100.332 0-55.442-27.098-107.339-76.301-146.13zm-297.166 217.046c-11.465.064-19.287.092-24.674.092-11.166 0-11.881-.123-12.961-.308-7.201-1.234-12.466-7.478-12.466-14.785v-99.915c0-8.284 6.716-15 15-15s15 6.716 15 15v84.994c5.999-.012 13.147-.04 19.933-.078h.086c8.245 0 14.952 6.66 14.998 14.916.046 8.284-6.632 15.038-14.916 15.084zm64.636-14.916c0 8.284-6.716 15-15 15s-15-6.716-15-15v-100c0-8.284 6.716-15 15-15s15 6.716 15 15zm112.898 13.156c-6.875 2.149-14.1-.483-18.411-6.707l-40.152-58.144v51.695c0 8.284-6.716 15-15 15s-15-6.716-15-15v-99.813c0-6.564 4.269-12.367 10.536-14.32 6.265-1.951 13.076.395 16.807 5.797l42.308 61.265-.51-52.784c-.08-8.284 6.57-15.064 14.854-15.145h.148c8.217 0 14.917 6.62 14.996 14.855l.935 96.63c.001.048.001.097.001.145-.001 7.853-4.52 14.34-11.512 16.526zm89.394-78.156c8.284 0 15 6.716 15 15s-6.716 15-15 15h-24.029v20h27.136c8.284 0 15 6.716 15 15s-6.716 15-15 15h-42.136c-8.284 0-15-6.716-15-15v-100c0-8.284 6.716-15 15-15h42.136c8.284 0 15 6.716 15 15s-6.716 15-15 15h-27.136v20z"/></g></svg>
                    <span>@okparking</span>
                </button>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div id="contact-info" class="rounded-lg p-4 mb-6" style="border-bottom:1px solid #e5e7eb;">
                <div class="container-m max-w-[500px] mx-auto flex flex-col justify-center items-center bg-neutral-400 p-6 border border-gray-300 rounded-xl">
                <p class="text-xs text-gray-600 thai-font mb-2 text-white">หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่</p>
                <div class="text-xs thai-font space-y-1 text-white">
                    <p><span class="font-semibold">โทรศัพท์ :</span> 081-123-4567</p>
                    <p><span class="font-semibold">อีเมล :</span> Support@okparking.com</p>
                    <p><span class="font-semibold">Line ID :</span> @okparking</p>
                </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-center gap-3 pt-2">
                <button class="text-white px-5 py-2 rounded-lg shadow thai-font font-semibold transition hidden" style="background:#06b6d4;" onclick="viewDetails()">ดูรายละเอียด</button>
                <button class="text-gray-700 px-5 py-2 rounded-lg shadow thai-font font-semibold transition" style="background:#e5e7eb;" onclick="goHome()">กลับสู่หน้าแรก</button>
            </div>
        </div>
    </div>
</div>
<script>
function openLine() {
    window.open('https://line.me/R/ti/p/@okparking', '_blank');
}
function viewDetails() {
    // ใส่ลิงก์หรือฟังก์ชันสำหรับดูรายละเอียด
    window.location.href = "/booking-details";
}
function goHome() {
    window.location.href = "/";
}
</script>
<?php
get_footer();
?>