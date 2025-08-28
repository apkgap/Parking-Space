jQuery(document).ready(function($) {
    'use strict'; // Enable Strict Mode for better code quality

    // =========================================================================
    // 1. STATE & CONFIGURATION (การจัดการสถานะและค่าตั้งต้น)
    // =========================================================================
    let parkingData = [];
    // Stores selected spaces with their assigned vehicle type, quantity, and plate number
    let selectedSpaces = [];
    // Stores vehicle types and quantities from the form
    let vehicleData = [];
    let viewMode = 'status'; // Parking grid display mode: 'status', 'distance', 'zone'
    let vehicleRowCount = 1; // Counter for adding vehicle type rows

    // For Time Picker
    let pickerStartInstance = null;
    let pickerEndInstance = null;

    function parseThaiDateToISO(thaiDateStr) {
        // ตัวอย่าง input: "1 ก.ค. 2568"
        const months = {
            'ม.ค.': '01', 'ก.พ.': '02', 'มี.ค.': '03', 'เม.ย.': '04',
            'พ.ค.': '05', 'มิ.ย.': '06', 'ก.ค.': '07', 'ส.ค.': '08',
            'ก.ย.': '09', 'ต.ค.': '10', 'พ.ย.': '11', 'ธ.ค.': '12'
        };
        const parts = thaiDateStr.trim().split(' ');
        if (parts.length !== 3) return '';
        const day = parts[0].padStart(2, '0');
        const month = months[parts[1]];
        const year = (parseInt(parts[2], 10) - 543).toString();
        if (!month || !year) return '';
        return `${year}-${month}-${day}`; // <-- เปลี่ยนเป็น Y-m-d
    }

    function stripMicroseconds(timeStr) {
        // ตัด .000000 ออก และถ้าเป็น HH:mm ให้เติม :00 ท้าย
        if (typeof timeStr !== 'string') return timeStr;
        let clean = timeStr.replace(/\.\d+$/, '');
        if (/^\d{2}:\d{2}$/.test(clean)) clean += ':00';
        return clean;
    }



    // API Functions (เพิ่มส่วนนี้ใหม่)
async function fetchParkingData() {
    try {
        // Get selected location from locationSelect if it exists
        const locationSelect = document.getElementById('location1');
        let locationId = locationSelect ? locationSelect.value : null;
        
        // If no location is selected in the dropdown, check URL parameters
        if (!locationId || locationId === '') {
            const urlParams = new URLSearchParams(window.location.search);
            locationId = urlParams.get('locate_id');
        }
        
        // Build the API URL with location parameter if available
        let apiUrl = 'https://ok-parking.zlla.in/wp-json/custom/v1/parking_space';
        if (locationId) {
            apiUrl += `?locate_id=${locationId}`;
        }

        console.log('API URL:', apiUrl);
        
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('API parking data:', data);
        return data;
    } catch (error) {
        console.error('Error fetching parking data:', error);
        return null;
    }
}

async function fetchParkingBookingData() {
    try {
        const response = await fetch('https://ok-parking.zlla.in/wp-json/custom/v1/parking_booking');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        // Transform to include grid_x and grid_y
        const transformed = data.map(item => ({
            ...item,
            grid_x: item.position?.x,
            grid_y: item.position?.y
        }));
        console.log('API parking_booking data:', transformed);
        return transformed;
    } catch (error) {
        console.error('Error fetching parking_booking data:', error);
        return null;
    }
}

fetchParkingBookingData();

function transformToGridFormat(apiData) {
    if (!apiData || !Array.isArray(apiData)) {
        console.error('Invalid API data');
        return [];
    }

    const gridData = [];
    const groupedData = {};
    
    apiData.forEach(space => {
        const prefix = space.title.charAt(0).toLowerCase();
        if (!groupedData[prefix]) {
            groupedData[prefix] = [];
        }
        groupedData[prefix].push(space);
    });

    // Sort each group by the number in the title
    Object.keys(groupedData).forEach(key => {
        groupedData[key].sort((a, b) => {
            const numA = parseInt(a.title.substring(1));
            const numB = parseInt(b.title.substring(1));
            return numA - numB;
        });
    });

    const rowMapping = { 'a': 0, 'b': 1, 'c': 2, 'd': 3, 'e': 4, 'f': 5 };
    
    Object.keys(groupedData).forEach(prefix => {
        const spaces = groupedData[prefix];
        const base_grid_y = rowMapping[prefix];
        
        spaces.forEach((space, index) => {
            // ใช้ค่าจาก API ถ้ามี position
            let grid_x = space.position.x;
            let resolved_grid_y = space.position.y;

            gridData.push({
                id: space.title.toUpperCase(),
                parking_id: space.parking_id,
                grid_x: grid_x,
                grid_y: resolved_grid_y,
                status: space.parking_space_status === 'available' ? 'available' : 'occupied',
                original_data: space
            });
        });
    });

    return gridData;
}


    // =========================================================================
    // 2. DOM ELEMENT CACHING (เก็บ Element ที่ใช้งานบ่อย)
    // =========================================================================
    const dom = {
        locationSpaceParking: document.getElementById('location1'),
        totalSpaces: document.getElementById('total-spaces'),
        availableSpaces: document.getElementById('available-spaces'),
        occupiedSpaces: document.getElementById('occupied-spaces'),
        occupancyRate: document.getElementById('occupancy-rate'),
        gridContainer: document.getElementById('parking-grid-container'),
        legendContent: document.getElementById('legend-content'),
        selectedInfoPanel: document.getElementById('selected-space-info'),
        selectedId: document.getElementById('selected-id'),
        selectedStatus: document.getElementById('selected-status'),
        vehicleAssignments: document.getElementById('vehicle-assignments'),
        viewStatusBtn: document.getElementById('view-status-btn'),
        viewDistanceBtn: document.getElementById('view-distance-btn'),
        viewZoneBtn: document.getElementById('view-zone-btn'),
        toggleStatusBtn: document.getElementById('toggle-status-btn'),
        carReservationForm: document.getElementById('carReservationForm'),
        vehicleRowsContainer: document.getElementById('vehicle-rows-container'),
        addVehicleRowButton: document.getElementById('addVehicleRow'),
        errorMessage: document.getElementById('error-message'),
        timePicker: {
            startInput: document.getElementById('startTime'),
            endInput: document.getElementById('endTime'),
            startContainer: document.querySelector('.input_start_time'),
            endContainer: document.querySelector('.input_end_time'),

            // Addon: sending
            sendingFlightDepartContainer: document.querySelector('.input_sending_flight_depart_time'),
            sendingFlightArrivalContainer: document.querySelector('.input_sending_flight_arrival_time'),
            sendingPickupContainer: document.querySelector('.input_sending_pickup_time'),
            sendingDropoffContainer: document.querySelector('.input_sending_dropoff_time'),

            // Addon: parking
            parkingFlightDepartContainer: document.querySelector('.input_flight_depart_time'),
            parkingFlightArrivalContainer: document.querySelector('.input_flight_arrival_time'),
            parkingPickupContainer: document.querySelector('.input_pickup_time'),
            parkingDropoffContainer: document.querySelector('.input_dropoff_time'),
        },
        submitBtn: document.getElementById('submit-btn'),
        addon: {
            // Checkbox บริการเสริม
            servicePickup: document.getElementById('service_pickup'),
            serviceValet: document.getElementById('service_valet'),
            // ฟิลด์ใน info-sending_airport
            sending: {
                flightDepart: document.getElementById('sending_flight_depart'),
                flightDepartTime: document.getElementById('sending_flight_depart_time'),
                flightArrival: document.getElementById('sending_flight_arrival'),
                flightArrivalTime: document.getElementById('sending_flight_arrival_time'),
                pickupTime: document.getElementById('sending_pickup_time'),
                dropoffTime: document.getElementById('sending_dropoff_time'),
                pickupPoint: document.getElementById('sending_pickup_point'),
                dropoffPoint: document.getElementById('sending_dropoff_point'),
                adultCount: document.getElementById('sending_adult_count'),
                childCount: document.getElementById('sending_child_count'),
                baggageCount: document.getElementById('sending_baggage_count'),
                pickupNote: document.getElementById('sending_pickup_note'),
            },
            // ฟิลด์ใน info-parking_car
            parking: {
                flightDepart: document.getElementById('flight_depart'),
                flightDepartTime: document.getElementById('flight_depart_time'),
                flightArrival: document.getElementById('flight_arrival'),
                flightArrivalTime: document.getElementById('flight_arrival_time'),
                pickupTime: document.getElementById('pickup_time'),
                dropoffTime: document.getElementById('dropoff_time'),
                pickupPoint: document.getElementById('pickup_point'),
                dropoffPoint: document.getElementById('dropoff_point'),
                pickupNote: document.getElementById('pickup_note'),
            },
        },
    };

    // --- Hide grid and related UI initially ---
    if (dom.gridContainer) dom.gridContainer.style.display = 'none';
    if (dom.legendContent) dom.legendContent.style.display = 'none';
    if (dom.selectedInfoPanel) dom.selectedInfoPanel.style.display = 'none';
    // Hide submit button initially
    if (dom.submitBtn) {
        dom.submitBtn.style.display = 'none';
        dom.submitBtn.disabled = true;
    }

    // =========================================================================
    // 3. CORE LOGIC & HELPER FUNCTIONS (ฟังก์ชันหลักและฟังก์ชันช่วยเหลือ)
    // =========================================================================

    /**
     * Creates parking data from API or uses fallback data.
     * @returns {void}
     */
    async function initializeParkingData() {
        console.log('Fetching parking data from API...');
        const apiData = await fetchParkingData();
        if (!apiData) {
            console.error('API failed, no parking data loaded');
            parkingData = []; // ไม่ใช้ fallback ใดๆ
        } else {
            parkingData = transformToGridFormat(apiData);
            console.log('Parking data loaded from API:', parkingData.length, 'spaces');
        }
    }


    /**
     * Calculates the color of a parking space based on its status and the display mode.
     * @param {object} space - The parking space data.
     * @returns {string} - Tailwind CSS color classes.
     */
    const getSpaceColor = (space) => {
        if (!space) return 'bg-gray-100 path'; // Path (non-parking space)
        if (selectedSpaces.some(s => s.id === space.id)) return 'bg-blue-300 selected'; // Selected space
        if (space.status === 'occupied') return 'bg-red-500 occupied'; // Occupied space

        // Available space colors based on view mode
        switch (viewMode) {
            case 'status': return 'bg-green-500 available';
            case 'distance':
                const distance = space.grid_x + space.grid_y;
                if (distance < 5) return 'bg-blue-500';
                if (distance < 10) return 'bg-yellow-500';
                return 'bg-orange-500 text-white';
            case 'zone':
                if (space.grid_y < 2) return 'bg-purple-500';
                if (space.grid_y < 4) return 'bg-indigo-500';
                return 'bg-teal-500 text-white';
            default: return 'bg-gray-400';
        }
    };

    /**
     * Calculates the total quantity of all vehicles from the form.
     * @returns {number} - Total number of vehicles.
     */
    function getTotalVehicleQuantity() {
        return vehicleData.reduce((sum, vehicle) => sum + (parseInt(vehicle.quantity) || 0), 0);
    }

    /**
     * Updates the `vehicleData` array based on the current form inputs.
     * This function should be called whenever vehicle type or quantity inputs change.
     * @returns {void}
     */
    function updateVehicleDataFromForm() {
        vehicleData = [];
        // เช็คก่อนว่า container มีจริงไหม
        if (!dom.vehicleRowsContainer) return;
        const vehicleRows = dom.vehicleRowsContainer.querySelectorAll('.vehicle-row');
        vehicleRows.forEach((row, index) => {
            const carTypeSelect = row.querySelector('.car-type-select');
            const quantityInput = row.querySelector('.quantity-input');
            const otherCarTypeInput = row.querySelector('.other-car-type-input input');

            if (carTypeSelect && quantityInput) {
                let carType = carTypeSelect.value;
                if (carType === '1236' && otherCarTypeInput) {
                    carType = otherCarTypeInput.value.trim();
                }
                const quantity = parseInt(quantityInput.value) || 0;

                if (carType && quantity > 0) {
                    vehicleData.push({
                        type: carType,
                        quantity: quantity,
                        rowIndex: index // Keep track of the original row index for potential future use
                    });
                }
            }
        });
        // After updating vehicleData, re-render everything to reflect changes in selected spaces limit
        // renderAll();
    }


    /**
     * Matches vehicle types with selected parking spaces for display and data storage.
     * This function updates the `selectedSpaces` array with `vehicleType` and `quantity` information.
     * It ensures that the selected spaces are associated with the correct vehicle types from the form.
     * @returns {void}
     */
    function assignVehiclesToSpaces() {
        // Clear previous assignments to prevent stale data
        selectedSpaces.forEach(space => {
            delete space.assignedVehicleType;
            delete space.assignedVehicleQuantity;
        });

        let currentVehicleDataIndex = 0;
        let currentVehicleDataCount = 0; // Tracks how many spaces have been assigned from the current vehicleData entry

        selectedSpaces.forEach((space, spaceIndex) => {
            while (currentVehicleDataIndex < vehicleData.length) {
                const vehicle = vehicleData[currentVehicleDataIndex];
                if (currentVehicleDataCount < vehicle.quantity) {
                    space.assignedVehicleType = vehicle.type;
                    // For simplicity, we assign quantity 1 to each space. The overall count comes from vehicleData.
                    space.assignedVehicleQuantity = 1;
                    space.originalVehicleRowIndex = vehicle.rowIndex; // Store the original row index
                    currentVehicleDataCount++;
                    break; // Move to the next selected space
                } else {
                    currentVehicleDataIndex++;
                    currentVehicleDataCount = 0; // Reset for the next vehicle type
                }
            }
        });
    }

    // =========================================================================
    // 4. RENDERING & UI UPDATE FUNCTIONS (Rendering and UI Update Functions)
    // =========================================================================

    /**
     * Updates parking statistics.
     * @returns {void}
     */
    function renderStats() {
        console.log('current parkingData:', parkingData);
        const total = parkingData.length;
        const occupied = parkingData.filter(s => s.status === 'occupied').length;
        const available = total - occupied;
        const rate = total > 0 ? ((occupied / total) * 100).toFixed(1) : 0;

        if (dom.totalSpaces) dom.totalSpaces.textContent = total;
        if (dom.availableSpaces) dom.availableSpaces.textContent = available;
        if (dom.occupiedSpaces) dom.occupiedSpaces.textContent = occupied;
        if (dom.occupancyRate) dom.occupancyRate.textContent = rate;
    }

    /**
     * Updates the legend based on the selected mode.
     * @returns {void}
     */
    function renderLegend() {
        dom.legendContent.innerHTML = '';
        let items = [];
        switch (viewMode) {
            case 'status':
                items = [
                    { color: 'bg-red-500', text: 'ช่องจอดที่ใช้งานแล้ว' },
                    { color: 'bg-green-500', text: 'ช่องจอดว่าง' },
                    { color: 'bg-blue-300', text: 'ช่องจอดที่เลือก' },
                ];
                break;
            case 'distance':
                items = [
                    { color: 'bg-blue-500', text: 'ใกล้ทางเข้า' },
                    { color: 'bg-yellow-500', text: 'ระยะปานกลาง' },
                    { color: 'bg-orange-500', text: 'ไกลจากทางเข้า' },
                    { color: 'bg-blue-300', text: 'ช่องจอดที่เลือก' },
                ];
                break;
            case 'zone':
                items = [
                    { color: 'bg-purple-500', text: 'โซน A' },
                    { color: 'bg-indigo-500', text: 'โซน B' },
                    { color: 'bg-teal-500', text: 'โซน C' },
                    { color: 'bg-blue-300', text: 'ช่องจอดที่เลือก' },
                ];
                break;
        }
        items.push({ color: 'bg-gray-100 border border-gray-300', text: 'ทางเดิน' });
        items.forEach(item => {
            dom.legendContent.innerHTML += `
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 ${item.color} rounded"></div>
                    <span class="text-sm">${item.text}</span>
                </div>`;
        });
    }

    /**
     * Creates and displays the entire parking grid.
     * @returns {void}
     */
    function renderGrid() {
        dom.gridContainer.innerHTML = '';
        if (parkingData.length === 0) return;

        const maxX = Math.max(...parkingData.map(space => space.grid_x));
        const maxY = Math.max(...parkingData.map(space => space.grid_y));
        const grid = Array.from({ length: maxY + 1 }, () => Array(maxX + 1).fill(null));

        parkingData.forEach(space => { grid[space.grid_y][space.grid_x] = space; });
        dom.gridContainer.style.gridTemplateColumns = `repeat(${maxX + 1}, 1fr)`;

        grid.forEach((row, rowIndex) => {
            row.forEach((space, colIndex) => {
                const cell = document.createElement('div');
                // Get base color classes based on the view mode and status
                const color = getSpaceColor(space);
                cell.className = `parking-space-cell ${color}`;

                // กำหนดตำแหน่ง grid-column และ grid-row ตาม grid_x, grid_y
                if (space) {
                    cell.textContent = space.id;
                    cell.style.gridColumn = space.grid_x;
                    cell.style.gridRow = space.grid_y;

                    cell.title = `${space.id} - ${space.status}`;
                    cell.dataset.spaceId = space.id;

                    // Add click listener only for available spaces
                    if (space.status === 'available') {
                        cell.addEventListener('click', (event) => {
                            if (event.target === cell || event.target.closest('.parking-space-cell') === cell) {
                                handleSpaceClick(cell);
                            }
                        });
                    }
                } else {
                    cell.title = 'ทางเดิน';
                }
                dom.gridContainer.appendChild(cell);
            });
        });
    }

    /**
     * Updates the panel displaying information about selected spaces.
     * @returns {void}
     */
    function renderSelectedInfo() {
        if (selectedSpaces.length > 0) {
            dom.selectedInfoPanel.style.display = 'block';
            dom.selectedId.textContent = `เลือกแล้ว ${selectedSpaces.length} ช่อง`;
            dom.selectedStatus.textContent = ''; // Clear status when multiple spaces are selected
            dom.toggleStatusBtn.style.display = 'none'; // Hide unused button

            assignVehiclesToSpaces(); // Assign vehicle types to selected spaces for display

            dom.vehicleAssignments.innerHTML = '<h4 class="text-md font-medium mb-2">รายละเอียดการจอง:</h4>';
            if (selectedSpaces.length > 0) {
                const listItems = selectedSpaces.map(space => {
                    const assignedType = space.assignedVehicleType ? space.assignedVehicleType : 'ยังไม่ได้ระบุประเภทรถ';
                    return `<li>ช่อง ${space.id} สำหรับ ${assignedType}</li>`;
                }).join('');
                dom.vehicleAssignments.innerHTML += `<ul class="list-disc pl-5 text-sm text-gray-700">${listItems}</ul>`;
            } else {
                dom.vehicleAssignments.innerHTML += '<p class="text-sm text-gray-600">ยังไม่มีการมอบหมายรถ</p>';
            }
        } else {
            dom.selectedInfoPanel.style.display = 'none';
            dom.vehicleAssignments.innerHTML = '';
        }
    }

    /**
     * Updates the styles of the view mode control buttons.
     * @returns {void}
     */
    function updateControlButtons() {
        const buttons = { status: dom.viewStatusBtn, distance: dom.viewDistanceBtn, zone: dom.viewZoneBtn };
        const activeClass = 'px-4 py-2 rounded-lg font-medium bg-blue-500 text-white ';
        const inactiveClass = 'px-4 py-2 rounded-lg font-medium bg-white text-gray-700 hover:bg-gray-100 ';

        Object.values(buttons).forEach(btn => {
            if (btn) btn.className = inactiveClass;
        });
        if (buttons[viewMode]) {
            buttons[viewMode].className = activeClass;
        }
    }

    /**
     * Shows/hides the "other car type" input field.
     * @param {HTMLSelectElement} selectElement - The select element for car type.
     * @returns {void}
     */
    function updateOtherCarTypeVisibility(selectElement) {
        const row = selectElement.closest('.vehicle-row');
        const otherInputGroup = row?.querySelector('.other-car-type-input');
        const otherInput = otherInputGroup?.querySelector('input');

        if (!row || !otherInputGroup || !otherInput) return;

        if (selectElement.value === '1236') {
            otherInputGroup.classList.remove('hidden');
            otherInput.required = true;
        } else {
            otherInputGroup.classList.add('hidden');
            otherInput.required = false;
            otherInput.value = '';
        }
    }

    /**
     * Main function to trigger a full re-render of the UI.
     * @returns {void}
     */
    function renderAll() {
        renderStats();
        // updateControlButtons();
        renderLegend();
        renderGrid();
        renderSelectedInfo();
        // --- Hide grid/legend if not visible ---
        if (dom.gridContainer && dom.gridContainer.style.display === 'none') {
            dom.gridContainer.innerHTML = '';
        }
        if (dom.legendContent && dom.legendContent.style.display === 'none') {
            dom.legendContent.innerHTML = '';
        }

        // --- Show/hide and enable/disable submit button based on selection condition ---
        if (dom.submitBtn) {
            const totalVehiclesRequired = getTotalVehicleQuantity();
            if (
                selectedSpaces.length > 0 &&
                selectedSpaces.length === totalVehiclesRequired
            ) {
                dom.submitBtn.style.display = '';
                dom.submitBtn.disabled = false;
            } else {
                dom.submitBtn.style.display = 'none';
                dom.submitBtn.disabled = true;
            }
        }
    }

async function checkParkingAvailability(startDateTime, endDateTime, locationChoose) {
    try {
        const response = await $.post(ajaxurl_obj.ajaxurl, {
            action: 'check_parking_availability',
            startDateTime,  // ส่งเป็น datetime เลย
            endDateTime,
            locationChoose
        });
        if (response.success === true   ) {
            // console.log('Success from server:', response.data);
            return response.data || [];
        }

        if (response.success === false) {
            console.error('Error from server:', response.data);
            return [];
        }
        return response.data || [];
    } catch (error) {
        console.error('AJAX error:', error);
        return [];
    }
}

$('#btn-search_all').on('click', async function() {
    $('.box-loading').css('display', 'flex');

    await initializeParkingData();
    try {
        // แสดง loading ตั้งแต่เริ่มต้น        
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();
        let startTime = $('#startTime').val();
        let endTime = $('#endTime').val();
        let locationChoose = $('#location1').val();

        // ตรวจสอบ input ก่อน
        if (!startDate || !startTime || !endDate || !endTime) {
            dom.errorMessage.textContent = 'กรุณาเลือกวันที่และเวลาให้ครบถ้วนก่อนค้นหา';
            dom.errorMessage.classList.remove('hidden');
            $('html, body').animate({ scrollTop: dom.errorMessage.offsetTop - 100 }, 300);
            $('.box-loading').css('display', 'none');
            return;
        }
        if (!locationChoose) {
            dom.errorMessage.textContent = 'กรุณาเลือกสถานที่ให้ครบถ้วนก่อนค้นหา';
            dom.errorMessage.classList.remove('hidden');
            $('html, body').animate({ scrollTop: dom.errorMessage.offsetTop - 100 }, 300);
            $('.box-loading').css('display', 'none');
            return;
        }

        // แปลงวันที่และตัด microseconds
        startDate = parseThaiDateToISO(startDate);
        endDate = parseThaiDateToISO(endDate);
        startTime = stripMicroseconds(startTime);
        endTime = stripMicroseconds(endTime);

        // รวมเป็น datetime
        let startDateTime = `${startDate} ${startTime}`;
        let endDateTime = `${endDate} ${endTime}`;

        console.log('ส่งไป PHP:', startDateTime, endDateTime, locationChoose);

        // เรียกใช้ function ที่ปรับปรุงแล้ว
        const occupiedSpaces = await checkParkingAvailability(startDateTime, endDateTime, locationChoose);

        console.log('Occupied parking spaces from PHP:', occupiedSpaces);
        console.log('All parking spaces:', parkingData); 

         // อัพเดทสถานะที่จอด
         const occupiedIds = occupiedSpaces.map(s => String(s.id));
         const occupiedIdsString = String(occupiedIds);
         console.log('Occupied IDs:', occupiedIdsString);
         
         parkingData.forEach(space => {
             const spaceId = space.original_data?.id;
             const spaceTitle = space.id; // This is the space title like "A8"
             if (spaceId && occupiedIdsString.includes(spaceId)) {
                 console.log(`Marking ${spaceTitle} as occupied`);
                 space.status = 'occupied';
             } else {
                 space.status = 'available';
             }
         });

        // แสดงผลลัพธ์
        if (dom.gridContainer) dom.gridContainer.style.display = '';
        if (dom.legendContent) dom.legendContent.style.display = '';
        renderAll();

        // คำนวณจำนวนช่องจอดว่าง
        const availableCount = parkingData.filter(s => s.status === 'available').length;

        // แสดง popup ถ้าไม่มีที่ว่าง
        if (availableCount === 0) {
            showNoParkingPopup();
        }

        // ซ่อน error message ถ้ามี
        dom.errorMessage.classList.add('hidden');

    } catch (error) {
        console.error('Error in search:', error);
        dom.errorMessage.textContent = 'เกิดข้อผิดพลาดในการค้นหา กรุณาลองใหม่อีกครั้ง';
        dom.errorMessage.classList.remove('hidden');
    } finally {
        // ซ่อน loading เสมอ
        $('.box-loading').css('display', 'none');
    }
});


    // =========================================================================
    // 5. EVENT HANDLERS (Event Handlers)
    // =========================================================================

    /**
     * Handles clicks on parking space cells.
     * @param {HTMLElement} clickedCell - The clicked parking space cell element (div.parking-space-cell).
     * @returns {void}
     */
    function handleSpaceClick(clickedCell) {
        const spaceId = clickedCell.dataset.spaceId;
        if (!spaceId) return;

        const space = parkingData.find(s => s.id === spaceId);
        if (!space || space.status === 'occupied') return;

        const totalVehicles = getTotalVehicleQuantity();
        const selectedIndex = selectedSpaces.findIndex(s => s.id === space.id);

        if (selectedIndex === -1) { // If not already selected
            if (selectedSpaces.length < totalVehicles) {
                // Add without plateNumber, just assignedVehicleType and assignedVehicleQuantity
                selectedSpaces.push({
                    ...space,
                    assignedVehicleType: null,
                    assignedVehicleQuantity: 0
                });
                dom.errorMessage.classList.add('hidden');
            } else {
                dom.errorMessage.textContent = `คุณสามารถเลือกช่องจอดได้สูงสุด ${totalVehicles} ช่องเท่านั้น`;
                dom.errorMessage.classList.remove('hidden');
            }
        } else { // If already selected (deselect)
            selectedSpaces.splice(selectedIndex, 1);
            dom.errorMessage.classList.add('hidden');
        }

        renderAll(); // Re-render the grid to reflect changes
    }

    /**
     * Adds a new row for vehicle type and quantity input.
     * @returns {void}
     */
    function addVehicleRow() {
    vehicleRowCount++;
    const newRowHtml = `
        <div class="vehicle-row border-b border-gray-300 pb-6 mb-6">
            <div class="vehicle-row-content flex flex-wrap gap-5">
                <div class="input-group w-full md:flex-1">
                    <label for="carType${vehicleRowCount}">ประเภทรถ (Car type)*</label>
                    <select id="carType${vehicleRowCount}" name="carType${vehicleRowCount}" class="input-field bg-white car-type-select" required>
                        <option value="">กำลังโหลด...</option>
                    </select>
                </div>

                <div class="input-group w-full md:flex-1">
                    <label for="quantity${vehicleRowCount}">จำนวน (Quantity)*</label>
                    <input type="range" id="quantity${vehicleRowCount}" name="quantity${vehicleRowCount}" class="range range-primary quantity-input" value="0" min="0" max="10" required>
                    <span class="cc-t flex items-center gap-2">
                        <div id="quantity-display-${vehicleRowCount}" class="quantity-display">0</div>
                        คัน
                    </span>
                </div>

                <div class="input-group other-car-type-input hidden w-full">
                    <label for="otherCarType${vehicleRowCount}">โปรดระบุประเภทรถอื่นๆ</label>
                    <input type="text" id="otherCarType${vehicleRowCount}" name="otherCarType${vehicleRowCount}" class="input-field bg-white" placeholder="ระบุประเภทรถ...">
                </div>
            </div>
            ${vehicleRowCount > 1 ? '<button type="button" class="remove-vehicle-row-button text-red-500 hover:text-red-700 text-sm mt-2">ลบแถวนี้</button>' : ''}
        </div>
    `;
    dom.vehicleRowsContainer.insertAdjacentHTML('beforeend', newRowHtml);

    const newRow = dom.vehicleRowsContainer.lastElementChild;
    const newSelect = newRow.querySelector('.car-type-select');
    const newQuantityInput = newRow.querySelector('.quantity-input');
    const newRemoveButton = newRow.querySelector('.remove-vehicle-row-button');

    const currentOrigin = window.location.origin;
    // ดึงข้อมูลจาก API แล้วสร้าง <option>
    fetch(`${currentOrigin}/wp-json/custom/v1/vehicle_type`)
        .then(response => response.json())
        .then(data => {
            // เรียงลำดับข้อมูลตาม title แบบ ascending
            data.sort((a, b) => {
                const titleA = a.title.toUpperCase(); // ไม่สนใจ case-sensitive
                const titleB = b.title.toUpperCase(); // ไม่สนใจ case-sensitive
                if (titleA < titleB) {
                    return -1;
                }
                if (titleA > titleB) {
                    return 1;
                }
                return 0;
            });

            newSelect.innerHTML = '<option value="">เลือกประเภทรถ</option>';
            data.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.id;
                option.textContent = vehicle.title.replace('->', '→'); // ทำให้ดูดีขึ้น
                newSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('โหลด vehicle types ไม่สำเร็จ:', error);
            newSelect.innerHTML = '<option value="">ไม่สามารถโหลดข้อมูล</option>';
        });

    // เพิ่ม event
    if (newSelect) {
        newSelect.addEventListener('change', (event) => {
            updateOtherCarTypeVisibility(event.target);
            updateVehicleDataFromForm();
            updateSummaryBox(); 
        });
    }
    if (newQuantityInput) {
        const quantityDisplay = newRow.querySelector(`#quantity-display-${vehicleRowCount}`);
        // Check if container already exists to prevent duplication
        let vehicleInputsContainer = newRow.querySelector('.vehicle-inputs-container');
        if (!vehicleInputsContainer) {
            vehicleInputsContainer = document.createElement('div');
            vehicleInputsContainer.className = 'vehicle-inputs-container mt-2 space-y-2 flex w-full flex-col gap-3';
            newRow.querySelector('.vehicle-row-content').appendChild(vehicleInputsContainer);
        }

        newQuantityInput.addEventListener('input', (event) => {
            const quantity = parseInt(event.target.value) || 0;
            quantityDisplay.textContent = quantity;
            
            // Get the selected vehicle type
            const carTypeSelect = newRow.querySelector('.car-type-select');
            let vehicleType = 'รถ';
            if (carTypeSelect && carTypeSelect.selectedIndex > 0) {
                vehicleType = carTypeSelect.options[carTypeSelect.selectedIndex].text;
            }
            
            // Make sure the container is empty before generating new inputs
            if (vehicleInputsContainer) {
                vehicleInputsContainer.innerHTML = '';
                console.log(`Cleared vehicle inputs container for row ${vehicleRowCount} before generating new ones`);
                // Use the new function to generate inputs
                generateVehicleInputs(vehicleInputsContainer, quantity, vehicleRowCount, vehicleType);
            }
            
            updateVehicleDataFromForm();
            updateSummaryBox();
        });
        
        newQuantityInput.addEventListener('change', updateVehicleDataFromForm);
    }
    if (newRemoveButton) {
        newRemoveButton.addEventListener('click', () => {
            newRow.remove();
            updateVehicleDataFromForm();
            updateSummaryBox(); 
        });
    }

    updateVehicleDataFromForm();
    updateSummaryBox();
}

/**
 * Handles form submission.
 * @param {Event} event - The form submit event.
 * @returns {void}
 */
async function handleSubmit(event) {
    event.preventDefault(); // Prevent default form submission

    $('.box-loading').css('display', 'flex');

    // Clear previous error messages
    dom.errorMessage.classList.add('hidden');
    dom.errorMessage.textContent = '';

    // --- Validate .req-oc fields ---
    let isValid = true;
    $('.req-oc').each(function() {
        const $field = $(this);
        clearFieldError($field);
        if (!$field.val() || $field.val().trim() === '') {
            showFieldError($field, 'กรุณากรอกข้อมูลให้ครบถ้วน');
            isValid = false;
        }
    });
    if (!isValid) {
        $('.box-loading').css('display', 'none');
        // Scroll to first error
        const $firstError = $('.req-oc.error').first();
        if ($firstError.length) {
            $('html, body').animate({ scrollTop: $firstError.offset().top - 100 }, 300);
            $firstError.focus();
        }
        return;
    }

    const formData = new FormData(dom.carReservationForm);
    const reservationDetails = {};
    const locationSpaceParking = dom.locationSpaceParking.value; 
    
    // Add location information to reservation details
    reservationDetails.location = locationSpaceParking;
    // Add profile information to reservation details
    reservationDetails.profile = {
        fullname: $('#fullname').val() || '',
        email: $('#email').val() || '',
        phone: $('#phone').val() || '',
        lineid: $('#lineid').val() || ''
    };

    // --- แปลงวันที่ก่อนเก็บลง reservationDetails ---
    let startDate = formData.get('startDate');
    let endDate = formData.get('endDate');
    if (startDate) startDate = parseThaiDateToISO(startDate);
    if (endDate) endDate = parseThaiDateToISO(endDate);
    
    // Get time values from form
    const startTime = formData.get('startTime') || '';
    const endTime = formData.get('endTime') || '';

    const totalQty = vehicleData.reduce((sum, v) => sum + (parseInt(v.quantity) || 0), 0);
    
    // Add time fields to reservationDetails
    reservationDetails.startTime = startTime;
    reservationDetails.endTime = endTime;

    reservationDetails.startDate = startDate;
    reservationDetails.endDate = endDate;

    reservationDetails.pricePerCar = pricePerCar;
    reservationDetails.totalPrice = pricePerCar * totalQty;

    // Add selectedSpaces and vehicleData to the submission
    reservationDetails.selectedParkingSpaces = selectedSpaces.map((space, index) => {
        // Find the vehicle plate input for this specific space
        let licensePlate = '';
        
        // Get the row index for this space from the vehicleData array
        const rowIndex = space.originalVehicleRowIndex || 0;
        
        // We need to calculate this based on how many vehicles have been assigned to this row
        let vehicleIndex = 1; 
        
        // Count how many spaces have been assigned to the same row before this one
        for (let i = 0; i < index; i++) {
            if (selectedSpaces[i].originalVehicleRowIndex === rowIndex) {
                vehicleIndex++;
            }
        }
        
        // Try to find the input field for this specific vehicle
        const plateInput = document.getElementById(`vehicle_plate_${rowIndex + 1}_${vehicleIndex}`);
        
        if (plateInput) {
            licensePlate = plateInput.value;
        } else {
            // Fallback: try to find any plate input for this row
            const plateInputs = document.querySelectorAll(`input[name^='vehicle_plate_${rowIndex + 1}_']`);
            if (plateInputs.length >= vehicleIndex) {
                licensePlate = plateInputs[vehicleIndex - 1].value;
            }
        }
        
        return {
            id: space.id,
            parking_id: space.parking_id,
            parking_post_id: space.original_data.id,
            assignedVehicleType: space.assignedVehicleType,
            assignedVehicleQuantity: space.assignedVehicleQuantity,
            licensePlate: licensePlate
        };
    });
    reservationDetails.vehicleTypeQuantities = vehicleData;

    // เพิ่ม addon ให้อยู่ระดับเดียวกับ vehicleTypeQuantities
    reservationDetails.addon = {
        servicePickup: dom.addon.servicePickup ? dom.addon.servicePickup.checked : false,
        serviceValet: dom.addon.serviceValet ? dom.addon.serviceValet.checked : false,
        sending: {
            flightDepart: dom.addon.sending.flightDepart?.value || '',
            flightDepartTime: dom.addon.sending.flightDepartTime?.value || '',
            flightArrival: dom.addon.sending.flightArrival?.value || '',
            flightArrivalTime: dom.addon.sending.flightArrivalTime?.value || '',
            pickupTime: dom.addon.sending.pickupTime?.value || '',
            dropoffTime: dom.addon.sending.dropoffTime?.value || '',
            pickupPoint: dom.addon.sending.pickupPoint?.value || '',
            dropoffPoint: dom.addon.sending.dropoffPoint?.value || '',
            adultCount: dom.addon.sending.adultCount?.value || '',
            childCount: dom.addon.sending.childCount?.value || '',
            baggageCount: dom.addon.sending.baggageCount?.value || '',
            pickupNote: dom.addon.sending.pickupNote?.value || '',
        },
        parking: {
            flightDepart: dom.addon.parking.flightDepart?.value || '',
            flightDepartTime: dom.addon.parking.flightDepartTime?.value || '',
            flightArrival: dom.addon.parking.flightArrival?.value || '',
            flightArrivalTime: dom.addon.parking.flightArrivalTime?.value || '',
            pickupTime: dom.addon.parking.pickupTime?.value || '',
            dropoffTime: dom.addon.parking.dropoffTime?.value || '',
            pickupPoint: dom.addon.parking.pickupPoint?.value || '',
            dropoffPoint: dom.addon.parking.dropoffPoint?.value || '',
            pickupNote: dom.addon.parking.pickupNote?.value || '',
        }
    };

    // --- Validation Logic ---
    const totalVehiclesRequired = getTotalVehicleQuantity();
    if (selectedSpaces.length === 0) {
        dom.errorMessage.textContent = 'กรุณาเลือกช่องจอดอย่างน้อยหนึ่งช่อง';
        dom.errorMessage.classList.remove('hidden');
        $('.box-loading').css('display', 'none');
        return;
    }

    if (selectedSpaces.length !== totalVehiclesRequired) {
        dom.errorMessage.textContent = `จำนวนช่องจอดที่เลือก (${selectedSpaces.length}) ไม่ตรงกับจำนวนรถทั้งหมด (${totalVehiclesRequired}) ที่ระบุในฟอร์ม.`;
        dom.errorMessage.classList.remove('hidden');
        $('.box-loading').css('display', 'none');
        return;
    }

    // Set the value of the hidden input field
    if (document.getElementById('formDataJson')) {
        document.getElementById('formDataJson').value = JSON.stringify(reservationDetails);
    } else {
        console.warn("Hidden input field 'formDataJson' not found. Data will not be stored there.");
    }

    console.log('Submission Data:', reservationDetails);

    // ส่งข้อมูลไป backend ด้วย AJAX
    try {
        const response = await $.ajax({
            url: ajaxurl_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'insert_parking_booking',
                bookingData: JSON.stringify(reservationDetails)
            },
            dataType: 'json'
        });
        
        $('.box-loading').css('display', 'none');
        if (response.success) {
             console.log('ข้อมูลที่ส่งไป:', response);
            // --- เปลี่ยนสถานะช่องที่เลือกเป็น occupied ---
            selectedSpaces.forEach(sel => {
                const idx = parkingData.findIndex(space => space.id === sel.id);
                if (idx !== -1) {
                    parkingData[idx].status = 'occupied';
                }
            });
            renderAll();
            // --- Redirect to thank you page with order code and email ---
            const email = document.querySelector('#email')?.value || '';
            window.location.href = '/thank-you?order_code=' + response.data.order_code + '&email=' + encodeURIComponent(email);
        } else {
            // Show error message
            const errorMessage = response.data || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง';
            $('.error-message').remove();
            const $alert = $('<div class="alert alert-error error-message">' + errorMessage + '</div>');
            $('.btn.btn-primary.w-full, #submit-btn').parent().prepend($alert);
        }
    } catch (error) {
        $('.box-loading').css('display', 'none');
        // Show error message
        const errorMessage = 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์ กรุณาตรวจสอบการเชื่อมต่อและลองใหม่อีกครั้ง';
        $('.error-message').remove();
        const $alert = $('<div class="alert alert-error error-message">' + errorMessage + '</div>');
        $('.btn.btn-primary.w-full, #submit-btn').parent().prepend($alert);
    }
}


/**
 * Initializes the time pickers and sets up click-outside-to-close behavior.
 * This function assumes 'dom' object and 'pickerStartInstance', 'pickerEndInstance'
 * global variables (or variables accessible in this scope) are defined elsewhere.
 * It also assumes Picker.js library is loaded.
 * @returns {void}
 */
function initializeTimePickers() {
    if (typeof Picker === 'undefined') return;

    // กำหนดฟิลด์เวลาและ container (ถ้ามี)
    const timeFields = [
        // Main time pickers
        { input: dom.timePicker.startInput, container: dom.timePicker.startContainer, defaultValue: '09:00', title: 'เลือกเวลาเริ่มต้น' },
        { input: dom.timePicker.endInput, container: dom.timePicker.endContainer, defaultValue: '18:00', title: 'เลือกเวลาสิ้นสุด' },

        // Addon: sending
        { input: dom.addon.sending.flightDepartTime, container: dom.timePicker.sendingFlightDepartContainer, title: 'เวลาออกเดินทาง (ขาไป)', defaultValue: '09:00' },
        { input: dom.addon.sending.flightArrivalTime, container: dom.timePicker.sendingFlightArrivalContainer, title: 'เวลาเดินทางถึง (ขากลับ)', defaultValue: '12:00' },
        { input: dom.addon.sending.pickupTime, container: dom.timePicker.sendingPickupContainer, title: 'เวลารับ', defaultValue: '07:00' },
        { input: dom.addon.sending.dropoffTime, container: dom.timePicker.sendingDropoffContainer, title: 'เวลาส่ง', defaultValue: '20:00' },

        // Addon: parking
        { input: dom.addon.parking.flightDepartTime, container: dom.timePicker.parkingFlightDepartContainer, title: 'เวลาออกเดินทาง (ขาไป)', defaultValue: '09:00' },
        { input: dom.addon.parking.flightArrivalTime, container: dom.timePicker.parkingFlightArrivalContainer, title: 'เวลาเดินทางถึง (ขากลับ)', defaultValue: '12:00' },
        { input: dom.addon.parking.pickupTime, container: dom.timePicker.parkingPickupContainer, title: 'เวลารับ', defaultValue: '07:00' },
        { input: dom.addon.parking.dropoffTime, container: dom.timePicker.parkingDropoffContainer, title: 'เวลาส่ง', defaultValue: '20:00' }
    ];

    const pickerOptions = {
        format: 'HH:mm',
        increment: { minute: 1 },
        headers: true,
        controls: true,
        inline: true,
        rows: 4,
        text: {
            title: 'เลือกเวลา',
            cancel: 'ยกเลิก',
            confirm: 'ตกลง'
        }
    };

    timeFields.forEach(field => {
        if (field.input) {
            new Picker(field.input, {
                ...pickerOptions,
                container: field.container || undefined,
                value: field.input.value || field.defaultValue,
                text: { ...pickerOptions.text, title: field.title }
            });

            // ซ่อน picker ตอนแรก (เฉพาะ inline)
            let pickerElem = field.container
                ? field.container.querySelector('.picker')
                : field.input.parentElement.querySelector('.picker');
            if (pickerElem) pickerElem.style.display = 'none';

            // แสดง picker เมื่อคลิก input
            field.input.addEventListener('click', (e) => {
                e.stopPropagation();
                if (pickerElem) pickerElem.style.display = 'block';
            });

            // ซ่อน picker เมื่อคลิกข้างนอก
            document.addEventListener('click', (e) => {
                if (
                    pickerElem &&
                    !field.input.contains(e.target) &&
                    !(field.container && field.container.contains(e.target)) &&
                    !pickerElem.contains(e.target)
                ) {
                    pickerElem.style.display = 'none';
                }
            });
        }
    });
}



    // Event Listeners
    if (dom.viewStatusBtn) {
        dom.viewStatusBtn.addEventListener('click', () => { viewMode = 'status'; renderAll(); });
    }
    if (dom.viewDistanceBtn) {
        dom.viewDistanceBtn.addEventListener('click', () => { viewMode = 'distance'; renderAll(); });
    }
    if (dom.viewZoneBtn) {
        dom.viewZoneBtn.addEventListener('click', () => { viewMode = 'zone'; renderAll(); });
    }

    if (dom.addVehicleRowButton) { // Check if element exists before adding listener
        dom.addVehicleRowButton.addEventListener('click', addVehicleRow);
    }


    // Initial event listeners for the first vehicle row
    const initialCarTypeSelect = document.getElementById('carType1');
    const initialQuantityInput = document.getElementById('quantity1');
    const initialQuantityDisplay = document.getElementById('quantity-display-1');

    if (initialCarTypeSelect) {
        initialCarTypeSelect.addEventListener('change', (event) => {
            updateOtherCarTypeVisibility(event.target);
            updateVehicleDataFromForm(); // Update data when type changes
        });
    }
    if (initialQuantityInput) {
        // Check if container already exists to prevent duplication
        let vehicleInputsContainer = initialQuantityInput.closest('.vehicle-row-content').querySelector('.vehicle-inputs-container');
        if (!vehicleInputsContainer) {
            vehicleInputsContainer = document.createElement('div');
            vehicleInputsContainer.className = 'vehicle-inputs-container mt-2 space-y-2 flex w-full flex-col gap-3';
            initialQuantityInput.closest('.vehicle-row-content').appendChild(vehicleInputsContainer);
        }

        initialQuantityInput.addEventListener('input', (event) => {
            const quantity = parseInt(event.target.value) || 0;
            initialQuantityDisplay.textContent = quantity;
            
            // Get the selected vehicle type
            const carTypeSelect = document.querySelector('.car-type-select');
            let vehicleType = null;
            if (carTypeSelect && carTypeSelect.selectedIndex > 0) {
                vehicleType = carTypeSelect.options[carTypeSelect.selectedIndex].text;
            }
            
            // Make sure the container is empty before generating new inputs
            if (vehicleInputsContainer) {
                vehicleInputsContainer.innerHTML = '';
                console.log('Cleared vehicle inputs container before generating new ones');
                generateVehicleInputs(vehicleInputsContainer, quantity, '1', vehicleType);
            }
            
            updateVehicleDataFromForm();
            updateSummaryBox();
        });
        initialQuantityInput.addEventListener('change', updateVehicleDataFromForm); // Also on change for final value
    }


    // Event listener for form submission
    if (dom.carReservationForm) {
        dom.carReservationForm.removeEventListener('submit', handleSubmit); // ป้องกันซ้ำ
        dom.carReservationForm.addEventListener('submit', handleSubmit);
    }


    // Initial setup
    initializeParkingData().then(() => {
        updateVehicleDataFromForm();
        // Do not render grid/legend at first
        renderStats();
        // updateControlButtons();
        // Hide submit button on load
        if (dom.submitBtn) {
            dom.submitBtn.style.display = 'none';
            dom.submitBtn.disabled = true;
        }
    });
    initializeTimePickers();
    updateVehicleDataFromForm();
    // Do not call renderAll() here to avoid showing grid before search

    // --- TailwindCSS Popup for "No Parking Available" ---
function showNoParkingPopup() {
    let popup = document.getElementById('no-parking-popup');
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'no-parking-popup';
        popup.innerHTML = `
            <div class="fixed inset-0 flex items-center justify-center z-[9999] bg-black/40">
                <div class="bg-white rounded-xl shadow-2xl p-8 max-w-xs w-full flex flex-col items-center animate-bounce-in">
                    <svg class="w-16 h-16 text-red-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="#fee2e2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6" stroke="#ef4444" stroke-width="2"/>
                    </svg>
                    <div class="text-xl font-bold text-red-600 mb-2">ไม่พบที่จอดว่าง</div>
                    <div class="text-gray-700 mb-4 text-center">ขออภัย ไม่พบช่องจอดว่างในช่วงเวลาที่เลือก กรุณาลองใหม่อีกครั้ง</div>
                    <button id="close-no-parking-popup" class="mt-2 px-5 py-2 rounded-lg bg-blue-500 text-white font-semibold hover:bg-blue-600 transition">ปิด</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
        // Animation
        const style = document.createElement('style');
        style.innerHTML = `
        @keyframes bounce-in {
            0% { transform: scale(0.7); opacity: 0; }
            60% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }
        .animate-bounce-in { animation: bounce-in 0.35s cubic-bezier(.4,2,.6,1) both; }
        `;
        document.head.appendChild(style);
    } else {
        popup.style.display = '';
    }
    // Close button
    document.getElementById('close-no-parking-popup').onclick = function() {
        popup.style.display = 'none';
    };
    
}

let pricePerCar = 0; // เริ่มต้นเป็น 0

async function fetchParkingPrice() {
    try {
        const response = await fetch('/wp-json/mytheme/v1/settings');
        const data = await response.json();
        if (data && data.price_parking) {
            pricePerCar = parseFloat(data.price_parking) || 0;
        }
        updateSummaryBox();
    } catch (e) {
        pricePerCar = 0;
        updateSummaryBox();
    }
}
fetchParkingPrice();

function updateSummaryBox() {
    // ดึงค่าจากฟอร์ม
    const startDate = $('#startDate').val() || '-';
    const startTime = $('#startTime').val() || '-';
    const endDate = $('#endDate').val() || '-';
    const endTime = $('#endTime').val() || '-';
    const locationSelect = document.getElementById('location1');
    const location = locationSelect ? locationSelect.options[locationSelect.selectedIndex].text : '-';

    // --- ดึงข้อมูลประเภทรถและจำนวนจากทุกแถว ---
    let carTypeSummary = [];
    const vehicleRows = document.querySelectorAll('.vehicle-row');
    vehicleRows.forEach(row => {
        const carTypeSelect = row.querySelector('.car-type-select');
        const quantityInput = row.querySelector('.quantity-input');
        const otherCarTypeInput = row.querySelector('.other-car-type-input input');
        let carTypeText = '-';
        if (carTypeSelect) {
            if (carTypeSelect.value === '1236' && otherCarTypeInput && otherCarTypeInput.value.trim()) {
                carTypeText = otherCarTypeInput.value.trim();
            } else if (carTypeSelect.selectedIndex > 0) {
                carTypeText = carTypeSelect.options[carTypeSelect.selectedIndex].text;
            }
        }
        const qty = quantityInput ? parseInt(quantityInput.value) || 0 : 0;
        if (carTypeText !== '-' && qty > 0) {
            carTypeSummary.push({ type: carTypeText, qty });
        }
    });

    // สร้าง HTML กลุ่มแนวตั้ง
    let carTypeDisplay = '-';
    if (carTypeSummary.length > 0) {
        carTypeDisplay = carTypeSummary.map(item => `
            <div class="list-type_car_x mb-2 flex gap-2 justify-between">
                <div class="l-1 flex flex-col justify-between gap-2">
                    <div class="t-1 text-sm">${item.type}</div>
                </div>
                <div class="l-2 flex flex-col justify-between gap-2">
                    <div class="t-2 text-sm">${item.qty} คัน</div>
                </div>    
            </div>
        `).join('');
    }
    const totalQty = carTypeSummary.reduce((sum, item) => sum + item.qty, 0);
    const quantityDisplay = `${totalQty} คัน`;

    // เช็ค element ก่อนเซ็ตค่า
    const priceElem = document.getElementById('summary-price-per-car');
    const totalElem = document.getElementById('summary-total');
    if (priceElem) {
        priceElem.textContent = pricePerCar > 0 ? `${pricePerCar.toLocaleString()} บาท/คัน` : 'รอโหลดราคา...';
    }
    if (totalElem) {
        totalElem.textContent = pricePerCar > 0 ? `${(pricePerCar * totalQty).toLocaleString()} บาท` : '-';
    }
    const locationElem = document.getElementById('summary-location');
    if (locationElem) locationElem.textContent = location;
    const startDateElem = document.getElementById('summary-start-date');
    if (startDateElem) startDateElem.textContent = startDate;
    const startTimeElem = document.getElementById('summary-start-time');
    if (startTimeElem) startTimeElem.textContent = startTime;
    const endDateElem = document.getElementById('summary-end-date');
    if (endDateElem) endDateElem.textContent = endDate;
    const endTimeElem = document.getElementById('summary-end-time');
    if (endTimeElem) endTimeElem.textContent = endTime;
    // ถ้ามี summary-car-type ให้แสดง carTypeDisplay (HTML)
    const carTypeElem = document.getElementById('summary-car-type');
    if (carTypeElem) carTypeElem.innerHTML = carTypeDisplay;
    const carQtyElem = document.getElementById('summary-car-qty');
    if (carQtyElem) carQtyElem.textContent = quantityDisplay;
}

// เรียกทุกครั้งที่ input หรือ select ในฟอร์มเปลี่ยน
$('#carReservationForm').on('input change', updateSummaryBox);

// เรียกครั้งแรกเมื่อโหลดหน้า
updateSummaryBox();

// Fix duplicate addVehicleRow binding - clear old listeners first
const addVehicleRowBtn = document.getElementById('addVehicleRow');
if (addVehicleRowBtn) {
    // Remove all existing listeners by cloning and replacing
    const newBtn = addVehicleRowBtn.cloneNode(true);
    addVehicleRowBtn.parentNode.replaceChild(newBtn, addVehicleRowBtn);
    
    // Add single listener
    newBtn.addEventListener('click', addVehicleRow);
    console.log('AddVehicleRow button fixed - single listener applied');
}

// Validate all .req-oc fields on form submit
$('#carReservationForm').on('submit', function(e) {
    let isValid = true;
    $('.req-oc').each(function() {
        const $field = $(this);
        clearFieldError($field);
        if (!$field.val() || $field.val().trim() === '') {
            showFieldError($field, 'กรุณากรอกข้อมูลให้ครบถ้วน');
            isValid = false;
        }
    });
    if (!isValid) {
        e.preventDefault();
        // Scroll to first error
        const $firstError = $('.req-oc.error').first();
        if ($firstError.length) {
            $('html, body').animate({ scrollTop: $firstError.offset().top - 100 }, 300);
            $firstError.focus();
        }
    }
});

// Add error class on validation fail
function showFieldError($field, message) {
    if (!$field || !$field.length || typeof $field.siblings !== 'function' || !$field.siblings('.popup_span').length) {
        $('.error-message').remove();
        const $alert = $('<div class="alert alert-error error-message">' + message + '</div>');
        $('.btn.btn-primary.w-full, #submit-btn').parent().prepend($alert);
        $field.addClass('error');
        return;
    }
    $field.siblings('.popup_span').empty();
    $field.siblings('.popup_span').removeClass('hide');
    $field.siblings('.popup_span').addClass('flex').append(`
        <div role="alert" class="alert alert-error mt-2 w-full text-center">
            <span>${message}</span>
        </div>
    `);
    $field.addClass('error');
}

// Remove error class and message on input/change
function clearFieldError($field) {
    $field.siblings('.popup_span').empty().addClass('hide').removeClass('flex');
    $field.removeClass('error');
}

// Clear errors on input/change
$('input, select, textarea').on('input change', function() {
    clearFieldError($(this));
});

function generateVehicleInputs(container, quantity, rowCount, customVehicleType) {
    // Clear container before generating new inputs
    container.innerHTML = '';
    
    console.log(`Generating vehicle inputs: rowCount=${rowCount}, quantity=${quantity}`);
    
    // Safety check - if container already has inputs, clear them first
    if (container.querySelectorAll('.vehicle-input-group').length > 0) {
        console.log('Container already has inputs, clearing them first');
        container.innerHTML = '';
    }
    
    if (quantity > 0) {
        const header = document.createElement('div');
        header.className = 'text-sm sm:text-base lg:text-[16px] text-primary';
        
        let vehicleType = customVehicleType;
        const carTypeSelect = container.closest('.vehicle-row').querySelector('.car-type-select');
        if (!vehicleType) {
            vehicleType = carTypeSelect?.options[carTypeSelect?.selectedIndex]?.text || 'รถ';
        }
        
        header.textContent = `${vehicleType} จำนวน ${quantity} คัน`;
        container.appendChild(header);

        // Update header text when vehicle type select changes
        if (carTypeSelect) {
            const updateHeader = () => {
                const vt = carTypeSelect.options[carTypeSelect.selectedIndex]?.text || 'รถ';
                header.textContent = `${vt} จำนวน ${quantity} คัน`;
            };
            // Prevent binding multiple times
            if (!carTypeSelect.dataset.headerBound) {
                carTypeSelect.addEventListener('change', updateHeader);
                carTypeSelect.dataset.headerBound = '1';
            }
        }
    }
    
    for (let i = 1; i <= quantity; i++) {
        const inputGroup = document.createElement('div');
        inputGroup.className = 'w-full vehicle-input-group';
        inputGroup.innerHTML = `
            <div class="flex flex-col md:flex-row gap-4 mb-2 vehicle-row-item">
                <div class="input-group flex-1 vehicle-input-container">
                    <label for="vehicle_plate_${rowCount}_${i}" class="text-sm sm:text-base lg:text-[16px] vehicle-label"><span class="text-red-500 pr-2">*</span>ทะเบียนรถคันที่ ${i}:</label>
                    <div class="relative input-wrapper">
                        <input type="text" 
                            id="vehicle_plate_${rowCount}_${i}" 
                            name="vehicle_plate_${rowCount}_${i}" 
                            class="input-field bg-white req-oc w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out tabienrod" 
                            placeholder="ระบุเลขทะเบียนรถ" 
                            >
                        <button type="button" 
                            class="tabien-select-btn mt-2 px-3 py-2 bg-primary hover:bg-primary text-white rounded-md transition duration-150 text-sm cursor-pointer font-medium shadow-sm hover:shadow-md" 
                            data-row-index="${rowCount}_${i}"
                            onclick="openTabienPopup('vehicle_plate_${rowCount}_${i}')">
                            เลือกทะเบียน
                        </button>
                        <div class="popup_span w-full hide absolute left-0 top-full error-message"></div>
                    </div>
            </div>
        `;
        container.appendChild(inputGroup);
    }
    
    // Check login status for newly created buttons
    if (typeof checkLoginStatus === 'function') {
        checkLoginStatus();
    }
    
    // Check if the button exists in the DOM
    console.log('Vehicle inputs generated successfully');
}
});
