<?php
/*
Template Name: Forgot Password Page
*/

get_header(); ?>

<div id="box-forgot-password" class="box-forgot-password" style="
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    background-image: url('<?php echo get_template_directory_uri(); ?>/customize/img/bg-x.webp');">
    
    <!-- Notification Modal -->
    <div id="notification-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div id="notification-content" class="bg-white rounded-3xl shadow-2xl p-10 w-full mx-auto max-w-md border border-white/20 relative z-10 animate-slide-in text-center">
            <div id="notification-icon" class="mb-3 flex justify-center">
                <!-- Success Icon -->
                <div id="success-icon" class="hidden w-20 h-20 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <!-- Error Icon -->
                <div id="error-icon" class="hidden w-20 h-20 bg-red-500 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <h2 id="notification-title" class="text-2xl font-bold mb-4 text-blue-600"></h2>
            <p id="notification-message" class="text-gray-600 mb-3"></p>
            <button id="notification-btn" class="px-8 py-3 rounded-xl text-white font-semibold transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg bg-primary hover:bg-blue-600">
                OKAY
            </button>
        </div>
    </div>

    <div class="xl:container mx-auto py-[2em] sm:py-[2em] md:py-[5em] lg:py-[5em] px-[1.5em] sm:py-[2em] md:py-[0em] lg:py-[0em]">
        <!-- Forgot Password Container -->
        <div class="glass-effect rounded-3xl shadow-2xl p-10 w-full mx-auto max-w-md border border-white/20 relative z-10 animate-slide-in">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <h1 class="gradient-text text-3xl font-bold mb-1"><?php _e('Forgot Password?', 'textdomain'); ?></h1>
                <p class="text-gray-600 text-sm"><?php _e('Enter your email to reset your password', 'textdomain'); ?></p>
            </div>

            <div class="auth-body">
                <!-- Display forgot password form -->
                <form id="forgotPasswordForm" method="post" action="">
                    <?php wp_nonce_field('forgot_password_nonce', 'forgot_nonce'); ?>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="user_email" class="block mb-2 text-gray-700 font-medium text-sm"><?php _e('Email Address', 'textdomain'); ?></label>
                        <div class="relative transition-transform duration-300 hover:-translate-y-0.5">
                            <input type="email" name="user_email" id="user_email" required autocomplete="off"
                                class="w-full pr-12 pl-4 py-4 border-2 border-gray-200 rounded-xl text-base bg-white/80 transition-all duration-300 focus:border-primary focus:bg-white focus:shadow-lg focus:shadow-primary/10 focus:-translate-y-0.5 outline-none hover:-translate-y-0.5 hover:shadow-md">
                            <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 transition-colors duration-300 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                        </div>
                        <div id="user_email_error" class="text-red-500 text-xs mt-1 opacity-0 transform -translate-y-2 transition-all duration-300"></div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" id="forgot-btn" class="w-full py-4 bg-primary text-white border-none rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 relative overflow-hidden btn-shimmer hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-primary/30 active:translate-y-0">
                        <span id="btn-text"><?php _e('Send Reset Link', 'textdomain'); ?></span>
                        <div id="btn-loading" class="hidden flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-white mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </div>
                    </button>
                </form>
                
                <!-- Links -->
                <div class="text-center mt-6">
                    <a href="<?php echo get_home_url(); ?>/login" class="text-primary hover:underline font-medium text-sm">
                        <?php _e('Back to Login', 'textdomain'); ?>
                    </a>
                    <?php if (get_option('users_can_register')) : ?>
                        <div class="text-center mt-4 text-gray-600 text-sm">
                            <?php _e("Don't have an account?", 'textdomain'); ?> 
                            <a href="<?php echo get_home_url(); ?>/register" class="text-primary hover:underline font-medium">
                                <?php _e('Sign up', 'textdomain'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/customize/css/sign-up.css">
<script src="<?php echo get_template_directory_uri(); ?>/customize/js/ajax-resetpassword.js"></script>

<script>
// Localize script for AJAX
if (typeof ajax_object === 'undefined') {
    window.ajax_object = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>'
    };
}
</script>

<style>
/* Forgot Password Page Specific Overrides */
/* :root {
    --primary: #3b82f6;
}

.bg-primary {
    background-color: var(--primary);
}

.text-primary {
    color: var(--primary);
}

.border-primary {
    border-color: var(--primary);
}

.focus\:border-primary:focus {
    border-color: var(--primary);
}

.hover\:border-primary:hover {
    border-color: var(--primary);
} */

.focus\:shadow-primary\/10:focus {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.hover\:shadow-primary\/30:hover {
    box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.3);
}
</style>

<?php get_footer(); ?>
