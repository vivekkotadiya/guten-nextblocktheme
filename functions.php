<?php

/**
 * Next Block Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage nextBlockTheme
 * @since Next Block Theme 1.0
 */


if (!function_exists('NBT_theme_support')) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Next Block Theme 1.0
	 *
	 * @return void
	 */
	function NBT_theme_support()
	{

		// Enqueue editor styles.
		add_editor_style('style.css');

		// register new sizes images
		add_image_size('xs', 480);
		add_image_size('md', 1024);
		add_image_size('xl', 1920);


	}

endif;

add_action('after_setup_theme', 'NBT_theme_support');


if (!function_exists('NBT_styles')) :

	/**
	 * Enqueue styles.
	 *
	 * @since Next Block Theme 1.0
	 *
	 * @return void
	 */
	function NBT_styles()
	{
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get('Version');

		$version_string = is_string($theme_version) ? $theme_version : false;
		wp_register_style(
			'NBT-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style('NBT-style');
	}

endif;

add_action('wp_enqueue_scripts', 'NBT_styles');



if (!function_exists('NBT_preload_webfonts')) :

	/**
	 * Preloads the main web font to improve performance.
	 *
	 * Only the main web font (font-style: normal) is preloaded here since that font is always relevant (it is used
	 * on every heading, for example). The other font is only needed if there is any applicable content in italic style,
	 * and therefore preloading it would in most cases regress performance when that font would otherwise not be loaded
	 * at all.
	 *
	 * @since Next Block Theme 1.0
	 *
	 * @return void
	 */
	function NBT_preload_webfonts()
	{ ?>
		<link rel="preload" href="<?php echo esc_url(get_theme_file_uri('assets/fonts/barlow-regular.woff2')); ?>" as="font" type="font/woff2" crossorigin>
<?php }

endif;

add_action('wp_head', 'NBT_preload_webfonts');



if (file_exists(get_template_directory() . '/inc/optimization.php')) {

	/**
	 * Remove emoji, feed url and other stuff from wordpress Head
	 */

	 require get_template_directory() . '/inc/optimization.php';
}

if (file_exists(get_template_directory() . '/inc/custom-post.php')) {

	/**
	 * Create custom post
	 */

	require get_template_directory() . '/inc/custom-post.php';
}

if (file_exists(get_template_directory() . '/inc/converter.php')) {
	/**
	 * compress jpg and png image and convert to webp
	 */
	require_once get_template_directory() . '/inc/converter.php';
}





