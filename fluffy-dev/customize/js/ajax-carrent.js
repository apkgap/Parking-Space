jQuery(document).ready(function($) {
    var isSubmitting = false;
    var loadingTimeout; // เพิ่มตัวแปรสำหรับจัดการ timeout

    $('#carBookingFormBtn').on('click', function(e) {
        e.preventDefault();

        // Prevent double submission
        if (isSubmitting) {
            return;
        }

        // Basic form validation
        if (!validateForm()) {
            return;
        }

        isSubmitting = true;

        // Show loading and disable button
        $('.box-loading').css('display', 'flex');
        $(this).prop('disabled', true).text('Processing...');

        // กำหนด timeout สำหรับ loading box (30 วินาที)
        loadingTimeout = setTimeout(function() {
            $('.box-loading').css('display', 'none');
            $('#carBookingFormBtn').prop('disabled', false).text('จองทันที');
            isSubmitting = false;
            showFieldError($('#carBookingFormBtn'), 'การประมวลผลใช้เวลานานเกินไป กรุณาลองใหม่อีกครั้ง');
        }, 30000);

        // Clear previous error messages
        $('.error-message').remove();

        // รวบรวมข้อมูลจาก hidden inputs ที่สร้างจาก service-carrent
        var selectedPackageData = {};
        $('.selected-package-hidden').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (name && value) {
                // แปลงชื่อ field ให้ตรงกับ structure ใน carrent.php
                if (name === 'selected_package_id') {
                    selectedPackageData.package_id = value;
                } else if (name === 'selected_package_route') {
                    selectedPackageData.route = value;
                } else if (name === 'selected_package_car_type') {
                    selectedPackageData.car_type = value;
                } else if (name === 'selected_package_price') {
                    selectedPackageData.price = value;
                }
            }
        });

        // Build form data
        var fields = {
            personal_information: {
                full_name: $('#fullname').val(),
                email: $('#email').val(),
                line_id_whatsapp: $('#lineId').val(),
                phone_number: $('#phone').val()
            },
            // เพิ่มข้อมูลแพ็คเกจที่เลือกจาก service-carrent
            selected_package: selectedPackageData,
            travel_information_outbound_trip: {
                depart_start_location: $('#departStart').val(),
                depart_start_location_note: $('#departStartNote').val(),
                depart_destination: $('#departHuaHin').val(),
                depart_destination_note: $('#departHuaHinNote').val(),
                depart_date: $('#departDate').val() ? $('#departDate').val().replace(/-/g, '') : '',
                depart_time: $('#departTime').val()
            },
            trip_type: $('#trip-type').is(':checked') ? 1 : 0,
            travel_information_return_trip: {
                return_start_location: $('#returnStart').val(),
                return_start_location_note: $('#returnStartNote').val(),
                return_destination: $('#returnHuaHin').val(),
                return_destination_note: $('#returnHuaHinNote').val(),
                return_date: $('#returnDate').val() ? $('#returnDate').val().replace(/-/g, '') : '',
                return_time: $('#returnTime').val()
            },
            booking_details: {
                car_type: $('#carTypeSelect').val(),
                adult_count: $('#adultCount').val(),
                child_count: $('#childCount').val(),
                remark_booking: $('#remark').val(),
                price_per_trip: pricePerTrip,
                total_price: totalPrice
            },
            flight_information: {
                arrival_flight_no: $('#arrivalFlightNo').val(),
                arrival_flight_time: $('#arrivalFlightTime').val(),
                departure_flight_no: $('#departureFlightNo').val(),
                departure_flight_time: $('#departureFlightTime').val(),
                discount_code: $('#discountCode').val(),
                remark_flight: $('#flightNote').val()
            }
        };

        // console.log('Form fields data:', fields);

        // รวบรวมข้อมูล hidden inputs และเพิ่มลงใน data ที่ส่ง
        var ajaxData = {
            action: 'insert_car_rent_acf',
            nonce: ajax_carrent_obj.nonce,
            fields: fields
        };

        // เพิ่มข้อมูล hidden inputs ลงใน AJAX data
        $('.selected-package-hidden').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (name && value) {
                ajaxData[name] = value;
            }
        });

        // AJAX request
        $.ajax({
            url: ajax_carrent_obj.ajax_url,
            type: 'POST',
            dataType: 'json',
            timeout: 30000,
            data: ajaxData,
            success: function(res) {
                // ยกเลิก timeout เมื่อได้ response สำเร็จ
                clearTimeout(loadingTimeout);
                
                if (res.success) {
                    // แสดง loading อีก 2 วินาทีก่อน redirect
                    setTimeout(function() {
                        // Get email from form
                        var email = $('#email').val();
                        window.location.href = '/thank-you?ref=' + res.data.booking_reference + '&email=' + encodeURIComponent(email);
                    }, 2000);
                } else {                
                    showFieldError($('#carBookingFormBtn'), res.data?.message || 'An error occurred while creating your booking');
                }
            },
            error: function(xhr, status, error) {
                // ยกเลิก timeout เมื่อเกิด error
                clearTimeout(loadingTimeout);
                
                console.error('AJAX Error:', { xhr, status, error });
                let errorMessage = 'Unable to process your booking. Please try again.';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please check your connection and try again.';
                } else if (xhr.responseJSON?.data?.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                
                // Display error message to user
                showFieldError($('#carBookingFormBtn'), errorMessage);
            },
            complete: function() {
                // ยกเลิก timeout และซ่อน loading (เฉพาะเมื่อไม่ redirect)
                if (loadingTimeout) {
                    clearTimeout(loadingTimeout);
                }
                isSubmitting = false;
                $('.box-loading').css('display', 'none');
                $('#carBookingFormBtn').prop('disabled', false).text('จองทันที');
            }
        });
    });

    function validateForm() {
        let isValid = true;
        let errors = [];

        // Required fields validation
        const requiredFields = {
            '#fullname': 'Full name is required',
            '#email': 'Email is required',
            '#phone': 'Phone number is required',
            '#lineId': 'Line ID/WhatsApp is required',
            '#departStart': 'Departure location is required',
            '#departStartNote': 'Departure location note is required',
            '#departHuaHin': 'Departure destination is required',
            '#departHuaHinNote': 'Departure destination note is required',
            '#departDate': 'Departure date is required',
            '#departTime': 'Departure time is required',
            '#carTypeSelect': 'Car type is required',
            '#adultCount': 'Number of adults is required',
            '#childCount': 'Number of children is required',
            '#arrivalFlightNo': 'Arrival flight number is required',
            '#arrivalFlightTime': 'Arrival flight time is required',
            '#departureFlightNo': 'Departure flight number is required',
            '#departureFlightTime': 'Departure flight time is required'
        };

        // Check if it's a round trip and validate return trip fields
        if ($('#trip-type').is(':checked')) {
            const returnFields = {
                '#returnStart': 'Return start location is required',
                '#returnStartNote': 'Return start location note is required',
                '#returnHuaHin': 'Return destination is required',
                '#returnHuaHinNote': 'Return destination note is required',
                '#returnDate': 'Return date is required',
                '#returnTime': 'Return time is required'
            };
            Object.assign(requiredFields, returnFields);
        }

        // ลบ alert-error เมื่อ input มีการเปลี่ยนแปลงหรือพิมพ์
        $('input, select, textarea').on('input change', function() {
            $(this).siblings('.alert-error').remove();
            $(this).removeClass('error');
        });

        $.each(requiredFields, function(selector, message) {
            const $field = $(selector);
            $field.siblings('.alert-error').remove(); // ลบ error เดิม
            if (!$field.val()) {
                $field.addClass('error');
                showFieldError($field, message); // แสดง error ใต้ input
                errors.push(message);
                isValid = false;
            } else {
                $field.removeClass('error');
            }
        });



        // Email validation
        if (!validateEmailField()) {
            errors.push('Please enter a valid email address');
            isValid = false;
        }
        // Passenger count validation (เรียกใช้ฟังก์ชัน)
        if (!validatePassengerCount($('#adultCount'))) {
            errors.push('จำนวนผู้โดยสาร (ผู้ใหญ่) ต้องอยู่ระหว่าง 0-10 คน');
            isValid = false;
        }
        if (!validatePassengerCount($('#childCount'))) {
            errors.push('จำนวนผู้โดยสาร (เด็ก) ต้องอยู่ระหว่าง 0-10 คน');
            isValid = false;
        }

        // Show validation errors
        if (!isValid) {
            // showFieldError(errors.join('<br>'));
        }

        return isValid;
    }

    function showSuccessMessage(message) {
        const $alert = $('<div class="alert alert-success success-message">' + message + '</div>');
        $('.btn.btn-primary.w-full').parent().prepend($alert);
        setTimeout(() => {
            $alert.fadeOut();
        }, 5000);
    }

    function resetForm() {
        $('form')[0].reset();
        $('.error').removeClass('error');
        $('.error-message, .success-message').remove();
    }

    function clearFieldError($field) {
        $field.siblings('.popup_span').empty().addClass('hide').removeClass('flex');
        $field.removeClass('error');
    }

    // Clear errors on input
    $('input, select, textarea').on('input change', function() {
        clearFieldError($(this));
    });

    function showFieldError($field, message) {
        // ตรวจสอบว่า $field เป็น jQuery object และมี popup_span เป็น sibling
        if (!$field || !$field.length || typeof $field.siblings !== 'function' || !$field.siblings('.popup_span').length) {
            // fallback: แสดง error ด้านบนปุ่ม submit
            $('.error-message').remove();
            const $alert = $('<div class="alert alert-error error-message">' + message + '</div>');
            $('.btn.btn-primary.w-full').parent().prepend($alert);
            return;
        }
        // โค้ดเดิม
        $field.siblings('.popup_span').empty();
        $field.siblings('.popup_span').removeClass('hide');
        $field.siblings('.popup_span').addClass('flex').append(`
            <div role="alert" class="alert alert-error mt-2 w-full text-center">
                
                <span>${message}</span>
            </div>
        `);
    }

    // Real-time validation
    $('input[required], select[required]').on('blur', function() {
        if ($(this).val() && $(this).val().trim()) {
            $(this).removeClass('error');
        }
    });

    // Trip type change handler
    $('#trip-type').on('change', function() {
        const isRoundTrip = $(this).is(':checked');
        const $returnSection = $('.return-trip-section'); // Assuming you have a section for return trip

        if (isRoundTrip) {
            $returnSection.show();
            // Add required attribute to return trip fields
            $('#returnStart, #returnStartNote, #returnHuaHin, #returnHuaHinNote, #returnDate, #returnTime').prop('required', true);
        } else {
            $returnSection.hide();
            // Remove required attribute and clear values
            $('#returnStart, #returnStartNote, #returnHuaHin, #returnHuaHinNote, #returnDate, #returnTime').prop('required', false).val('').removeClass('error');
        }
    });

    function validateEmailField() {
        const email = $('#email').val();
        $('#email').siblings('.alert-error').remove();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#email').addClass('error');
            showFieldError($('#email'), 'Please enter a valid email address');
            return false;
        } else {
            $('#email').removeClass('error');
            return true;
        }
    }

    function validatePassengerCount($field) {
        const val = parseInt($field.val(), 10);
        let message = '';
        if ($field.attr('id') === 'adultCount') {
            message = 'จำนวนผู้โดยสาร (ผู้ใหญ่) ต้องอยู่ระหว่าง 0-10 คน';
        } else {
            message = 'จำนวนผู้โดยสาร (เด็ก) ต้องอยู่ระหว่าง 0-10 คน';
        }
        if (isNaN(val) || val < 1 || val > 10) {
            $field.addClass('error');
            showFieldError($field, message);
            return false;
        } else {
            $field.removeClass('error');
            clearFieldError($field);
            return true;
        }
    }


    // Time field validation (HH:MM format)
    $('#departTime, #returnTime, #arrivalFlightTime, #departureFlightTime').on('input blur', function() {
        const time = $(this).val();
        if (time && !/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(time)) {
            $(this).addClass('error');
            showFieldError('Please enter time in HH:MM format (e.g., 14:30)');
        } else {
            $(this).removeClass('error');
        }
    });

    // Phone number validation (basic Thai phone format)
    // $('#phone').on('input blur', function() {
    //     const phone = $(this).val();
    //     if (phone && !/^[0-9\-\s\+\(\)]{8,15}$/.test(phone)) {
    //         $(this).addClass('error');
    //         showFieldError('Please enter a valid phone number');
    //     } else {
    //         $(this).removeClass('error');
    //     }
    // });

    // เรียก validateEmailField แบบ real-time
    $('#email').on('input blur', function() {
        validateEmailField();
    });

    $('select').on('change', function() {
        const $field = $(this);
        if ($field.val() === "") {
            $field.addClass('error');
            showFieldError($field, 'กรุณาเลือกข้อมูล');
        } else {
            clearFieldError($field);
        }
    });

    $('#adultCount, #childCount').on('input blur', function() {
        const val = parseInt($(this).val(), 10);
        if (isNaN(val) || val < 1 || val > 10) {
            $(this).addClass('error');
            showFieldError($(this), $(this).attr('id') === 'adultCount'
                ? 'จำนวนผู้โดยสาร (ผู้ใหญ่) ต้องอยู่ระหว่าง 0-10 คน'
                : 'จำนวนผู้โดยสาร (เด็ก) ต้องอยู่ระหว่าง 0-10 คน'
            );
        } else {
            $(this).removeClass('error');
            clearFieldError($(this));
        }
    });

    $('#adultCount, #childCount').on('input blur', function() {
        validatePassengerCount($(this));
    });

    $('#adultCount, #childCount').on('input blur', function() {
        const val = parseInt($(this).val(), 10);
        if (isNaN(val) || val < 1 || val > 10) {
            $(this).addClass('error');
            showFieldError($(this), $(this).attr('id') === 'adultCount'
                ? 'จำนวนผู้โดยสาร (ผู้ใหญ่) ต้องอยู่ระหว่าง 0-10 คน'
                : 'จำนวนผู้โดยสาร (เด็ก) ต้องอยู่ระหว่าง 0-10 คน'
            );
        } else {
            $(this).removeClass('error');
            clearFieldError($(this));
        }
    });

    let pricePerTrip = 0;
    let totalPrice = 800;

    async function fetchCarrentPrice() {
        try {
            const res = await fetch('/wp-json/mytheme/v1/settings');
            const data = await res.json();
            if (data && data.price_carrent) {
                pricePerTrip = parseFloat(data.price_carrent) || 0;
            }
        } catch (e) {
            pricePerTrip = 0;
        }
        updateSummaryPrice(); // เรียกที่นี่
    }
    fetchCarrentPrice();

    function updateSummaryPrice() {
        $('#summary-total-price').text(pricePerTrip.toLocaleString() + ' บาท');
        // $('#summary-discount').text(discountPercent + '%');
        $('#summary-grand-total').text(totalPrice.toLocaleString() + ' บาท');
    }

    // เรียกทุกครั้งที่คำนวณราคาใหม่ เช่น
    $('#trip-type, #discountCode, #adultCount, #childCount').on('change input', function() {
        // ...คำนวณ totalPrice, discountPercent ใหม่...
        updateSummaryPrice();
    });

});