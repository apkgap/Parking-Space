<?php
/**
 * Dashboard Header Component
 * Reusable header component for dashboard tabs
 */

function render_dashboard_header($title, $icon_svg, $description = '') {
    ?>
    <div class="head-bar flex items-center px-4 py-3 text-gray-700 border-b border-gray-200 mb-10">
        <h3 class="flex items-center text-2xl">
            <?php echo $icon_svg; ?>
            <?php echo esc_html($title); ?>
        </h3>
        <?php if ($description): ?>
            <p class="ml-4 text-gray-600"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

// Icon library for common dashboard icons
function get_dashboard_icon($icon_name) {
    $icons = [
        'user-group' => '<svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
        </svg>',
        
        'parking' => '<svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 5a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
        </svg>',
        
        'car' => '<svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
        </svg>',
        
        'transport' => '<svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
        </svg>'
    ];
    
    return isset($icons[$icon_name]) ? $icons[$icon_name] : '';
}
?>
