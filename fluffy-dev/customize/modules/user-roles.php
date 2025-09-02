<?php
/**
 * User Roles and Permissions Module
 * Handles custom user roles and capabilities
 */

// Create custom role 'service-customer'
add_action('init', function() {
    add_role('service-customer', 'Service Customer', array(
        'read'         => true,
        'edit_posts'   => true,
        'delete_posts' => true,
        'upload_files' => true,
    ));
});

/**
 * Check if user has specific capability
 */
function user_can_access_service($capability = 'read') {
    if (!is_user_logged_in()) {
        return false;
    }
    
    return current_user_can($capability);
}

/**
 * Get user role display name
 */
function get_user_role_display_name($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return '';
    }
    
    $roles = array(
        'administrator' => 'ผู้ดูแลระบบ',
        'service-customer' => 'ลูกค้าบริการ',
        'subscriber' => 'สมาชิก',
        'editor' => 'บรรณาธิการ',
        'author' => 'ผู้เขียน',
        'contributor' => 'ผู้ร่วมเขียน'
    );
    
    $user_roles = $user->roles;
    if (empty($user_roles)) {
        return 'ไม่ระบุ';
    }
    
    $role = $user_roles[0];
    return isset($roles[$role]) ? $roles[$role] : ucfirst($role);
}

/**
 * Redirect users based on role after login
 */
function redirect_user_after_login($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('service-customer', $user->roles)) {
            return home_url('/dashboard/');
        }
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_user_after_login', 10, 3);
?>
