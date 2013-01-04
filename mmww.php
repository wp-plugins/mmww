<?php
		/*
		Plugin Name: MMWW
		Plugin URI: http://joebackward.wordpress.com/2012/11/25/mmww-media-metadata-workfilow-wizard-plugin-for-wordpress-3-4/
		Description: Use the Media Metadata Workflow Wizard to integrate your media metadata workflow with WordPress's Media Library. If you create lots of images, audio clips, or video clips you probably work hard to put metadata (titles, authors, copyrights, track names, dates, and all that) into them. Now you can have that metadata stored into the Media Library automatically when you upload your media files.
		Author: Ollie Jones
		Version: 0.9.1
		Author URI: http://joebackward.wordpress.com/2012/11/25/mmww-media-metadata-workfilow-wizard-plugin-for-wordpress-3-4/
		Text Domain: mmww
		*/
/** current version number  */
if (!defined('MMWW_VERSION_NUM')) {
	define('MMWW_VERSION_NUM', '0.9.1');
}
/* set up some handy globals */
if (!defined('MMWW_THEME_DIR')) {
	define('MMWW_THEME_DIR', ABSPATH . 'wp-content/themes/' . get_template());
}
if (!defined('MMWW_PLUGIN_NAME')) {
	define('MMWW_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
}
if (!defined('MMWW_PLUGIN_DIR')) {
	define('MMWW_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . MMWW_PLUGIN_NAME);
}
if (!defined('MMWW_PLUGIN_URL')) {
	define('MMWW_PLUGIN_URL', WP_PLUGIN_URL . '/' . MMWW_PLUGIN_NAME);
}
if (!defined('MMWW_POSTMETA_KEY')) {
	define('MMWW_POSTMETA_KEY', '_' . MMWW_PLUGIN_NAME . '_metadata');
}

//TODO make this go away
ini_set('display_errors', 'On');
error_reporting(E_ALL);

register_activation_hook( __FILE__, 'mmww_activate' );

$saved = get_include_path();
set_include_path( $saved . PATH_SEPARATOR . MMWW_PLUGIN_DIR . '/code' );

add_action( 'init', 'mmww_do_everything' );

/* check version and upgrade plugin if need be. */
if (MMWW_VERSION_NUM != ($opt = get_option('mmww_version', '0.0.0'))) {
	/* do update procedure here as needed */
	update_option('mmww_version', MMWW_VERSION_NUM);
}

function mmww_do_everything () {

	if ( is_admin() && current_user_can ( 'manage_options' )) {
		require_once ( 'code/mmww_admin.php' );
	}
	if (current_user_can( 'upload_files' )) {
		require_once( 'code/mmww_media_upload.php' );
	}
}

function mmww_activate() {
	if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) ); /* fail activation */
	}
	/* make sure the options are loaded, but don't overwrite version */
	add_option('mmww_version', MMWW_VERSION_NUM, false);
	$o = array (
		'audio_shortcode' => 'disabled', /* Custom, Attachment, Media, None, disabled */
		);
	add_option('mmww_options', $o, false);
}
