<?php
/**
 * Dashboard Components Loader
 * Loads all dashboard components and utilities
 */

// Load all component files
require_once __DIR__ . '/dashboard-header.php';
require_once __DIR__ . '/dashboard-table.php';
require_once __DIR__ . '/dashboard-modal.php';

// Enqueue dashboard utilities JavaScript
function enqueue_dashboard_components() {
    wp_enqueue_script(
        'dashboard-utils',
        get_template_directory_uri() . '/dashboard/components/dashboard-utils.js',
        ['jquery'],
        '1.0.0',
        true
    );
    
    // Add common dashboard styles
    wp_add_inline_style('dashboard-utils', '
        .dashboard-fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dashboard-loading {
            position: relative;
        }
        
        .dashboard-loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .responsive-table {
            overflow-x: auto;
        }
        
        @media (max-width: 768px) {
            .responsive-table table,
            .responsive-table thead,
            .responsive-table tbody,
            .responsive-table th,
            .responsive-table td,
            .responsive-table tr {
                display: block;
            }
            
            .responsive-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            
            .responsive-table tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                padding: 10px;
                border-radius: 8px;
                background: white;
            }
            
            .responsive-table td {
                border: none;
                position: relative;
                padding-left: 50% !important;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            
            .responsive-table td:before {
                content: attr(data-label) ": ";
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                color: #666;
            }
        }
    ');
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_components');

/**
 * Dashboard page wrapper
 */
function render_dashboard_page($title, $content_callback, $scripts = []) {
    ?>
    <div class="dashboard-page min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dashboard-fade-in">
                <?php
                if (is_callable($content_callback)) {
                    call_user_func($content_callback);
                } else {
                    echo $content_callback;
                }
                ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($scripts)): ?>
        <script>
            <?php foreach ($scripts as $script): ?>
                <?php echo $script; ?>
            <?php endforeach; ?>
        </script>
    <?php endif; ?>
    <?php
}

/**
 * Common dashboard data formatters
 */
class DashboardFormatters {
    public static function formatDate($date, $format = 'd/m/Y') {
        if (empty($date)) return '-';
        return date($format, strtotime($date));
    }
    
    public static function formatCurrency($amount, $currency = 'THB') {
        if (empty($amount)) return '-';
        return number_format($amount, 2) . ' ' . $currency;
    }
    
    public static function formatStatus($status, $statuses = []) {
        if (empty($statuses)) {
            $statuses = [
                'active' => ['label' => 'Active', 'class' => 'bg-green-100 text-green-800'],
                'inactive' => ['label' => 'Inactive', 'class' => 'bg-red-100 text-red-800'],
                'pending' => ['label' => 'Pending', 'class' => 'bg-yellow-100 text-yellow-800']
            ];
        }
        
        $config = $statuses[$status] ?? ['label' => $status, 'class' => 'bg-gray-100 text-gray-800'];
        
        return sprintf(
            '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full %s">%s</span>',
            $config['class'],
            $config['label']
        );
    }
    
    public static function formatBoolean($value, $true_text = 'Yes', $false_text = 'No') {
        return $value ? $true_text : $false_text;
    }
}
?>
