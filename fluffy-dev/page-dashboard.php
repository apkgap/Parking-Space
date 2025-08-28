<?php
/*
 Template Name: Dashboard Page
 This template is used to display a thank you page after a successful booking and payment.
 It includes information about the booking, confirmation, and next steps for the user.
*/

get_header();

?>
<script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">
    
    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <style>
        div#parkingTable_paginate {
    display: none;
}
        .text-blue-500 {
            color: #29B2E2!important;
        }
        .bg-blue-500 {
                background-color: #29B2E2!important;
            }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #29B2E2;
            color: white;
        }
        .sidebar-item.active {
            background-color: #29B2E2;
            color: white;
        }
        
        /* Tab System Styles */
        .content-tab {
            display: none;
        }
        
        .content-tab.active {
            display: block;
        }
        
        /* Custom DataTable styles */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 1px solid #d1d5db;
            background: white;
            color: #374151 !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #29B2E2 !important;
            color: white !important;
            border-color: #29B2E2;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_filter {
            display: none; /* Hide default search */
        }
        
        .dataTables_wrapper .dataTables_length {
            display: none; /* Hide default length */
        }
        
        /* Custom row striping colors */
        #parkingTable tbody tr:nth-child(odd) {
            background-color: #eff6ff; /* Light blue */
        }
        
        #parkingTable tbody tr:nth-child(even) {
            background-color: #ffffff; /* White */
        }
        
        #parkingTable tbody tr:hover {
            background-color: #dbeafe !important; /* Slightly darker blue on hover */
        }
        
        /* Ensure the striping works with DataTables */
        .dataTable tbody tr.odd {
            background-color: #eff6ff !important;
        }
        
        .dataTable tbody tr.even {
            background-color: #ffffff !important;
        }
        
        .dataTable tbody tr.odd:hover,
        .dataTable tbody tr.even:hover {
            background-color: #dbeafe !important;
        }
        .entry-header{
            display: none!important;
        }
        .v-container {
    max-width: 1340px;
}
        
        /* Mobile Navigation Styles */
        .mobile-menu-button {
            display: none;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        .sidebar {
            transition: transform 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-button {
                display: block;
            }
        
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 100;
                transform: translateX(-100%);
                width: 280px;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }
            
            .flex.min-h-screen {
                flex-direction: column;
                min-height: auto;
            }
            
            .flex-1.flex.flex-col {
                margin-left: 0;
                width: 100%;
                min-height: auto;
            }
            
            /* Fix mobile layout issues */
            #ty-page {
                padding: 1rem 0;
                margin-bottom: 2rem;
            }
            
            .v-container {
                padding: 0 1rem;
            }
        }
        
        /* Table Responsive Styles */
        .wrap-top-table {
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .wrap-top-table h3 {
            flex: 1;
            min-width: 200px;
        }
        
        .wrap-top-table > div:last-child {
            flex: 1;
            min-width: 300px;
            justify-content: flex-end;
        }
        
        /* Mobile Table Styles */
        @media (max-width: 1024px) {
            .wrap-top-table {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .wrap-top-table > div:last-child {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            
            #customSearch {
                width: 100%;
                max-width: none;
            }
            
            #exportSelect, #filterBtn {
                flex: 1;
                min-width: 120px;
            }
        }
        
        @media (max-width: 640px) {
            .wrap-top-table > div:last-child {
                flex-direction: column;
                align-items: stretch;
            }
            
            #customSearch, #exportSelect, #filterBtn {
                width: 100%;
            }
            
            /* Hide some table columns on small screens */
            #parkingTable th:nth-child(5),
            #parkingTable td:nth-child(5),
            #parkingTable th:nth-child(6),
            #parkingTable td:nth-child(6),
            #parkingTable th:nth-child(7),
            #parkingTable td:nth-child(7) {
                display: none;
            }
            
            /* Make table text smaller on mobile */
            #parkingTable {
                font-size: 0.875rem;
            }
            
            #parkingTable th,
            #parkingTable td {
                padding: 0.5rem 0.25rem;
            }
            
            /* Adjust table header text */
            #parkingTable th {
                font-size: 0.75rem;
            }
            
            /* Make status badges smaller */
            #parkingTable .bg-green-100,
            #parkingTable .bg-red-100 {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            /* Even more aggressive hiding on very small screens */
            #parkingTable th:nth-child(3),
            #parkingTable td:nth-child(3),
            #parkingTable th:nth-child(4),
            #parkingTable td:nth-child(4) {
                display: none;
            }
            
            /* Adjust padding for very small screens */
            .p-6 {
                padding: 1rem;
            }
            
            .p-4 {
                padding: 0.75rem;
            }
            
            /* Make pagination responsive */
            #customPagination .flex {
                flex-direction: row;
                gap: 0.5rem;
                align-items: center;
            }
            
            #tableInfo {
                text-align: center;
                font-size: 0.75rem;
            }
            
            #tablePagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.25rem;
            }
            
            #tablePagination button {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            /* Fix mobile table container */
            .bg-white.rounded-lg.shadow {
                margin-bottom: 2rem;
                overflow: hidden;
            }
            
            /* Ensure table is fully visible */
            .overflow-x-auto {
                max-width: 100vw;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Mobile specific table adjustments */
            #parkingTable {
                min-width: 320px;
            }
        }
    </style>

            <div id="ty-page" class="min-h-screen py-10 mb-[130px]">
                <div class="max-w-full w-full mx-auto">
                        <div class="v-container">
                            <div class="flex min-h-screen flex-col md:flex-row">
                    
                    
                    <!-- Sidebar Overlay for Mobile -->
                    <div id="sidebarOverlay" class="sidebar-overlay"></div>
                    
                    <!-- Sidebar -->
                    <div id="sidebar" class="sidebar w-64 bg-white md:relative md:translate-x-0">
                        <!-- Close button for mobile -->
                        <div class="flex justify-end p-4 md:hidden">
                            <button id="closeSidebar" class="text-gray-600 hover:text-gray-800">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    
                        <nav class="mt-4 sidebar-nav px-4">
                            <a href="#" data-nav="tab1" class="sidebar-item active flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- Dashboard: Home icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 11h1v6a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1v-6h1a1 1 0 00.707-1.707l-7-7z"/>
                                </svg>
                                แดชบอร์ด
                            </a>
                            <a href="#" data-nav="tab2" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- การจองที่จอดรถ: Calendar Check icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6zm2.293 5.293a1 1 0 011.414 0l1 1 2-2a1 1 0 111.414 1.414l-2.707 2.707a1 1 0 01-1.414 0l-1.293-1.293a1 1 0 010-1.414z"/>
                                </svg>
                                การจองที่จอดรถ
                            </a>
                            <a href="#" data-nav="tab3" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- การจองรถส่วนตัว: Car icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 13V9.5A2.5 2.5 0 015.5 7h9A2.5 2.5 0 0117 9.5V13M3 13h14M3 13v2a1 1 0 001 1h1a1 1 0 001-1v-1h6v1a1 1 0 001 1h1a1 1 0 001-1v-2"/>
                                    <circle cx="7" cy="16" r="1"/>
                                    <circle cx="13" cy="16" r="1"/>
                                </svg>
                                การจองรถส่วนตัว
                            </a>
                            <a href="#" data-nav="tab4" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- การจัดการระบบรถรับส่ง: Bus icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <rect x="3" y="5" width="14" height="8" rx="2"/>
                                    <path d="M3 13h14M7 16a1 1 0 11-2 0 1 1 0 012 0zm8 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                                </svg>
                                การจัดการระบบรถรับส่ง
                            </a>
                            <a href="#" data-nav="tab5" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- การติดตามเที่ยวบิน: Airplane icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.18 2.09a1 1 0 011.64 0l6.5 9.75a1 1 0 01-.82 1.56H13v4a1 1 0 01-2 0v-4H2.5a1 1 0 01-.82-1.56l6.5-9.75z"/>
                                </svg>
                                การติดตามเที่ยวบิน
                            </a>
                            <a href="#" data-nav="tab6" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- การจัดการคนขับ: User Group icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM2 17a6 6 0 1112 0H2z"/>
                                </svg>
                                การจัดการคนขับ
                            </a>
                            <a href="#" data-nav="tab7" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- รายงานการเงิน: Chart Bar icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                                รายงานการเงิน
                            </a>
                            <a href="#" data-nav="tab8" class="sidebar-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-500 hover:text-white">
                                <!-- ตั้งค่าระบบ: Cog/Settings icon -->
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046a1 1 0 00-2.6 0l-.25.832a1 1 0 01-.95.69h-.9a1 1 0 00-.98.804l-.2.8a1 1 0 01-.7.7l-.8.2a1 1 0 00-.804.98v.9a1 1 0 01-.69.95l-.832.25a1 1 0 000 2.6l.832.25a1 1 0 01.69.95v.9a1 1 0 00.804.98l.8.2a1 1 0 01.7.7l.2.8a1 1 0 00.98.804h.9a1 1 0 01.95.69l.25.832a1 1 0 002.6 0l.25-.832a1 1 0 01.95-.69h.9a1 1 0 00.98-.804l.2-.8a1 1 0 01.7-.7l.8-.2a1 1 0 00.804-.98v-.9a1 1 0 01.69-.95l.832-.25a1 1 0 000-2.6l-.832-.25a1 1 0 01-.69-.95v-.9a1 1 0 00-.804-.98l-.8-.2a1 1 0 01-.7-.7l-.2-.8a1 1 0 00-.98-.804h-.9a1 1 0 01-.95-.69l-.25-.832zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                                ตั้งค่าระบบ
                            </a>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 flex flex-col main-content min-h-0 relative">


                        <!-- Mobile Menu Button -->
                        <button id="mobileMenuButton" class="mobile-menu-button w-10 h-10 absolube top-4 left-4 z-50 p-2 bg-blue-500 text-white rounded-md md:hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>


                    <!-- Tab 1: Dashboard Overview -->
                    <div class="content-tab active" data-tab="tab1">
                        <?php require_once('dashboard/tab1.php'); ?>
                    </div>

                    <!-- Tab 2: การจองที่จอดรถ -->
                    <div class="content-tab" data-tab="tab2">
                    <?php //require_once('dashboard/tab2.php'); ?>    
                    
                    <?php require_once('dashboard/parking-management/parking-tab.php'); ?>
                    </div>

                    <!-- Tab 3: การจองรถส่วนตัว -->
                    <div class="content-tab" data-tab="tab3">
                    <?php require_once('dashboard/carrental-management/carrental-tab.php'); ?>
                    </div>

                    <!-- Tab 4: การจัดการระบบรถรับส่ง -->
                    <div class="content-tab" data-tab="tab4">
                        <?php require_once('dashboard/transport-management/transport-tab.php'); ?>
                    </div>

                    <!-- Tab 5: การติดตามเที่ยวบิน -->
                    <div class="content-tab" data-tab="tab5">
                        <?php require_once('dashboard/tab5.php'); ?>
                    </div>

                    <!-- Tab 6: การจัดการคนขับ -->
                    <div class="content-tab" data-tab="tab6">
                        <?php require_once('dashboard/driver-management/driver-tab.php'); ?>
                    </div>

                    <!-- Tab 7: รายงานการเงิน -->
                    <div class="content-tab" data-tab="tab7">
                        <?php require_once('dashboard/tab7.php'); ?>
                    </div>

                    <!-- Tab 8: ตั้งค่าระบบ -->
                    <div class="content-tab" data-tab="tab8">
                        <?php require_once('dashboard/tab8.php'); ?>
                    </div>
                </div>

            </div>
    </div>
</div>
</div>




    <script>
        let dataTable;
        
        $(document).ready(function() {
            // Initialize DataTable
            dataTable = $('#parkingTable').DataTable({
                "language": {
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "ไม่มีข้อมูล",
                    "infoFiltered": "(กรองจาก _MAX_ รายการทั้งหมด)",
                    "search": "ค้นหา:",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "pageLength": 10,
                "lengthChange": false,
                "searching": true, // Enable default search but hide via CSS
                "info": false, // Disable default info
                "paging": true, // Enable paging functionality
                "pagingType": "simple_numbers",
                "dom": '<"top">rt<"bottom"><"clear">', // Remove 'p' to hide default pagination
                "columnDefs": [
                    { "orderable": false, "targets": [7] } // Disable sorting for status column
                ],
                "order": [[0, 'desc']], // Sort by booking number descending
                "initComplete": function() {
                    // Initialize custom pagination after DataTable is fully loaded
                    setTimeout(function() {
                        updateCustomPagination();
                    }, 100);
                },
                "drawCallback": function(settings) {
                    // Custom pagination and info
                    setTimeout(function() {
                        updateCustomPagination();
                    }, 50);
                }
            });
            
            // Custom search functionality - only if element exists
            if ($('#customSearch').length > 0) {
                $('#customSearch').on('keyup', function() {
                    if (dataTable) {
                        dataTable.search(this.value).draw();
                    }
                });
            }
            
            // Export functionality - only if element exists
            if ($('#exportSelect').length > 0) {
                $('#exportSelect').on('change', function() {
                    const format = this.value;
                    if (format === 'excel') {
                        exportToExcel();
                    } else if (format === 'csv') {
                        exportToCSV();
                    }
                    this.value = ''; // Reset select
                });
            }
            
            // Filter functionality - only if element exists
            if ($('#filterBtn').length > 0) {
                $('#filterBtn').on('click', function() {
                    // You can implement custom filter modal here
                    alert('Filter functionality can be implemented here');
                });
            }
        });
        
        function updateCustomPagination() {
            // Check if dataTable is initialized
            if (!dataTable || typeof dataTable.page !== 'function') {
                return;
            }
            
            try {
                const info = dataTable.page.info();
                const totalRecords = info.recordsTotal;
                const filteredRecords = info.recordsDisplay;
                const start = info.start + 1;
                const end = info.end;
                const currentPage = info.page + 1;
                const totalPages = info.pages;
                
                // Update info - only if element exists
                if ($('#tableInfo').length > 0) {
                    let infoText = `Total: ${totalRecords}`;
                    if (filteredRecords !== totalRecords) {
                        infoText += ` (filtered from ${totalRecords})`;
                    }
                    infoText += ` | Showing: ${start} - ${end} of ${filteredRecords}`;
                    
                    $('#tableInfo').text(infoText);
                }
                
                // Update pagination - only if element exists
                if ($('#tablePagination').length > 0) {
                    let paginationHtml = '';
                    
                    // Previous button
                    if (currentPage > 1) {
                        paginationHtml += `<button class="px-3 py-1 border rounded hover:bg-gray-100" onclick="changePage('previous')">‹</button>`;
                    } else {
                        paginationHtml += `<button class="px-3 py-1 border rounded bg-gray-100 text-gray-400 cursor-not-allowed">‹</button>`;
                    }
                    
                    // Page numbers
                    for (let i = 1; i <= totalPages; i++) {
                        if (i === currentPage) {
                            paginationHtml += `<button class="px-3 py-1 bg-blue-500 text-white rounded">${i}</button>`;
                        } else {
                            paginationHtml += `<button class="px-3 py-1 border rounded hover:bg-gray-100" onclick="changePage(${i-1})">${i}</button>`;
                        }
                    }
                    
                    // Next button
                    if (currentPage < totalPages) {
                        paginationHtml += `<button class="px-3 py-1 border rounded hover:bg-gray-100" onclick="changePage('next')">›</button>`;
                    } else {
                        paginationHtml += `<button class="px-3 py-1 border rounded bg-gray-100 text-gray-400 cursor-not-allowed">›</button>`;
                    }
                    
                    $('#tablePagination').html(paginationHtml);
                }
            } catch (error) {
                console.error('Error updating pagination:', error);
            }
        }
        
        function changePage(page) {
            if (!dataTable || typeof dataTable.page !== 'function') {
                return;
            }
            
            try {
                if (page === 'previous') {
                    dataTable.page('previous').draw('page');
                } else if (page === 'next') {
                    dataTable.page('next').draw('page');
                } else {
                    // page is a number (0-based index)
                    dataTable.page(page).draw('page');
                }
            } catch (error) {
                console.error('Error changing page:', error);
            }
        }

        function exportToExcel() {
            try {
                // Get all table data (including filtered results)
                const headers = [];
                const rows = [];
                
                // Get headers
                $('#parkingTable thead th').each(function() {
                    headers.push($(this).text().trim());
                });
                
                // Add headers to rows
                rows.push(headers);
                
                // Get data from DataTable (respects current filter/search)
                if (dataTable) {
                    dataTable.rows({search: 'applied'}).every(function() {
                        const rowData = [];
                        $(this.node()).find('td').each(function() {
                            rowData.push($(this).text().trim());
                        });
                        rows.push(rowData);
                    });
                } else {
                    // Fallback: get data directly from table
                    $('#parkingTable tbody tr:visible').each(function() {
                        const rowData = [];
                        $(this).find('td').each(function() {
                            rowData.push($(this).text().trim());
                        });
                        rows.push(rowData);
                    });
                }
                
                // Create workbook
                const ws = XLSX.utils.aoa_to_sheet(rows);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "การจองที่จอดรถ");
                
                // Download file
                XLSX.writeFile(wb, "parking_reservations.xlsx");
                
                console.log('Excel export completed successfully');
            } catch (error) {
                console.error('Error exporting to Excel:', error);
                alert('เกิดข้อผิดพลาดในการส่งออกไฟล์ Excel');
            }
        }

        function exportToCSV() {
            try {
                // Get all table data (including filtered results)
                const headers = [];
                const rows = [];
                
                // Get headers
                $('#parkingTable thead th').each(function() {
                    headers.push($(this).text().trim());
                });
                
                // Add headers to rows
                rows.push(headers);
                
                // Get data from DataTable (respects current filter/search)
                if (dataTable) {
                    dataTable.rows({search: 'applied'}).every(function() {
                        const rowData = [];
                        $(this.node()).find('td').each(function() {
                            rowData.push($(this).text().trim());
                        });
                        rows.push(rowData);
                    });
                } else {
                    // Fallback: get data directly from table
                    $('#parkingTable tbody tr:visible').each(function() {
                        const rowData = [];
                        $(this).find('td').each(function() {
                            rowData.push($(this).text().trim());
                        });
                        rows.push(rowData);
                    });
                }
                
                // Create workbook and export as CSV
                const ws = XLSX.utils.aoa_to_sheet(rows);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "การจองที่จอดรถ");
                
                // Download CSV file
                XLSX.writeFile(wb, "parking_reservations.csv", {bookType: 'csv'});
                
                console.log('CSV export completed successfully');
            } catch (error) {
                console.error('Error exporting to CSV:', error);
                alert('เกิดข้อผิดพลาดในการส่งออกไฟล์ CSV');
            }
        }
    </script>
    
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButtons = document.querySelectorAll('[id^="mobileMenuButton"]');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const closeSidebar = document.getElementById('closeSidebar');
            
            // Open mobile menu - handle all mobile menu buttons
            mobileMenuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    sidebarOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
                });
            });
            
            // Close mobile menu
            function closeMenu() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
            
            closeSidebar.addEventListener('click', closeMenu);
            sidebarOverlay.addEventListener('click', closeMenu);
            
            // Mobile menu item functionality is handled in tab system below
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMenu(); // Close mobile menu if screen becomes larger
                }
            });

            // Tab System Functionality
            const sidebarItems = document.querySelectorAll('.sidebar-item[data-nav]');
            const contentTabs = document.querySelectorAll('.content-tab[data-tab]');

            // Add click event listeners to sidebar navigation items
            sidebarItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetTab = this.getAttribute('data-nav');
                    
                    // Remove active class from all sidebar items
                    sidebarItems.forEach(navItem => {
                        navItem.classList.remove('active');
                    });
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Hide all content tabs
                    contentTabs.forEach(tab => {
                        tab.classList.remove('active');
                    });
                    
                    // Show the target tab
                    const targetTabElement = document.querySelector(`[data-tab="${targetTab}"]`);
                    if (targetTabElement) {
                        targetTabElement.classList.add('active');
                    }
                    
                    // Close mobile menu after tab selection
                    if (window.innerWidth <= 768) {
                        closeMenu();
                    }
                });
            });

            // Set initial active tab if none is set
            const activeTab = document.querySelector('.content-tab.active');
            const activeSidebarItem = document.querySelector('.sidebar-item.active');
            
            if (!activeTab && contentTabs.length > 0) {
                contentTabs[0].classList.add('active'); // Default to tab1 (dashboard)
            }
            
            if (!activeSidebarItem && sidebarItems.length > 0) {
                sidebarItems[0].classList.add('active'); // Default to first item (dashboard)
            }
        });
    </script>
<?php
get_footer();
?>