<?php
/*
Template Name: Login Page
*/

get_header(); ?>

<div id="box-login" class="box-login">
    
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
        <!-- Login Container -->
        <div class="glass-effect rounded-3xl p-10 w-full mx-auto max-w-md border border-gray-300 relative z-10 animate-slide-in">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <h1 class="gradient-text text-3xl font-bold mb-1"><?php _e('Welcome Back', 'textdomain'); ?></h1>
                <p class="text-gray-600 text-sm"><?php _e('Please sign in to your account', 'textdomain'); ?></p>
            </div>

            <div class="auth-body">
                <?php
                // Display login messages
                if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                    ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Sorry :(', 'Login failed. Please check your credentials.');
                        });
                    </script>
                    <?php
                }
                if (isset($_GET['login']) && $_GET['login'] == 'empty') {
                    ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('error', 'Sorry :(', 'Please enter your username and password.');
                        });
                    </script>
                    <?php
                }
                if (isset($_GET['login']) && $_GET['login'] == 'false') {
                    ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('success', 'Success!', 'You are now logged out.');
                        });
                    </script>
                    <?php
                }
                
                // Check if user is already logged in
                if (is_user_logged_in()) {
                    ?>
                    <div class="text-center">
                        <div class="mb-6 p-4 bg-green-100 border border-green-200 rounded-xl text-green-700">
                            <?php _e('You are already logged in!', 'textdomain'); ?>
                        </div>
                        <div class="flex gap-4">
                            <a href="<?php echo wp_logout_url(home_url()); ?>" class="flex-1 px-4 py-3 bg-transparent border-2 border-primary text-primary rounded-xl font-semibold transition-all duration-300 hover:bg-primary hover:text-white text-center flex items-center justify-center">
                                <?php _e('Logout', 'textdomain'); ?>
                            </a>
                            <a href="<?php echo admin_url(); ?>" class="flex-1 px-4 py-3 bg-primary text-white rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg text-center flex items-center justify-center">
                                <?php _e('Go to Dashboard', 'textdomain'); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                } else {
                    // Display login form
                    ?>
                    <form id="loginForm" method="post" action="">
                        <?php wp_nonce_field('user_login_nonce', 'login_nonce'); ?>

                        <!-- Username/Email -->
                        <div class="mb-6">
                            <label for="user_login" class="block mb-2 text-gray-700 font-medium text-sm"><?php _e('Username or Email', 'textdomain'); ?></label>
                            <div class="relative transition-transform duration-300 hover:-translate-y-0.5">
                                <input type="text" name="username" id="user_login" required autocomplete="on"
                                    class="w-full pr-12 pl-4 py-4 border-2 border-gray-200 rounded-xl text-base bg-white/80 transition-all duration-300 focus:border-primary focus:bg-white focus:shadow-lg focus:shadow-primary/10 focus:-translate-y-0.5 outline-none hover:-translate-y-0.5 hover:shadow-md">
                                <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 transition-colors duration-300 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div id="log_error" class="text-red-500 text-xs mt-1 opacity-0 transform -translate-y-2 transition-all duration-300"></div>
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-6">
                            <label for="user_pass" class="block mb-2 text-gray-700 font-medium text-sm"><?php _e('Password', 'textdomain'); ?></label>
                            <div class="relative transition-transform duration-300 hover:-translate-y-0.5">
                                <input type="password" name="password" id="user_pass" required autocomplete="on"
                                    class="w-full pr-12 pl-4 py-4 border-2 border-gray-200 rounded-xl text-base bg-white/80 transition-all duration-300 focus:border-primary focus:bg-white focus:shadow-lg focus:shadow-primary/10 focus:-translate-y-0.5 outline-none hover:-translate-y-0.5 hover:shadow-md">
                                <button type="button" id="toggle-password" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-300 focus:outline-none">
                                    <svg id="eye-open" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <svg id="eye-closed" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path>
                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div id="pwd_error" class="text-red-500 text-xs mt-1 opacity-0 transform -translate-y-2 transition-all duration-300"></div>
                        </div>
                        
                        <!-- Remember Me -->
                        <div class="flex items-start gap-3 mb-6">
                            <div class="relative mt-0.5">
                                <input type="checkbox" name="rememberme" id="rememberme" value="forever" class="opacity-0 absolute">
                                <span class="w-5 h-5 bg-white border-2 border-gray-200 rounded cursor-pointer transition-all duration-300 flex items-center justify-center hover:border-primary">
                                    <svg class="w-3 h-3 text-white opacity-0 transition-opacity duration-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            </div>
                            <label for="rememberme" class="text-gray-600 text-sm leading-relaxed cursor-pointer">
                                <?php _e('Remember Me', 'textdomain'); ?>
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" id="login-btn" class="w-full py-4 bg-primary text-white border-none rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 relative overflow-hidden btn-shimmer hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-primary/30 active:translate-y-0">
                            <span id="btn-text"><?php _e('Sign In', 'textdomain'); ?></span>
                            <div id="btn-loading" class="hidden flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 text-white mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing In...
                            </div>
                        </button>
                    </form>
                    
                    <!-- Links -->
                    <div class="text-center mt-6">
                        <a href="<?php echo get_home_url(); ?>/forgot-password" class="text-primary hover:underline font-medium text-sm">
                            <?php _e('Forgot your password?', 'textdomain'); ?>
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
                <?php } ?>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>


