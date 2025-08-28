<?php
/**
 * Template Name: Service Control
 *
 * This template provides Check-in and Check-out functionality for customers
 * with the 'service-customer' role.
 */

get_header(); // Include theme header
?>

<div class="py-[5em] px-[2em] sm:py-[2em] sm:px-[1.5em] md:py-[3em] md:px-[2em] lg:py-[5em] lg:px-[2em] bg-base-200 flex items-center justify-center p-4 service-control-box">
    <div class="container mx-auto service-control-box">

        <?php if (is_user_logged_in()) : ?>
            <?php
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;

            // Check if the user has the 'service-customer' role
            if (in_array('service-customer', $user_roles, true) || in_array('administrator', $user_roles, true)) :
            ?>
                <div class="card bg-base-100 shadow-xl max-w-lg mx-auto service-control-box">
                    <div class="card-body items-center text-center service-control-box">
                        <h1 class="card-title text-2xl service-control-box">Service Control</h1>
                        <p class="service-control-box">Please select an action for your service.</p>
                        <div class="card-actions justify-center w-full grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 service-control-box">
                            <a href="<?php echo esc_url(get_home_url(). '/check-in'); ?>" class="btn btn-primary btn-lg w-full service-control-box">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 service-control-box" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" class="service-control-box" /></svg>
                                Check-in
                            </a>
                            <a href="<?php echo esc_url(get_home_url(). '/check-out'); ?>" class="btn btn-secondary btn-lg w-full service-control-box">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 service-control-box" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" class="service-control-box" /></svg>
                                Check-out
                            </a>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div role="alert" class="alert alert-warning max-w-lg mx-auto service-control-box">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 service-control-box" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" class="service-control-box" /></svg>
                    <span class="service-control-box"><strong class="service-control-box">Access Denied:</strong> You do not have the required permissions to view this page.</span>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div role="alert" class="alert alert-error max-w-lg mx-auto service-control-box">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 service-control-box" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" class="service-control-box" /></svg>
                <span class="service-control-box"><strong class="service-control-box">Please Log In:</strong> You must be logged in to access this page. <a href="<?php echo wp_login_url(get_permalink()); ?>" class="link service-control-box">Click here to log in.</a></span>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
get_footer(); // Include theme footer

