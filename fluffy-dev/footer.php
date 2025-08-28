<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package fluffy
 */
?>
	<footer id="colophon" class="site-footer">
		<div class="v-container">
			<div class="row-footer">
					<div class="col-info">
						<div class="site-brand-footer">
							<?php if (!empty(get_field('logo_footer','option'))): ?>
								<img src="<?php echo get_field('logo_footer','option')['url']; ?>" alt="footer-logo">
								<?php else: ?>
									<?php the_custom_logo(); ?>
							<?php endif; ?>
						</div><!-- site-brand-footer -->
						<?php if (!empty(get_field('footer_info','option'))): ?>
							<p><?php echo get_field('footer_info','option'); ?></p>
							<?php else: ?>
								<p>พบบริการดูแลสุขภาพที่ครบวงจร ในแอปเดียว ทั้งปรึกษาแพทย์ ปรึกษาเภสัชกร ช้อปสินค้าสุขภาพ และจัดส่งให้คุณถึงบ้าน ภายใต้มาตรฐานเครือ BDMS</p>
						<?php endif; ?>
					</div>
				<div class="right">
					<?php if (is_active_sidebar('footer-widget-1')): ?>
						<div class="col-1 footer-item">
								<?php dynamic_sidebar( 'footer-widget-1' ); ?>
						</div>
					<?php endif; ?>
					<?php if (is_active_sidebar('footer-widget-2')): ?>
						<div class="col-2 footer-item">
								<?php dynamic_sidebar( 'footer-widget-2' ); ?>
						</div>
					<?php endif; ?>
					<?php if (is_active_sidebar('footer-widget-3')): ?>
						<div class="col-3 footer-item">
								<?php dynamic_sidebar( 'footer-widget-3' ); ?>
						</div>
					<?php endif; ?>
					<?php if (is_active_sidebar('footer-widget-4')): ?>
						<div class="col-4 footer-item">
								<?php dynamic_sidebar( 'footer-widget-4' ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<div class="site-info">
				<div class="left">
						<p>© <?php yp_text( 'Copyright', 'Copyright' );  echo ' '.date('Y').' '.get_bloginfo().' ';  yp_text('All rights reserved.','All rights reserved.'); ?></p>
				</div>
				<div class="vc-social-icon">
					<?php echo do_shortcode('[social_list]'); ?>
				</div>
		</div><!-- .site-info -->
	</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
