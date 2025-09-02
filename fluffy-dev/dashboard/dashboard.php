<?php 
/**
 * Dashboard functionality
 */

// Include dashboard components
require_once get_template_directory() . '/dashboard/components/components.php';

// Add dashboard menu to admin
add_action('admin_menu', 'add_dashboard_menu');

function add_dashboard_menu() {
    add_menu_page(
        'Dashboard',
        'Dashboard',
        'manage_options',
        'custom-dashboard',
        'render_dashboard_page',
        'dashicons-dashboard',
        2
    );
}

function render_dashboard_page() {
    render_dashboard_page('Dashboard Overview', function() {
        render_dashboard_header('Dashboard Overview', get_dashboard_icon('user-group'), 'System management dashboard');
        
        // Sample stats
        $stats = [
            ['label' => 'Total Bookings', 'value' => '1,234', 'icon' => get_dashboard_icon('car')],
            ['label' => 'Active Drivers', 'value' => '56', 'icon' => get_dashboard_icon('user-group')],
            ['label' => 'Available Spaces', 'value' => '89', 'icon' => get_dashboard_icon('parking')],
            ['label' => 'Revenue Today', 'value' => '฿12,500', 'icon' => get_dashboard_icon('transport')]
        ];
        
        render_dashboard_stats($stats);
        
        echo '<div class="bg-white rounded-lg shadow p-6">';
        echo '<h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>';
        echo '<p class="text-gray-600">Dashboard content and recent activity will be displayed here.</p>';
        echo '</div>';
    });
}

function enqueue_dashboard_js() {
    wp_enqueue_script('parking-perday', get_template_directory_uri() . '/dashboard/js/parking-perday.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('parking-permonth', get_template_directory_uri() . '/dashboard/js/parking-permonth.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('parking-export', get_template_directory_uri() . '/dashboard/js/parking-export.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_js');
