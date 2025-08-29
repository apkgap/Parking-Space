    <?php
/*
 Template Name: Carpark
*/

get_header();
// print_r($_POST['formDataJson']);

?>

<main id="primary" class="site-main">
    <div class="information-booking py-[2em] sm:py-[2em] lg:py-[2em] xl:py-[5em] px-[1.5em] sm:px-[1.5em] lg:px-[2em] xl:px-[0em]">
        <div class="ib-box flex flex-col gap-[1em] md:gap-[3em] lg:gap-[5em]">
            <div class="ib-box-header">
                <div class="container max-w-7xl mx-auto p-0 flex flex-col items-center justify-center">
                    <div class="head-step_box w-full">
                        <ul class="steps steps-horizontal w-full">
                            <li class="step step-primary">
                                 <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                                 ค้นหาที่จอดรถ
                            </li>
                            <li class="step">
                                <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                การจอง
                            </li>
                            <li class="step">
                                <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg></span>
                                ชำระเงิน
                            </li>
                            <li class="step">
                                <span class="step-icon"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></span>
                                ยืนยันการจอง
                            </li>
                        </ul>
                    </div>
                </div>
                
            </div>
            <div class="container max-w-7xl mx-auto flex flex-col md:flex-row gap-4 md:gap-5 lg:gap-10">            
                <div class="w-full md:w-[70%]">

                    <div class="form-container">
                        <h2 class="text-xl sm:text-2xl lg:text-2xl font-medium mb-6 py-4 px-0 text-primary rounded-lg inline-flex">
                            ค้นหาที่จอดรถ
                        </h2>

                        <form action="" id="carReservationForm" method="post" class="flex flex-col gap-5">
                            <!-- Date and Time Section -->
                            <div id="dateTimeStartGrid" class="flex flex-col md:flex-row gap-5">
                                <div class="input-group flex-1 mb-4 md:mb-0">
                                    <label for="startDate" class="text-sm sm:text-base lg:text-[16px]">วันเริ่มต้น (Start date)<span class="text-red-500 pr-2">*</span></label>
                                    <input type="text" id="startDate" name="startDate" class="input-field bg-white" >
                                </div>
                                <div class="input-group b-start_time input-group flex-1">
                                    <label for="startTime" class="text-sm sm:text-base lg:text-[16px]">เวลาเริ่มต้น (Start time)<span class="text-red-500 pr-2">*</span></label>
                                    <!-- Input type changed to text for Picker.js to handle -->
                                    <input type="text" id="startTime" name="startTime" class="input-field bg-white startTime" value="09:00" readonly>
                                    <div class="input_start_time"></div>
                                </div>
                            </div>

                            <div id="dateTimeEndGrid" class="flex flex-col md:flex-row gap-5">
                                <div class="input-group flex-1 mb-4 md:mb-0">
                                    <label for="endDate" class="text-sm sm:text-base lg:text-[16px]">วันสิ้นสุด (End date)<span class="text-red-500 pr-2">*</span></label>
                                    <input type="text" id="endDate" name="endDate" class="input-field bg-white" >
                                </div>
                                <div class="input-group input-group flex-1">
                                    <label for="endTime" class="text-sm sm:text-base lg:text-[16px]">เวลาสิ้นสุด (End time)<span class="text-red-500 pr-2">*</span></label>
                                    <!-- Input type changed to text for Picker.js to handle -->
                                    <input type="text" id="endTime" name="endTime" class="input-field bg-white endTime" value="18:00" readonly>
                                    <div class="input_end_time"></div>
                                </div>
                            </div>

                            <div id="locationGrid" class="flex flex-col md:flex-row gap-5">
                                <div class="input-group flex-1 mb-4 md:mb-0">
                                    <label for="location1" class="text-sm sm:text-base lg:text-[16px]">สถานที่ (Location)<span class="text-red-500 pr-2">*</span></label>
                                    <select id="location1"  name="location1" class="input-field bg-white car-type-select" >
                                        <optionvalue="" disabled selected>เลือกสถานที่</option>
                                        <option value="SuvarnabhumiAirport">สนามบินสุวรรณภูมิ</option>
                                        <option value="DonMueangAirport">สนามบินดอนเมือง</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Vehicle Type and Quantity Section -->
                            <div id="vehicle-rows-container">
                                <div class="vehicle-row border-b border-gray-300 pb-6 mb-6">
                                    <div class="vehicle-row-content flex flex-wrap gap-5">
                                        <!-- Column 1: Car Type -->
                                        <div class="input-group w-full md:flex-1">
                                            <label for="carType1" class="text-sm sm:text-base lg:text-[16px]">ประเภทรถ (Car type)<span class="text-red-500 pr-2">*</span></label>
                                            <select id="carType1" name="carType1" class="input-field bg-white car-type-select" >
                                                <option value="">กำลังโหลด...</option>            
                                            </select>
                                        </div>

                                        <!-- Column 2: Quantity -->
                                        <div class="input-group w-full md:flex-1">
                                            <label for="quantity1" class="text-sm sm:text-base lg:text-[16px]">จำนวน (Quantity)<span class="text-red-500 pr-2">*</span></label>
                                            <input type="range" id="quantity1" name="quantity1" class="range range-primary quantity-input" value="0" min="0" max="10" >
                                            <span class="cc-t flex items-center gap-2">
                                                <div id="quantity-display-1" class="quantity-display">0</div>
                                                คัน
                                            </span>
                                        </div>

                                        <!-- Full Width Row: Other Car Type (conditionally shown) -->
                                        <div class="input-group other-car-type-input hidden w-full">
                                            <label for="otherCarType1" class="text-sm sm:text-base lg:text-[16px]">โปรดระบุประเภทรถอื่นๆ</label>
                                            <input type="text" id="otherCarType1" name="otherCarType1" class="input-field bg-white" placeholder="ระบุประเภทรถ...">
                                        </div>                               
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addVehicleRow" class="add-row-button flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-light text-sm">ถ้ามีประเภทรถฝากเพิ่ม (If there are additional vehicle types to add)</span>
                            </button>

                            <div class="box-f_btn flex justify-center align-center">
                                <button type="button" id="btn-search_all" class="submit-button">ค้นหา</button>
                            </div>  
                                                        

                            <div class="box-parking pt-6 mt-6 border-t border-t-gray-300">
                                <div class="max-w-7xl mx-auto">
                                    <!-- Header & Stats -->
                                    <div class="mb-6">
                                        <h1 class="text-xl sm:text-2xl lg:text-2xl font-medium mb-6 py-4 px-0 text-primary rounded-lg inline-flex">กรุณาเลือกที่จอดรถ</h1>
                                        <div class="flex flex-wrap gap-4 items-center">
                                            <div class="text-sm bg-white px-3 py-1 rounded-lg shadow hidden">
                                                <span class="font-medium text-gray-600">รวม:</span> <span id="total-spaces"></span> ช่อง
                                            </div>
                                            <div class="text-sm bg-green-100 px-3 py-1 rounded-lg">
                                                <span class="font-medium text-green-800">ว่าง:</span> <span id="available-spaces"></span> ช่อง
                                            </div>
                                            <div class="text-sm bg-red-100 px-3 py-1 rounded-lg hidden">
                                                <span class="font-medium text-red-800">ใช้งาน:</span> <span id="occupied-spaces"></span> ช่อง
                                            </div>
                                            <div class="text-sm bg-blue-100 px-3 py-1 rounded-lg hidden">
                                                <span class="font-medium text-blue-800">อัตราการใช้:</span> <span id="occupancy-rate"></span>%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Controls -->
                                    <div class="mb-6 flex flex-wrap gap-2">
                                        <button id="view-status-btn" type="button" class="px-4 py-2 rounded-lg font-medium">
                                            แสดงสถานะ
                                        </button>
                                        <button id="view-distance-btn" type="button" class="hidden px-4 py-2 rounded-lg font-medium">
                                            แสดงระยะห่าง
                                        </button>
                                        <button id="view-zone-btn" type="button" class="hidden px-4 py-2 rounded-lg font-medium">
                                            แสดงโซน
                                        </button>
                                    </div>

                                    <!-- Legend -->
                                    <div class="mb-6 p-4 bg-white rounded-lg shadow">
                                        <h3 class="text-lg font-medium mb-3">คำอธิบาย</h3>
                                        <div id="legend-content" class="flex flex-wrap gap-4">
                                            <!-- เนื้อหาส่วนนี้จะถูกสร้างโดย JavaScript -->
                                        </div>
                                    </div>

                                    <!-- Parking Grid -->
                                    <div class="p-6 bg-white rounded-lg shadow overflow-x-auto">
                                        <h3 class="text-lg font-medium mb-4">แผนผังลานจอดรถ</h3>
                                        <div id="parking-grid-container" class="grid gap-1">
                                            <!-- ตารางช่องจอดจะถูกสร้างโดย JavaScript -->
                                        </div>
                                    </div>

                                    <!-- Selected Space Info -->
                                    <div id="selected-space-info" class="p-4 bg-white rounded-lg shadow" style="display: none;">
                                        <h3 class="text-lg font-medium mb-3">ข้อมูลช่องจอด</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                            <div>
                                                <span class="text-sm font-medium text-gray-600">รหัสช่อง:</span>
                                                <p id="selected-id" class="text-lg font-[600]"></p>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-600">สถานะ:</span>
                                                <p id="selected-status" class="font-medium"></p>
                                            </div>
                                            <div>
                                                <!-- This button is hidden when multiple spaces are selected in new logic -->
                                                <button id="toggle-status-btn" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-primary transition-colors" style="display: none;">
                                                    เปลี่ยนสถานะ
                                                </button>
                                            </div>
                                        </div>
                                        <div id="vehicle-assignments" class="mt-4">
                                            <!-- Vehicle assignments will be rendered here -->
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div id="error-message" role="alert" class="hidden alert alert-error text-center flex justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <!-- Error messages will be displayed here -->
                            </div>

                            <div class="profile-info">
                                <div class="flex flex-col md:flex-row gap-4 mb-2">
                                    <div class="input-group flex-1">
                                        <label for="fullname" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>ชื่อ - นามสกุล</label>
                                        <input type="text" id="fullname" name="fullname" class="input-field bg-white req-oc" >
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                    <div class="input-group flex-1">
                                        <label for="email" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>อีเมล</label>
                                        <input type="email" id="email" name="email" class="input-field bg-white req-oc" >
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-4 mb-2">
                                    <div class="input-group flex-1">
                                        <label for="phone" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เบอร์โทรศัพท์</label>
                                        <input type="tel" id="phone" name="phone" class="input-field bg-white req-oc" >
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                    <div class="input-group flex-1">
                                        <label for="lineid" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>Line ID</label>
                                        <input type="text" id="lineid" name="lineid" class="input-field bg-white req-oc" >
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="addon-information">
                                <!-- บริการเสริม -->
                                <div class="mb-4">
                                <div class="font-[600] text-base mb-2 text-red-600"><span class="text-red-500 pr-2">*</span>ฟรี บริการเสริม</div>
                                <div class="flex flex-col gap-2">
                                    <label for="service_pickup" class="inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            id="service_pickup"
                                            name="service_pickup"
                                            class="form-checkbox text-blue-600 checkbox checkbox-primary text-white border border-primary"
                                        />
                                        <span class="ml-2">บริการรับ - ส่งสนามบิน</span>
                                    </label>

                                    <label for="service_valet" class="inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            id="service_valet"
                                            name="service_valet"
                                            class="form-checkbox text-blue-600 checkbox checkbox-primary text-white border border-primary"
                                            value="valet"
                                        />
                                        <span class="ml-2">บริการรับจอด - รับส่งรถ สนามบิน</span>
                                    </label>

                                </div>
                                </div>
                                <div class="info-sending_airport hidden">
                                    <!-- ข้อมูลเที่ยวบิน -->
                                    <div class="mb-4">
                                    <div class="font-[600] text-base mb-2">ข้อมูลเที่ยวบิน</div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="sending_flight_depart" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>กรอกเที่ยวบินขาออก (Departure Flight No.)</label>
                                        <input type="text" id="sending_flight_depart" name="sending_flight_depart" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="sending_flight_depart_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลาเที่ยวบินขาออก</label>
                                        <input type="text" id="sending_flight_depart_time" name="sending_flight_depart_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_sending_flight_depart_time"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="sending_flight_arrival" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>กรอกเที่ยวบินขาเข้า (Arrival Flight No.)</label>
                                        <input type="text" id="sending_flight_arrival" name="sending_flight_arrival" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="sending_flight_arrival_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลาเที่ยวบินขาเข้า</label>
                                        <input type="text" id="sending_flight_arrival_time" name="sending_flight_arrival_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_sending_flight_arrival_time"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="sending_pickup_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลาถึงสนามจอดรถ</label>
                                        <input type="text" id="sending_pickup_time" name="sending_pickup_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_sending_pickup_time"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="sending_dropoff_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลารับที่สนามบิน</label>
                                        <input type="text" id="sending_dropoff_time" name="sending_dropoff_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_sending_dropoff_time"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="sending_pickup_point" class="text-sm sm:text-base lg:text-[16px]">ระบุจุดส่งที่สนามบิน เช่น ประตู 1 ขาออก</label>
                                        <input type="text" id="sending_pickup_point" name="sending_pickup_point" class="input-field bg-white">
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="sending_dropoff_point" class="text-sm sm:text-base lg:text-[16px]">ระบุจุดรับที่สนามบิน เช่น ประตู 10 ขาเข้า</label>
                                        <input type="text" id="sending_dropoff_point" name="sending_dropoff_point" class="input-field bg-white">
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="sending_adult_count" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>จำนวนผู้โดยสาร (ผู้ใหญ่)</label>
                                        <input type="number" id="sending_adult_count" name="sending_adult_count" class="input-field bg-white req-ocx" min="0" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="sending_child_count" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>จำนวนผู้โดยสาร (เด็ก)</label>
                                        <input type="number" id="sending_child_count" name="sending_child_count" class="input-field bg-white req-ocx" min="0" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="sending_baggage_count" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>จำนวนกระเป๋า</label>
                                        <input type="number" id="sending_baggage_count" name="sending_baggage_count" class="input-field bg-white req-ocx" min="0" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                    </div>
                                    <div class="input-group mb-2">
                                        <label for="sending_pickup_note" class="text-sm sm:text-base lg:text-[16px]">หมายเหตุสำหรับคนขับ เช่น โปรดระบุ เช่น เพิ่มคาร์ซีท</label>
                                        <input type="text" id="sending_pickup_note" name="sending_pickup_note" class="input-field bg-white" placeholder="โปรดระบุ เช่น เพิ่มคาร์ซีท">
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                    </div>
                                </div>
                                <div class="info-parking_car hidden">
                                    <!-- ข้อมูลเที่ยวบิน -->
                                    <div class="mb-4">
                                    <div class="font-[600] text-base mb-2">ข้อมูลเที่ยวบิน</div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="flight_depart" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>กรอกเที่ยวบินขาออก (Departure Flight No.)</label>
                                        <input type="text" id="flight_depart" name="flight_depart" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="flight_depart_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลาเที่ยวบินขาออก</label>
                                        <input type="text" id="flight_depart_time" name="flight_depart_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_flight_depart_time"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="flight_arrival" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>กรอกเที่ยวบินขาเข้า (Arrival Flight No.)</label>
                                        <input type="text" id="flight_arrival" name="flight_arrival" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="flight_arrival_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลาเที่ยวบินขาเข้า</label>
                                        <input type="text" id="flight_arrival_time" name="flight_arrival_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_flight_arrival_time"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="pickup_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลารับรถที่สนามบิน</label>
                                        <input type="text" id="pickup_time" name="pickup_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_pickup_time"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="dropoff_time" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>เวลาส่งรถที่สนามบิน</label>
                                        <input type="text" id="dropoff_time" name="dropoff_time" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        <div class="input_dropoff_time"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:flex-row gap-4 mb-2">
                                        <div class="input-group flex-1">
                                        <label for="pickup_point" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>ระบุจุดรับรถที่สนามบิน</label>
                                        <input type="text" id="pickup_point" name="pickup_point" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                        <div class="input-group flex-1">
                                        <label for="dropoff_point" class="text-sm sm:text-base lg:text-[16px]"><span class="text-red-500 pr-2">*</span>ระบุจุดส่งรถที่สนามบิน</label>
                                        <input type="text" id="dropoff_point" name="dropoff_point" class="input-field bg-white req-ocx" >
                                        <div class="popup_span w-full hide"></div>
                                        </div>
                                    </div>
                                    <div class="input-group mb-2">
                                        <label for="pickup_note" class="text-sm sm:text-base lg:text-[16px]">หมายเหตุสำหรับคนขับรถ</label>
                                        <input type="text" id="pickup_note" name="pickup_note" class="input-field bg-white" placeholder="เช่น รถสีเทา camry">
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                    </div>
                                </div>
                                
                            </div>

                            <input type="hidden" id="formDataJson" name="formDataJson">
                            <div class="box-f_btn flex justify-center align-center">
                                <button type="submit" id="submit-btn" class="submit-button">ส่งข้อมูล</button>
                            </div>                        
                        </form>
                    </div>

                </div>
                <div class="w-full md:w-[30%]">
                    <!-- Summary Box -->
                    <div id="summary-box" class="bg-white rounded-xl shadow p-6 max-w mx-auto text-gray-800">
                        <!-- รายละเอียดการค้นหา -->
                        <div class="mb-4">
                            <div class="font-[600] text-base mb-2">รายละเอียดการค้นหา</div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>สถานที่</span>
                                <span id="summary-location" class="font-medium">สนามบินสุวรรณภูมิ</span>
                            </div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>วันเริ่มต้น (Start date)</span>
                                <span id="summary-start-date">14/05/2025</span>
                            </div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>เวลาเริ่มต้น (Start time)</span>
                                <span id="summary-start-time">09:00</span>
                            </div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>วันสิ้นสุด (End date)</span>
                                <span id="summary-end-date">16/05/2025</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>เวลาสิ้นสุด (End time)</span>
                                <span id="summary-end-time">18:00</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <!-- รายละเอียดรถ -->
                        <div class="mb-4">
                            <div class="font-[600] text-base mb-2">รายละเอียดรถ</div>
                            <div class="flex flex-col gap-2">
                              <div class="font-medium text-sm">ประเภทรถ</div>
                              <div id="summary-car-type"></div>
                            </div>
                            <div class="flex justify-between text-sm">
                              <span>จำนวนรถที่จอด</span>
                              <span id="summary-car-qty">3 คัน</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <!-- รายละเอียดราคา -->
                        <div class="mb-4">
                            <div class="font-[600] text-base mb-2">รายละเอียดราคา</div>
                            <div class="flex justify-between text-sm">
                                <span>ราคาค่าจอด</span>
                                <span id="summary-price-per-car">300 บาท/คัน</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <!-- รวม -->
                        <div class="flex justify-between items-center text-lg font-[600] mb-4">
                            <span>รวม</span>
                            <span id="summary-total" class="text-primary">480 บาท</span>
                        </div>
                        <button type="submit" id="submit-btn" class="hide w-full bg-primary text-white font-[600] py-2 rounded-xl transition-colors text-base hover:opacity-70 submit-button">
                            จองทันที
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>


</main><!-- #main -->


<?php
// get_sidebar();
get_footer();
?>
