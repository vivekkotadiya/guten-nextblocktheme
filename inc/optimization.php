<?php

/**
 * Cleanup wp head.
 */

function NTB_remove_version()
{
	return '';
}

add_filter('the_generator', 'NTB_remove_version');

remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

function cleanup_query_string($src)
{
	$parts = explode('?', $src);
	return $parts[0];
}
add_filter('script_loader_src', 'cleanup_query_string', 15, 1);
add_filter('style_loader_src', 'cleanup_query_string', 15, 1);

/**
 * Disable the emoji's
 */
function disable_emojis()
{
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

	// Remove from TinyMCE
	add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
}

add_action('init', 'disable_emojis');

/**
 * Filter out the tinymce emoji plugin.
 */
function disable_emojis_tinymce($plugins)
{
	if (is_array($plugins)) {
		return array_diff($plugins, array('wpemoji'));
	} else {
		return array();
	}
}

/**
 * Filter out the global styles
 */

remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

// remove DNS prefetch
remove_action('wp_head', 'wp_resource_hints', 2);

/**
 * remove theme support
 */
if (!function_exists('NBT_theme_setup')) :
	function NBT_theme_setup()
	{

		remove_theme_support('automatic-feed-links');

		// remove_theme_support('post-thumbnails');

		remove_theme_support('editor-styles');

		remove_theme_support('editor-script');

		remove_theme_support('wp-block-styles');

		remove_theme_support('core-block-patterns');

		add_theme_support('menus');

		// remove_theme_support('post-formats');

		add_theme_support('post-thumbnails');
	}
endif;

add_action('after_setup_theme', 'NBT_theme_setup');

// dequeue unused styles
function remove_wp_block_library_css()
{

	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wp-block-site-logo');
	wp_dequeue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'remove_wp_block_library_css');

// remove classes from body
add_filter('body_class', 'remove_body_classes');

function remove_body_classes($classes)
{
	$remove_classes = ['wp-custom-logo', 'wp-embed-responsive'];
	$classes = array_diff($classes, $remove_classes);
	return $classes;
}

remove_action('wp_footer', 'the_block_template_skip_link');

add_filter('image_size_names_choose', 'child_custom_sizes');

function child_custom_sizes($sizes)
{
	return array_merge($sizes, array(
		'xl' => 			__('Large'),
		'md' => 			__('Medium'),
		'xs' => 			__('Small'),
	));
}

/** 
 * Wp-contentainer filter
 */
remove_filter('render_block',  'wp_render_layout_support_flag');

add_filter('render_block', 'wp_render_theme_layout_support_flag', 11, 9);

function wp_render_theme_layout_support_flag($block_content, $block)
{
	return $block_content;
}

add_filter('allowed_block_types_all', 'theme_allowed_block_types');

function theme_allowed_block_types($allowed_blocks)
{

	return array(
		'gbblocks/header',
		'gbblocks/footer',
		'gbblocks/section',
		'gbblocks/row',
		'gbblocks/column',
		'gbblocks/swipesection',
		'gbblocks/headline',
		'gbblocks/paragraph',
		'gbblocks/list',
		'gbblocks/button',
		'gbblocks/image',
		'gbblocks/divider',
		'gbblocks/youtube',
		'gbblocks/youtubelightbox',
		'gbblocks/icon',
		'gbblocks/icontext',
		'gbblocks/quote',
		'gbblocks/listlink',
		'gbblocks/accordion',
		'gbblocks/cardcollection',
		'gbblocks/card',
		'gbblocks/headlinenumbered',
		'gbblocks/newsslider',
		'gbblocks/newslisting',
		'gbblocks/customerlisting',
		'gbblocks/customerpreview',
		'gbblocks/quoteimage',
		'gbblocks/imageoverlaycollection',
		'gbblocks/imageoverlay',
		'gbblocks/heromainsection',
		'gbblocks/herosection',
		'gbblocks/locations',
		'gbblocks/locationitem',
		'gbblocks/sectioncontent',
		'gbblocks/downloadlisting',
		'gbblocks/jobscollection',
		'gbblocks/jobcard',
		'gbblocks/testimonialslider',
		'gbblocks/testimonialslide',
		'gbblocks/timelineslider',
		'gbblocks/timelineslide',
		'gbblocks/form',
		'gbblocks/tabs',
		'gbblocks/tabcontent',
		'gbblocks/imagebutton',
		'gbblocks/visualcircle',
		'gbblocks/visualcircleitem',
		'gbblocks/visualimage',
		'gbblocks/circlepreview',
		'gbblocks/previewitem',
		'gbblocks/videoslider',
		'gbblocks/newsletter',
		'polylang/language-switcher'
	);
}


/**
 * Removes some menus by page.
 */
function nbt_remove_menus()
{
	remove_menu_page('edit.php');                   //Posts
	remove_menu_page('edit-comments.php');          //Comments

}
add_action('admin_menu', 'nbt_remove_menus');



function nbt_custom_menu_order($menu_ord)
{
	return array(
		'index.php', // Dashboard
		'separator1', // First separator
		'edit.php?post_type=page',
		'edit.php?post_type=news', // Posts
		'edit.php?post_type=customers', // Media
		'edit.php?post_type=downloads', // Links
		'upload.php', // Comments
		'separator2', // Second separator
		'themes.php', // Appearance
		'plugins.php', // Plugins
		'users.php', // Users
		'tools.php', // Tools
		'options-general.php', // Settings
		'separator-last', // Last separator
	);
}
add_filter('custom_menu_order', 'nbt_custom_menu_order', 10, 1);
add_filter('menu_order', 'nbt_custom_menu_order', 10, 1);

function NTB_register_setting()
{
	register_setting('ntb_smtp_setting', 'smtp_host');
	register_setting('ntb_smtp_setting', 'smtp_port');
	register_setting('ntb_smtp_setting', 'smtp_username');
	register_setting('ntb_smtp_setting', 'smtp_password');
	register_setting('ntb_smtp_setting', 'smtp_secure');
	register_setting('ntb_smtp_setting', 'smtp_from');
	register_setting('ntb_smtp_setting', 'smtp_fromName');
}
add_action('admin_init', 'NTB_register_setting');

function NTB_register_options_page()
{
	add_options_page('SMTP Settings', 'SMTP Settings', 'manage_options', 'smtp_setting', 'NTB_option_page');
}
add_action('admin_menu', 'NTB_register_options_page');

function NTB_option_page()
{
?>
	<div>
		<h2>SMTP Settings</h2>
		<form method="post" action="options.php">
			<?php settings_fields('ntb_smtp_setting'); ?>
			<table>
				<tr valign="top">
					<th scope="row"><label for="smtp_host">SMTP Host</label></th>
					<td><input type="text" id="smtp_host" name="smtp_host" value="<?php echo get_option('smtp_host'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smtp_port">SMTP Port</label></th>
					<td><input type="text" id="smtp_port" name="smtp_port" value="<?php echo get_option('smtp_port'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smtp_username">SMTP Username</label></th>
					<td><input type="text" id="smtp_username" name="smtp_username" value="<?php echo get_option('smtp_username'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smtp_password">SMTP Password</label></th>
					<td><input type="password" id="smtp_password" name="smtp_password" value="<?php echo get_option('smtp_password'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smtp_secure">SMTP Secure</label></th>
					<td><input type="text" id="smtp_secure" name="smtp_secure" value="<?php echo get_option('smtp_secure'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smtp_from">From Email</label></th>
					<td><input type="text" id="smtp_from" name="smtp_from" value="<?php echo get_option('smtp_from'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smtp_fromName">From Name</label></th>
					<td><input type="text" id="smtp_fromName" name="smtp_fromName" value="<?php echo get_option('smtp_fromName'); ?>" /></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}
function NTB_phpmailer_example($phpmailer)
{
	$phpmailer->isSMTP();
	$phpmailer->Host = get_option('smtp_host');
	$phpmailer->SMTPAuth = true;
	$phpmailer->Port = get_option('smtp_port');
	$phpmailer->Username = get_option('smtp_username');
	$phpmailer->Password = get_option('smtp_password');

	// Additional settings…
	$phpmailer->SMTPSecure = get_option('smtp_secure'); // Choose 'ssl' for SMTPS on port 465, or 'tls' for SMTP+STARTTLS on port 25 or 587
	$phpmailer->From = get_option('smtp_from');
	$phpmailer->FromName = get_option('smtp_fromName');
	$phpmailer->SMTPDebug = 0; // write 0 if you don't want to see client/server communication in page
	$phpmailer->CharSet  = "utf-8";
}
add_action('phpmailer_init', 'NTB_phpmailer_example');
