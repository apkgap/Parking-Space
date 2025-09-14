<?php
/*
 Template Name: Rent Car Booking
 Description: A template for booking a private car with a summary section.
*/

get_header();
?>
<main id="primary" class="primary-rentcar site-main py-[1.5em] sm:py-[1.5em] lg:py-[2em] xl:py-[5em] px-[1.5em] sm:px-[1.5em] lg:px-[2em] xl:px-[0em] bg-gray-50 min-h-screen">
    <div class="container max-w-7xl mx-auto">
            <?php
        echo do_shortcode( '[car_route_search]');
                ?>  
                <form method="POST" action="" id="rentcar-booking-form" autocomplete="off">

            

        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Form Section -->
            <div class="w-full lg:w-[70%] space-y-8">
                    <!-- Personal Information Card -->
                    <div class="bg-white rounded-xl shadow-md sticky top-8 p-4 sm:p-6 md:p-8">
                        <h2 class="text-2xl font-bold mb-2 text-black">ข้อมูลส่วนตัว</h2>
                        <p class="text-sm text-gray-500 mb-6">*กรุณากรอกข้อมูลให้ครบถ้วน</p>
                        <div class="space-y-4">
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col">
                                    <label for="fullname" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>ชื่อ - นามสกุล</label>
                                    <input
                                        type="text"
                                        id="fullname"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="ชื่อ - นามสกุล"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col">
                                    <label for="email" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="อีเมล"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex gap-4 flex-col md:flex-row">
                                    <div class="flex flex-col flex-1">
                                        <label for="lineId" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>Line ID / Whatsapp</label>
                                        <input
                                            type="text"
                                            id="lineId"
                                            class="input input-bordered w-full input-field text-lg h-12"
                                            placeholder="Line ID / Whatsapp"
                                        />
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                    <div class="flex flex-col flex-1">
                                        <label for="phone" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>เบอร์โทรศัพท์</label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            class="input input-bordered w-full input-field text-lg h-12"
                                            placeholder="หมายเลขโทรศัพท์"
                                        />
                                        <div class="popup_span w-full hide"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Travel Information Card -->
                    <div class="bg-white rounded-xl shadow-md sticky top-8 p-4 sm:p-6 md:p-8">
                        <h2 class="text-2xl font-bold mb-4 text-black">ข้อมูลการเดินทาง</h2>
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h3 class="font-semibold text-lg mb-2">เที่ยวขาไป</h3>
                            <div class="flex flex-col md:flex-row gap-4 mb-4 flex-1">
                                <div class="flex flex-col flex-1">
                                    <label for="departStart" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>สถานที่ต้นทาง</label>
                                    <select
                                        id="departStart"
                                        class="select select-bordered w-full input-field text-lg h-12"
                                        name="departStart"
                                    >
                                        <option value="" disabled selected>เลือกสถานที่</option>
                                        <option value="SuvarnabhumiAirport">สนามบินสุวรรณภูมิ</option>
                                        <option value="DonMueangAirport">สนามบินดอนเมือง</option>
                                    </select>
                                    
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <label for="departStartNote" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>รายละเอียดสถานที่ / หมายเหตุ</label>
                                    <input
                                        type="text"
                                        id="departStartNote"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="โปรดระบุ"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row gap-4 mb-4 ">
                                <div class="flex flex-col flex-1">
                                    <label for="departHuaHin" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>สถานที่ปลายทาง</label>
                                    <select
                                        id="departHuaHin"
                                        class="select select-bordered w-full input-field text-lg h-12"
                                        name="departHuaHin"
                                    >
                                        <option value="">กำลังโหลด...</option> 
                                    </select>
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <label for="departHuaHinNote" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>รายละเอียดสถานที่ / หมายเหตุ</label>
                                    <input
                                        type="text"
                                        id="departHuaHinNote"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="โปรดระบุ"
                                    />                                
                                    <div class="popup_span w-full hide"></div>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row gap-4 mb-4">
                                <div class="flex flex-col flex-1">
                                    <label for="departDate" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>วันที่เดินทางไป</label>
                                    <input
                                        type="date"
                                        id="departDate"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="เลือกวันเดินทางไป"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1 relative">
                                    <label for="departTime" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>เวลานัดหมาย</label>
                                    <input
                                        type="text"
                                        id="departTime"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        value="09:00"
                                        placeholder="กรอกเวลานัดหมาย"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                    <div class="ib_departTime"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <span class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>ลักษณะเที่ยว</span>
                            <label for="trip-type" class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="trip-type"
                                    name="trip-type"
                                    class="form-checkbox text-blue-600 checkbox checkbox-primary text-white border border-primary"
                                />
                                <span class="ml-2">ไป-กลับ</span>
                            </label>
                        </div>
                        <!-- Return Trip -->
                        <div id="return-box" class="return-trip-section border-t border-gray-200 pt-4 mt-4" style="display:none;">
                            <h3 class="font-semibold text-lg mb-2">เที่ยวขากลับ</h3>
                            <div class="flex flex-col md:flex-row gap-4 mb-4">
                                <div class="flex flex-col flex-1">
                                    <label for="returnStart" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>สถานที่ต้นทาง</label>
                                    <select
                                        id="returnStart"
                                        class="select select-bordered w-full input-field text-lg h-12"
                                        name="returnStart"
                                    >
                                    <option value="">กำลังโหลด...</option>      
                                    </select>
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <label for="returnStartNote" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>รายละเอียดสถานที่ / หมายเหตุ</label>
                                    <input
                                        type="text"
                                        id="returnStartNote"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="โปรดระบุ"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row gap-4 mb-4">
                                <div class="flex flex-col flex-1">
                                    <label for="returnHuaHin" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>สถานที่ปลายทาง</label>
                                    <select
                                        id="returnHuaHin"
                                        class="select select-bordered w-full input-field text-lg h-12"
                                    >
                                        <option value="" disabled selected>เลือกสถานที่</option>
                                        <option value="SuvarnabhumiAirport">สนามบินสุวรรณภูมิ</option>
                                        <option value="DonMueangAirport">สนามบินดอนเมือง</option>
                                    </select>
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <label for="returnHuaHinNote" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>รายละเอียดสถานที่ / หมายเหตุ</label>
                                    <input
                                        type="text"
                                        id="returnHuaHinNote"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="โปรดระบุ"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row gap-4 mb-4">
                                <div class="flex flex-col flex-1">
                                    <label for="returnDate" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>วันที่เดินทางกลับ</label>
                                    <input
                                        type="date"
                                        id="returnDate"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        placeholder="เลือกวันเดินทางกลับ"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1 relative">
                                    <label for="returnTime" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>เวลานัดหมาย</label>
                                    <input
                                        type="text"
                                        id="returnTime"
                                        class="input input-bordered w-full input-field text-lg h-12"
                                        value="18:00"
                                        placeholder="กรอกเวลานัดหมาย"
                                    />
                                    <div class="popup_span w-full hide"></div>
                                    <div class="ib_returnTime"></div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h3 class="font-semibold text-lg mb-2"><span class="text-red-500 pr-2">*</span>ประเภทรถที่ต้องการ</h3>
                            <div class="mb-4">
                                <label for="carTypeSelect" class="sr-only">Car type</label>
                                <select
                                    id="carTypeSelect"
                                    class="select select-bordered w-full input-field text-lg h-12"
                                >
                                    <option value="" disabled selected>เลือกรถ</option>
                                    <option value="Standard Sedan">Standard Sedan</option>
                                    <option value="Premium Sedan">Premium Sedan</option>
                                    <option value="Standard MPV">Standard MPV</option>
                                    <option value="Toyota Fortuner">Toyota Fortuner</option>
                                    <option value="VIP Computer">VIP Computer</option>
                                    <option value="Hyundai H1">Hyundai H1</option>
                                </select>
                                <div class="popup_span w-full hide"></div>
                            </div>
                            <div class="flex flex-col md:flex-row gap-4 mb-4">
                                <div class="flex flex-col flex-1">
                                    <label for="adultCount" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>จำนวนผู้โดยสาร (ผู้ใหญ่)</label>
                                    <input
                                        type="range"
                                        id="adultCount"
                                        name="adultCount"
                                        class="range range-primary quantity-input w-full"
                                        value="0"
                                        min="0"
                                        max="10"
                                        required
                                    />
                                    <span class="cc-t flex items-center gap-2">
                                        <div id="adultCount-display" class="quantity-display">0</div>
                                        คน
                                    </span>
                                    <div class="popup_span w-full hide"></div>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <label for="childCount" class="block text-gray-700 font-medium mb-1">จำนวนผู้โดยสาร (เด็ก)</label>
                                    <input
                                        type="range"
                                        id="childCount"
                                        name="childCount"
                                        class="range range-primary quantity-input w-full"
                                        value="0"
                                        min="0"
                                        max="10"
                                        required
                                    />
                                    <span class="cc-t flex items-center gap-2">
                                        <div id="childCount-display" class="quantity-display">0</div>
                                        คน
                                    </span>
                                    <div class="popup_span w-full hide"></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="remark" class="block text-gray-700 font-medium mb-1">หมายเหตุ</label>
                                <input
                                    type="text"
                                    id="remark"
                                    class="input input-bordered w-full input-field h-12"
                                    placeholder="โปรดระบุข้อความ"
                                />
                                <div class="popup_span w-full hide"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Flight Information Card -->
                    <div class="bg-white rounded-xl shadow-md sticky top-8 p-4 sm:p-6 md:p-8">
                        <h2 class="text-2xl font-bold mb-4 text-black">ข้อมูลเที่ยวบิน</h2>
                        <div class="flex flex-col md:flex-row gap-4 mb-4">
                            <div class="flex flex-col flex-1 input-group">
                                <label for="arrivalFlightNo" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>กรอกชื่อเที่ยวบินขาเข้า (Arrival Flight No.)</label>
                                <input
                                    type="text"
                                    id="arrivalFlightNo" 
                                    class="input input-bordered w-full input-field h-12"
                                    placeholder="ระบุหมายเลขบินขาเข้า"
                                />
                                <div class="popup_data w-full hide"></div>
                                <div class="popup_span w-full hide"></div>
                                <input type="hidden" id="arrivalFlightNo_data" name="arrivalFlightNo_data">
                            </div>
                            <div class="flex flex-col flex-1 relative">
                                <label for="arrivalFlightTime" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>เวลาเที่ยวบินขาเข้า</label>
                                <input
                                    type="text"
                                    id="arrivalFlightTime"
                                    class="input input-bordered w-full input-field h-12"
                                    value="09:00"
                                    placeholder="กรอกเวลาเที่ยวบินขาเข้า"
                                />
                                <div class="popup_span w-full hide"></div>
                                <div class="ib_arrivalFlightTime"></div>
                            </div>
                        </div>
                        <div class="flex flex-col md:flex-row gap-4 mb-4">
                            <div class="flex flex-col flex-1 relative input-group">
                                <label for="departureFlightNo" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>กรอกชื่อเที่ยวบินขาออก (Departure Flight No.)</label>
                                <input
                                    type="text"
                                    id="departureFlightNo"
                                    class="input input-bordered w-full input-field h-12"
                                    placeholder="ระบุหมายเลขบินขาออก"
                                />
                                <div class="popup_data w-full hide"></div>
                                <div class="popup_span w-full hide"></div>
                                <input type="hidden" id="departureFlightNo_data" name="departureFlightNo_data">
                            </div>
                            <div class="flex flex-col flex-1 relative">
                                <label for="departureFlightTime" class="block text-gray-700 font-medium mb-1"><span class="text-red-500 pr-2">*</span>เวลาเที่ยวบินขาออก</label>
                                <input
                                    type="text"
                                    id="departureFlightTime"
                                    class="input input-bordered w-full input-field h-12"
                                    value="09:00"
                                    placeholder="กรอกเวลาเที่ยวบินขาออก"
                                />
                                <div class="popup_span w-full hide"></div>
                                <div class="ib_departureFlightTime"></div>
                            </div>
                        </div>
                        <!-- Coupon Section -->
                        <div class="coupon-section mb-4">
                            <label for="discountCode" class="block text-gray-700 font-medium mb-1">โค้ดส่วนลด</label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    id="discountCode"
                                    class="input input-bordered flex-grow input-field h-12"
                                    placeholder="#EarlyBird20%"
                                />
                                <button type="button" id="apply-coupon-btn" class="btn btn-primary">ใช้โค้ด</button>
                            </div>
                            <div id="coupon-message" class="text-sm mt-1 hidden"></div>
                            <div class="popup_span w-full hide"></div>
                        </div>
                        <div>
                            <label for="flightNote" class="block text-gray-700 font-medium mb-1">หมายเหตุ</label>
                            <textarea
                                id="flightNote"
                                class="textarea textarea-bordered input-field w-full h-24"
                            ></textarea>
                            <div class="popup_span w-full hide"></div>
                        </div>
                    </div>
                
            </div>

            <!-- Summary Section -->
            <div id="summary-rentcar" class="w-full lg:w-[30%] mt-8 lg:mt-0">
                <div class="bg-white rounded-xl shadow-md sticky top-8 p-4 sm:p-6 md:p-8">
                    <h2 class="text-2xl font-bold mb-4 text-black">สรุปรายละเอียด</h2>
                    
                    <!-- Personal Information Summary -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3 text-gray-800">ข้อมูลส่วนตัว</h3>
                        <div class="divide-y divide-gray-200 space-y-2">
                            <div class="py-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
                                <span class="font-medium text-gray-600">ชื่อ - นามสกุล:</span>
                                <span class="summary-fullname text-gray-800">-</span>
                            </div>
                            <div class="py-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
                                <span class="font-medium text-gray-600">Email:</span>
                                <span class="summary-email text-gray-800">-</span>
                            </div>
                            <div class="py-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
                                <span class="font-medium text-gray-600">Line ID:</span>
                                <span class="summary-lineId text-gray-800">-</span>
                            </div>
                            <div class="py-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
                                <span class="font-medium text-gray-600">เบอร์โทรศัพท์:</span>
                                <span class="summary-phone text-gray-800">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Travel Information Summary -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3 text-gray-800">ข้อมูลการเดินทาง</h3>
                        
                        <!-- Outbound Trip -->
                        <div class="mb-4 summary-trip-outbound">
                            <h4 class="font-medium text-md mb-2 text-black flex items-center gap-2">เที่ยวขาไป</h4>
                            <div class="divide-y divide-gray-200 space-y-2">
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">ต้นทาง:</span>
                                    <span class="summary-departStart summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">หมายเหตุ:</span>
                                    <span class="summary-departStartNote summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">ปลายทาง:</span>
                                    <span class="summary-departHuaHin summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">หมายเหตุ:</span>
                                    <span class="summary-departHuaHinNote summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">วันที่:</span>
                                    <span class="summary-departDate summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">เวลา:</span>
                                    <span class="summary-departTime summary-value text-sm text-gray-800">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Return Trip -->
                        <div id="summary-return-trip" class="mb-4 summary-trip-return" style="display: none;">
                            <h4 class="font-medium text-md mb-2 text-black flex items-center gap-2">เที่ยวขากลับ</h4>
                            <div class="divide-y divide-gray-200 space-y-2">
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">ต้นทาง:</span>
                                    <span class="summary-returnStart summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">หมายเหตุ:</span>
                                    <span class="summary-returnStartNote summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">ปลายทาง:</span>
                                    <span class="summary-returnHuaHin summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">หมายเหตุ:</span>
                                    <span class="summary-returnHuaHinNote summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">วันที่:</span>
                                    <span class="summary-returnDate summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">เวลา:</span>
                                    <span class="summary-returnTime summary-value text-sm text-gray-800">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="mb-4 summary-booking">
                            <h4 class="font-medium text-md mb-2 text-black flex items-center gap-2">รายละเอียดการจอง</h4>
                            <div class="divide-y divide-gray-200 space-y-2">
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">ประเภทรถ:</span>
                                    <span class="summary-carType summary-value text-sm text-gray-800">-</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">ผู้ใหญ่:</span>
                                    <span class="summary-adultCount summary-value text-sm text-gray-800">0 คน</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">เด็ก:</span>
                                    <span class="summary-childCount summary-value text-sm text-gray-800">0 คน</span>
                                </div>
                                <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                    <span class="text-sm text-gray-600">หมายเหตุ:</span>
                                    <span class="summary-remark summary-value text-sm text-gray-800">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flight Information Summary -->
                    <div class="mb-6 summary-flight">
                        <h3 class="font-semibold text-lg mb-3 text-black flex items-center gap-2">ข้อมูลเที่ยวบิน</h3>
                        <div class="divide-y divide-gray-200 space-y-2">
                            <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                <span class="text-sm text-gray-600">เที่ยวบินขาเข้า:</span>
                                <span class="summary-arrivalFlight summary-value text-sm text-gray-800">-</span>
                            </div>
                            <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                <span class="text-sm text-gray-600">เวลาขาเข้า:</span>
                                <span class="summary-arrivalFlightTime summary-value text-sm text-gray-800">-</span>
                            </div>
                            <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                <span class="text-sm text-gray-600">เที่ยวบินขาออก:</span>
                                <span class="summary-departureFlight summary-value text-sm text-gray-800">-</span>
                            </div>
                            <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                <span class="text-sm text-gray-600">เวลาขาออก:</span>
                                <span class="summary-departureFlightTime summary-value text-sm text-gray-800">-</span>
                            </div>
                            <div class="py-1 flex flex-col sm:flex-row justify-between items-start sm:items-center summary-data-row gap-1 sm:gap-0">
                                <span class="text-sm text-gray-600">โค้ดส่วนลด:</span>
                                <span class="summary-discountCode summary-value text-sm text-gray-800">-</span>
                            </div>
                            <div class="py-1 summary-data-row">
                                <span class="text-sm text-gray-600">หมายเหตุ:</span>
                                <div class="summary-flightNote summary-value text-sm text-gray-800 mt-1 break-words">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 my-4"></div>
                    <h3 class="font-bold text-lg mb-2">รายละเอียดราคา</h3>
                    <div class="space-y-2 mb-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
                            <span>ราคารวมทั้งหมด</span>
                            <span id="summary-total-price">1,000 บาท</span>
                        </div>
                        <div id="coupon-discount-row" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0 text-green-600 hidden">
                            <span>ส่วนลด</span>
                            <span id="summary-discount">-</span>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 my-4"></div>
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-xl font-bold mb-6 gap-1 sm:gap-0">
                        <span>รวม</span>
                        <span id="summary-grand-total">800 บาท</span>
                    </div>
                    <button id="carBookingFormBtn" type="submit" class="btn btn-primary w-full">จองทันที</button>
                </div>
            </div>
        </div>
        </form>
    </div>
</main>


<?php

// get_sidebar();
get_footer();
?>

