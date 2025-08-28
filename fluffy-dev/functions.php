<?php
/**
 * fluffy functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package fluffy
 */
require_once get_template_directory() . '/customize/customize.php';
require_once get_template_directory() . '/control/control.php';
require_once get_template_directory() . '/dashboard/dashboard.php';

function enqueue_wpt_assets() {
    $css_file = get_template_directory() . '/booking/wpt-styles.css';
    $js_file = get_template_directory() . '/booking/wpt-script.js';

    $css_version = filemtime($css_file);
    $js_version = filemtime($js_file);

	    // Enqueue Flatpickr from local files
		wp_enqueue_style('flatpickr-css', get_template_directory_uri() . '/booking/flatpickr.min.css', array(), filemtime($theme_dir . '/booking/flatpickr.min.css'));
		wp_enqueue_script('flatpickr-js', get_template_directory_uri() . '/booking/flatpickr.min.js', array(), filemtime($theme_dir . '/booking/flatpickr.min.js'), true);
		wp_enqueue_script('flatpickr-th', get_template_directory_uri() . '/booking/th.js', array('flatpickr-js'), filemtime($theme_dir . '/booking/l10n/th.js'), true);
		

    wp_enqueue_style('wpt-styles', get_template_directory_uri() . '/booking/wpt-styles.css', array(), $css_version);
    wp_enqueue_script('wpt-script', get_template_directory_uri() . '/booking/wpt-script.js', array('jquery'), $js_version, true);

	
}
add_action('wp_enqueue_scripts', 'enqueue_wpt_assets');


function wpt_enqueue_swiper_scripts() {
    // Get file modification times for versioning
    $css_file = get_template_directory() . '/css/wpt-carousel.css';
    $js_file = get_template_directory() . '/js/wpt-carousel.js';
    $css_ver = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
    $js_ver = file_exists($js_file) ? filemtime($js_file) : '1.0.0';
    
    // Enqueue Swiper.js CSS
    wp_enqueue_style('swiper-css', get_template_directory_uri() . '/css/swiper-bundle.min.css', array());

    // Enqueue Swiper.js JS
    wp_enqueue_script('swiper-js', get_template_directory_uri() . '/js/swiper-bundle.min.js', array(), '', true);

    // Enqueue custom CSS for the carousel with file modification time as version
    wp_enqueue_style('wpt-carousel-style', get_template_directory_uri() . '/css/wpt-carousel.css', array(), $css_ver);

	if(is_front_page()){
		wp_enqueue_script('wpt-carousel-script', get_template_directory_uri() . '/js/wpt-carousel.js', array('swiper-js'), $js_ver, true);
	}
}
add_action('wp_enqueue_scripts', 'wpt_enqueue_swiper_scripts');



function wpt_promotion_carousel_shortcode($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(array(
        'posts_per_page' => 9, // Number of posts to display
		'layout' => '', // Optional category slug
        'category' => '', // Optional category slug
    ), $atts, 'wpt_post_carousel');

    // Query arguments
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $atts['posts_per_page'],
        'post_status' => 'publish',
    );

    if (!empty($atts['category'])) {
        $args['category_name'] = $atts['category'];
    }

    $query = new WP_Query($args);

    // Start output buffering
    ob_start();

	$layout = '';
    if ($query->have_posts()) :
	
		$layout = $atts['layout']
	?>
        <div class="wpt-post-carousel swiper<?php echo $layout; ?>">
            <div class="swiper-wrapper<?php echo $layout; ?>">
                <?php while ($query->have_posts()) : $query->the_post(); 
					$wpt_promotion_discount = get_field('wpt_promotion_discount');
				?>
                    <div class="wpt-swiper-slide swiper-slide<?php echo $layout; ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title(); ?>" class="wpt-slide-image">
                        <?php endif; ?>
						<?php if ($wpt_promotion_discount): ?>
							<div class="wpt-discount-badge">
								<span>ส่วนลด</span>
								<span class="r2"><?php echo $wpt_promotion_discount; ?>%</span>
							</div>
						<?php endif; ?>
                        
                        <div class="wpt-slide-content">
                            <a href="<?php the_permalink(); ?>">
								<h3 class="wpt-slide-title"><?php the_title(); ?></h3>
							</a>
                            <p class="wpt-slide-description"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                            <a href="<?php the_permalink(); ?>" class="wpt-slide-button">จองเลย!</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- Add Navigation -->
			 <?php if ($layout != 'grid'): ?>
				<div class="swiper-button-next"></div>
				<div class="swiper-button-prev"></div>
			 <?php endif; ?>
            
        </div>
    <?php endif;

    // Reset post data
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('wpt_promotion_carousel', 'wpt_promotion_carousel_shortcode');


function is_elementor() {
    // Check if Elementor is active.
	if (class_exists('\Elementor\Plugin')) {
        global $post;
        
        // Ensure $post and $post->ID are available.
        if (!empty($post) && !empty($post->ID)) {
            // Check if the post is built with Elementor.
            $document = \Elementor\Plugin::$instance->documents->get($post->ID);
            if ($document) {
                return $document->is_built_with_elementor();
            }
        }
    }
    
    return false; // Default return value if conditions aren't met.
}


function rocket_lazyload_exclude_class( $attributes ) {
	$attributes[] = 'class="custom-logo"';
	return $attributes;
}
add_filter( 'rocket_lazyload_excluded_attributes', 'rocket_lazyload_exclude_class' );


add_action('wp_head', function() { echo '<script type="text/javascript"> if (typeof(wp) == "undefined") { window.wp = { i18n: { setLocaleData: (function() { return false; })} }; } </script>'; });

function vecular_prefix_menu_arrow($item_output, $item, $depth, $args) {
		if (in_array('menu-item-has-children', $item->classes)) {
				$arrow = '<div class="wrap-toggle-mobile"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></div>'; // Change the class to your font icon
				$item_output = str_replace('</a>', '</a>'. $arrow .'', $item_output);
		}
		return $item_output;
}
add_filter('walker_nav_menu_start_el', 'vecular_prefix_menu_arrow', 10, 4);


if ( ! function_exists( 'fluffy_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function fluffy_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on fluffy, use a find and replace
		 * to change 'fluffy' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'fluffy', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );


		require( get_template_directory() . '/updater/theme-updater.php' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary' => esc_html__( 'Primary', 'fluffy' ),
				'mobile' => esc_html__( 'Mobile Menu', 'fluffy' ),
				'topbar' => esc_html__( 'Top Bar Menu', 'fluffy' ),
			)
		);
	
		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'fluffy_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

	}
endif;



add_action( 'after_setup_theme', 'fluffy_setup' );





/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function fluffy_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'fluffy_content_width', 640 );
}
add_action( 'after_setup_theme', 'fluffy_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function fluffy_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Widget 1', 'fluffy' ),
			'id'            => 'footer-widget-1',
			'description'   => esc_html__( 'Add widgets here.', 'fluffy' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Widget 2', 'fluffy' ),
			'id'            => 'footer-widget-2',
			'description'   => esc_html__( 'Add widgets here.', 'fluffy' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Widget 3', 'fluffy' ),
			'id'            => 'footer-widget-3',
			'description'   => esc_html__( 'Add widgets here.', 'fluffy' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Widget 4', 'fluffy' ),
			'id'            => 'footer-widget-4',
			'description'   => esc_html__( 'Add widgets here.', 'fluffy' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'fluffy' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'fluffy' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'fluffy_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function fluffy_scripts() {
	wp_enqueue_style( 'fluffy-style', get_stylesheet_uri(), array(), '1.0' );
	wp_style_add_data( 'fluffy-style', 'rtl', 'replace' );
	wp_enqueue_style('fluffy-animate', get_theme_file_uri('/css/animate.min.css'), array(), filemtime(get_theme_file_path('/css/animate.min.css')));
	wp_enqueue_style('fluffy-main', get_theme_file_uri('/css/main.css'), array(), filemtime(get_theme_file_path('/css/main.css')));
	wp_enqueue_style('fluffy-fonts', get_theme_file_uri('/fonts/noto-sans-thai/font.css'), array(), filemtime(get_theme_file_path('/fonts/noto-sans-thai/font.css')));
	wp_enqueue_script( 'vecular-script', get_template_directory_uri() . '/js/vecular.js', array('jquery'), filemtime(get_theme_file_path('/js/vecular.js')), true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'fluffy_scripts' );


function get_excerpt($limit, $source = null){

    $excerpt = $source == "content" ? get_the_content() : get_the_excerpt();
    $excerpt = preg_replace(" (\[.*?\])",'',$excerpt);
    $excerpt = strip_shortcodes($excerpt);
    $excerpt = strip_tags($excerpt);
    $excerpt = substr($excerpt, 0, $limit);
    $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
    $excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
    // $excerpt = $excerpt.'... <a href="'.get_permalink($post->ID).'">'.esc_html__( 'Read More', 'fluffy' ).'</a>';
		  $excerpt = $excerpt.'...';
    return $excerpt;
}

function yp_popup_search(){
  // if (isset($_GET['_sf_s'])) {
  //   $s = $_GET['_sf_s'];
  // }
  // else {
  //   $s = $_GET['s'];
  // }
  ?>
	<div class="wrap-all-search">
		<div class="vc_search-toggle">
			<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
		</div>
		<div class="wrap-popup_search">
			<div class="cancel-btn_search">
					<span class="btn-close">
					<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</span>
			</div>
			<div class="popup_search">
				<div class="box-search">
						<div class="sec-search_box">
														<form action="<?php echo home_url(); ?>" id="search-h_box" class="search-h_box" method="GET">
																<div class="main-object_s">
																		<div class="s-object object-1">
																			<label for="s-text">
																				<input type="text" name="s" id="s-text" class="input-s_box input-s_text" value="<?php echo $s; ?>" placeholder="<?php yp_text( 'ค้นหา...', 'Search...' ); ?>">
																				</label>
																		</div>
																		<div class="s-object object-2">
																				<button type="submit" class="btn-search_h btn-sh"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
																		</div>
																</div>
														</form>
												</div>
				</div>
			</div>
		</div>
		<div class="vc-search-overlay"></div>
	</div>
  <?php
}

function polylang_slink() {
	if ( ! function_exists( 'pll_the_languages' ) ) return;

	  // Gets the pll_the_languages() raw code
	  $languages = pll_the_languages( array(
	    'display_names_as'       => 'name',
	    'hide_if_no_translation' => 0,
			'show_flags' => 1,
	    'raw'                    => true
	  ) );
		// print_r($languages);

	  // $output = '';

	  // Checks if the $languages is not empty
	  if ( ! empty( $languages ) ) {

	    // Creates the $output variable with languages container
	  ?>
    <div class="polylang_slink">

    <?php

	    // Runs the loop through all languages

       foreach ( $languages as $language ) {
         $id             = $language['id'];
         $name           = $language['name'];
         $url            = $language['url'];
         $current        = $language['current_lang'] ? ' languages__item--current' : '';
         $no_translation = $language['no_translation'];
         ?>
         <?php if ($current): ?>

             <?php
              foreach ( $languages as $language ) {

                   // Variables containing language data
                   $id             = $language['id'];
                   $name           = $language['name'];
                   $url            = $language['url'];
                   $current        = $language['current_lang'] ? ' languages__item--current' : '';
                   $no_translation = $language['no_translation'];
                       ?>
                       <a href="<?php echo $url; ?>" class="languages__item <?php echo $current; ?>">
												 <div class="l-text">
													 <?php echo $name; ?>
												 </div>
                       </a>
                       <?php
                 }
                ?>


         <?php endif; ?>

         <?php } ?>
      </div>

<?php
}
	  // return $output;
}

add_shortcode( 'polylang_slink', 'polylang_slink' );



if( function_exists('acf_add_options_page') ) {

  acf_add_options_page(array(
    'page_title' 	=> 'Site Settings',
    'menu_title'	=> 'Site Settings',
    'menu_slug' 	=> 'vc-site-settings',
    'capability'	=> 'edit_posts',
    'redirect'		=> false
  ));

  acf_add_options_sub_page(array(
    'page_title' 	=> 'Footer Settings',
    'menu_title'	=> 'Footer Settings',
    'parent_slug'	=> 'vc-site-settings',
  ));

  acf_add_options_sub_page(array(
    'page_title' 	=> 'Social Media Settings',
    'menu_title'	=> 'Social Media Settings',
    'parent_slug'	=> 'vc-site-settings',
  ));


}


function yp_text($text_th,$text_eng){
  if (get_locale() == 'th') {
    echo $text_th;
  }
  if (get_locale() != 'th') {
    echo $text_eng;
  }

}

function yp_text_get($text_th,$text_eng){
  if (get_locale() == 'th') {
      $text_th;
  }
  if (get_locale() != 'th') {
     $text_eng;
  }
}


function social_list() {
		ob_start();
?>
<?php
$facebook = get_field('facebook_link', 'option');
$twitter = get_field('twitter_link', 'option');
$youtube = get_field('youtube_link', 'option');
$line = get_field('line_link', 'option');
$map = get_field('map_link', 'option');
$instagram = get_field('instagram_link', 'option');
?>
<div class="social-list">
	<ul>
		<?php if ($facebook): ?>
			<li class="item-facebook">
				<a target="_blank" href="<?php echo $facebook; ?>">
					<span>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="64px" height="64px"><path d="M 19.253906 2 C 15.311906 2 13 4.0821719 13 8.8261719 L 13 13 L 8 13 L 8 18 L 13 18 L 13 30 L 18 30 L 18 18 L 22 18 L 23 13 L 18 13 L 18 9.671875 C 18 7.884875 18.582766 7 20.259766 7 L 23 7 L 23 2.2050781 C 22.526 2.1410781 21.144906 2 19.253906 2 z"/></svg>
					</span>
				</a>
			</li>
		<?php endif; ?>

		<?php if ($twitter): ?>
			<li class="item-twitter">
				<a target="_blank" href="<?php echo $twitter; ?>">
					<span>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="96px" height="96px">    <path d="M22,3.999c-0.78,0.463-2.345,1.094-3.265,1.276c-0.027,0.007-0.049,0.016-0.075,0.023c-0.813-0.802-1.927-1.299-3.16-1.299 c-2.485,0-4.5,2.015-4.5,4.5c0,0.131-0.011,0.372,0,0.5c-3.353,0-5.905-1.756-7.735-4c-0.199,0.5-0.286,1.29-0.286,2.032 c0,1.401,1.095,2.777,2.8,3.63c-0.314,0.081-0.66,0.139-1.02,0.139c-0.581,0-1.196-0.153-1.759-0.617c0,0.017,0,0.033,0,0.051 c0,1.958,2.078,3.291,3.926,3.662c-0.375,0.221-1.131,0.243-1.5,0.243c-0.26,0-1.18-0.119-1.426-0.165 c0.514,1.605,2.368,2.507,4.135,2.539c-1.382,1.084-2.341,1.486-5.171,1.486H2C3.788,19.145,6.065,20,8.347,20 C15.777,20,20,14.337,20,8.999c0-0.086-0.002-0.266-0.005-0.447C19.995,8.534,20,8.517,20,8.499c0-0.027-0.008-0.053-0.008-0.08 c-0.003-0.136-0.006-0.263-0.009-0.329c0.79-0.57,1.475-1.281,2.017-2.091c-0.725,0.322-1.503,0.538-2.32,0.636 C20.514,6.135,21.699,4.943,22,3.999z"/></svg>
					</span>
				</a>
			</li>
		<?php endif; ?>

		<?php if ($line): ?>
			<li class="item-line">
				<a target="_blank" href="<?php echo $line; ?>">
					<span>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="100px" height="100px">    <path d="M 9 4 C 6.24 4 4 6.24 4 9 L 4 41 C 4 43.76 6.24 46 9 46 L 41 46 C 43.76 46 46 43.76 46 41 L 46 9 C 46 6.24 43.76 4 41 4 L 9 4 z M 25 11 C 33.27 11 40 16.359219 40 22.949219 C 40 25.579219 38.959297 27.960781 36.779297 30.300781 C 35.209297 32.080781 32.660547 34.040156 30.310547 35.660156 C 27.960547 37.260156 25.8 38.519609 25 38.849609 C 24.68 38.979609 24.44 39.039062 24.25 39.039062 C 23.59 39.039062 23.649219 38.340781 23.699219 38.050781 C 23.739219 37.830781 23.919922 36.789063 23.919922 36.789062 C 23.969922 36.419063 24.019141 35.830937 23.869141 35.460938 C 23.699141 35.050938 23.029062 34.840234 22.539062 34.740234 C 15.339063 33.800234 10 28.849219 10 22.949219 C 10 16.359219 16.73 11 25 11 z M 23.992188 18.998047 C 23.488379 19.007393 23 19.391875 23 20 L 23 26 C 23 26.552 23.448 27 24 27 C 24.552 27 25 26.552 25 26 L 25 23.121094 L 27.185547 26.580078 C 27.751547 27.372078 29 26.973 29 26 L 29 20 C 29 19.448 28.552 19 28 19 C 27.448 19 27 19.448 27 20 L 27 23 L 24.814453 19.419922 C 24.602203 19.122922 24.294473 18.992439 23.992188 18.998047 z M 15 19 C 14.448 19 14 19.448 14 20 L 14 26 C 14 26.552 14.448 27 15 27 L 18 27 C 18.552 27 19 26.552 19 26 C 19 25.448 18.552 25 18 25 L 16 25 L 16 20 C 16 19.448 15.552 19 15 19 z M 21 19 C 20.448 19 20 19.448 20 20 L 20 26 C 20 26.552 20.448 27 21 27 C 21.552 27 22 26.552 22 26 L 22 20 C 22 19.448 21.552 19 21 19 z M 31 19 C 30.448 19 30 19.448 30 20 L 30 26 C 30 26.552 30.448 27 31 27 L 34 27 C 34.552 27 35 26.552 35 26 C 35 25.448 34.552 25 34 25 L 32 25 L 32 24 L 34 24 C 34.553 24 35 23.552 35 23 C 35 22.448 34.553 22 34 22 L 32 22 L 32 21 L 34 21 C 34.552 21 35 20.552 35 20 C 35 19.448 34.552 19 34 19 L 31 19 z"/></svg>
					</span>
				</a>
			</li>
		<?php endif; ?>

		<?php if ($instagram): ?>
			<li class="item-instagram">
				<a target="_blank" href="<?php echo $instagram; ?>">
					<span>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="100px" height="100px"><path d="M 16 3 C 8.8324839 3 3 8.8324839 3 16 L 3 34 C 3 41.167516 8.8324839 47 16 47 L 34 47 C 41.167516 47 47 41.167516 47 34 L 47 16 C 47 8.8324839 41.167516 3 34 3 L 16 3 z M 16 5 L 34 5 C 40.086484 5 45 9.9135161 45 16 L 45 34 C 45 40.086484 40.086484 45 34 45 L 16 45 C 9.9135161 45 5 40.086484 5 34 L 5 16 C 5 9.9135161 9.9135161 5 16 5 z M 37 11 A 2 2 0 0 0 35 13 A 2 2 0 0 0 37 15 A 2 2 0 0 0 39 13 A 2 2 0 0 0 37 11 z M 25 14 C 18.936712 14 14 18.936712 14 25 C 14 31.063288 18.936712 36 25 36 C 31.063288 36 36 31.063288 36 25 C 36 18.936712 31.063288 14 25 14 z M 25 16 C 29.982407 16 34 20.017593 34 25 C 34 29.982407 29.982407 34 25 34 C 20.017593 34 16 29.982407 16 25 C 16 20.017593 20.017593 16 25 16 z"/></svg>
					</span>
				</a>
			</li>
		<?php endif; ?>




		<?php if ($youtube): ?>
			<li class="item-youtube">
				<a target="_blank" href="<?php echo $youtube; ?>">
				<span>
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 310 310" style="enable-background:new 0 0 310 310;" xml:space="preserve">
					<g id="XMLID_822_">
						<path id="XMLID_823_" d="M297.917,64.645c-11.19-13.302-31.85-18.728-71.306-18.728H83.386c-40.359,0-61.369,5.776-72.517,19.938   C0,79.663,0,100.008,0,128.166v53.669c0,54.551,12.896,82.248,83.386,82.248h143.226c34.216,0,53.176-4.788,65.442-16.527   C304.633,235.518,310,215.863,310,181.835v-53.669C310,98.471,309.159,78.006,297.917,64.645z M199.021,162.41l-65.038,33.991   c-1.454,0.76-3.044,1.137-4.632,1.137c-1.798,0-3.592-0.484-5.181-1.446c-2.992-1.813-4.819-5.056-4.819-8.554v-67.764   c0-3.492,1.822-6.732,4.808-8.546c2.987-1.814,6.702-1.938,9.801-0.328l65.038,33.772c3.309,1.718,5.387,5.134,5.392,8.861   C204.394,157.263,202.325,160.684,199.021,162.41z"/>
					</g>
					</svg>
				</span>
				</a>
			</li>
		<?php endif; ?>

		<?php if ($map): ?>
			<li class="item-map">
				<a target="_blank" href="<?php echo $map; ?>">
					<span>
						<svg xmlns="http://www.w3.org/2000/svg" fill="#000000" viewBox="0 0 50 50" width="50px" height="50px"><path d="M 36 2 C 35.570313 2 35.136719 2.015625 34.71875 2.0625 C 34.605469 2.074219 34.488281 2.109375 34.375 2.125 C 34.070313 2.164063 33.765625 2.21875 33.46875 2.28125 C 33.355469 2.304688 33.269531 2.316406 33.15625 2.34375 C 32.863281 2.414063 32.566406 2.5 32.28125 2.59375 C 32.1875 2.625 32.09375 2.652344 32 2.6875 C 31.699219 2.796875 31.382813 2.929688 31.09375 3.0625 C 31.023438 3.09375 30.976563 3.125 30.90625 3.15625 C 30.597656 3.304688 30.292969 3.453125 30 3.625 C 29.953125 3.652344 29.890625 3.691406 29.84375 3.71875 C 29.53125 3.90625 29.230469 4.097656 28.9375 4.3125 C 28.914063 4.332031 28.898438 4.355469 28.875 4.375 C 28.5625 4.609375 28.257813 4.863281 27.96875 5.125 C 27.960938 5.132813 27.945313 5.117188 27.9375 5.125 C 27.636719 5.398438 27.363281 5.699219 27.09375 6 C 25.1875 8.125 24 10.925781 24 14 C 24 18.148438 26.136719 20.992188 28.40625 24 C 29.230469 25.09375 30.109375 26.246094 30.9375 27.53125 C 32.722656 29.773438 34.371094 32.410156 35.28125 35.90625 C 35.421875 36.449219 35.417969 36.875 35.65625 37.0625 C 35.765625 37.085938 35.871094 37.09375 36 37.09375 C 36.128906 37.09375 36.234375 37.082031 36.34375 37.0625 C 36.582031 36.875 36.578125 36.449219 36.71875 35.90625 C 37.628906 32.410156 39.277344 29.773438 41.0625 27.53125 C 41.890625 26.246094 42.769531 25.089844 43.59375 24 C 43.734375 23.816406 43.863281 23.621094 44 23.4375 C 44.214844 23.152344 44.417969 22.878906 44.625 22.59375 C 44.722656 22.460938 44.808594 22.320313 44.90625 22.1875 C 45.0625 21.964844 45.222656 21.753906 45.375 21.53125 C 45.484375 21.367188 45.582031 21.195313 45.6875 21.03125 C 45.820313 20.828125 45.972656 20.644531 46.09375 20.4375 C 46.203125 20.253906 46.304688 20.0625 46.40625 19.875 C 46.515625 19.679688 46.621094 19.480469 46.71875 19.28125 C 46.820313 19.078125 46.910156 18.863281 47 18.65625 C 47.085938 18.464844 47.175781 18.257813 47.25 18.0625 C 47.339844 17.832031 47.394531 17.613281 47.46875 17.375 C 47.527344 17.1875 47.605469 17.003906 47.65625 16.8125 C 47.726563 16.53125 47.761719 16.230469 47.8125 15.9375 C 47.839844 15.773438 47.886719 15.636719 47.90625 15.46875 C 47.964844 15 48 14.503906 48 14 C 48 7.382813 42.617188 2 36 2 Z M 6.34375 6 C 3.953125 6 2 7.953125 2 10.34375 L 2 43.65625 C 2 43.882813 2.027344 44.09375 2.0625 44.3125 C 2.382813 46.386719 4.179688 48 6.34375 48 L 39.65625 48 C 39.882813 48 40.09375 47.972656 40.3125 47.9375 C 42.277344 47.632813 43.8125 46.011719 43.96875 44 L 44 44 L 44 26.78125 C 43.582031 27.355469 43.160156 27.957031 42.75 28.59375 L 42.625 28.78125 C 40.597656 31.328125 39.367188 33.675781 38.65625 36.40625 C 38.625 36.523438 38.613281 36.617188 38.59375 36.71875 C 38.515625 37.125 38.371094 37.773438 37.90625 38.3125 L 42 42.40625 L 42 43.65625 C 42 44.941406 40.941406 46 39.65625 46 L 38.40625 46 L 24.34375 31.96875 L 27.9375 28.3125 L 31.5 31.90625 C 30.914063 30.871094 30.21875 29.839844 29.375 28.78125 L 29.25 28.59375 C 28.667969 27.691406 28.078125 26.902344 27.5 26.125 L 7.59375 46 L 6.34375 46 C 5.058594 46 4 44.941406 4 43.65625 L 4 42.40625 L 24.46875 21.90625 C 23.058594 19.671875 22 17.183594 22 14 C 22 11.027344 22.941406 8.269531 24.53125 6 Z M 12.125 10 C 13.710938 10 15.132813 10.605469 16.21875 11.59375 L 14.5 13.3125 C 13.855469 12.761719 13.039063 12.4375 12.125 12.4375 C 10.089844 12.4375 8.4375 14.089844 8.4375 16.125 C 8.4375 18.160156 10.089844 19.8125 12.125 19.8125 C 13.835938 19.8125 15.003906 18.789063 15.4375 17.375 L 12.125 17.375 L 12.125 15.03125 L 17.875 15.03125 C 18.378906 17.414063 17.46875 22.21875 12.125 22.21875 C 8.75 22.21875 6 19.5 6 16.125 C 6 12.75 8.75 10 12.125 10 Z M 36 11 C 37.65625 11 39 12.34375 39 14 C 39 15.65625 37.65625 17 36 17 C 34.34375 17 33 15.65625 33 14 C 33 12.34375 34.34375 11 36 11 Z"></path></svg>
					</span>
				</a>
			</li>
		<?php endif; ?>
	</ul>
</div>
<?php
$output_string = ob_get_contents();
ob_end_clean();
return $output_string;
}
add_shortcode('social_list','social_list');


// Enable media_library_infinite_scrolling
class Enable_Media_Library_Infinite_Scrolling {
	public function __construct() {
		$this->add_hooks();
	}
	public function add_hooks() {
		add_filter( 'media_library_infinite_scrolling', '__return_true' );
	}
}
new Enable_Media_Library_Infinite_Scrolling();

 // Disables the block editor from managing widgets in the Gutenberg plugin.
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
// Disables the block editor from managing widgets.
add_filter( 'use_widgets_block_editor', '__return_false' );



/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}


