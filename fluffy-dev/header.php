<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package fluffy
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'fluffy' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="v-container">
			<div class="site-branding">
					<?php the_custom_logo(); ?>
			</div><!-- .site-branding -->
		<nav id="site-navigation" class="main-navigation">

			<div id="toggle-main-menu" class="_mobile hamburger hamburger--slider">
        <!-- <div class="hamburger-box">
          <div class="hamburger-inner"></div>
        </div> -->
				<svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none">
<path d="M5 8H13.75M5 12H19M10.25 16L19 16" stroke="#464455" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
      </div>

		<div class="desktop_menu _desktop">
			<?php
			// if (has_nav_menu('topbar')) {
			// 	wp_nav_menu(
			// 		array(
			// 			'theme_location' => 'topbar',
			// 			'menu_id'        => 'topbar-menu',
			// 		)
			// 	);
			// }
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
				)
			);
			?>
		</div>

		<div class="overlay_menu_m"></div>
			<div id="mobile_menu_wrap">


				<div id="close-mobile-menu" class="hamburger hamburger--slider is-active">
					<div class="hamburger-box">
						<div class="hamburger-inner"></div>
					</div>
				</div>

				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'mobile',
						'menu_id'        => 'mobile-menu',
					)
				);
				?>
			</div>
		</nav><!-- #site-navigation -->
	<div class="micro-icon">
		<?php yp_popup_search(); ?>
		<?php polylang_slink(); ?>
	</div>
		</div>
	</header>
	<!-- #masthead -->

