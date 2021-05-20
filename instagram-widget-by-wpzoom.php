<?php
/**
 * Plugin Name: WPZOOM Instagram Widget
 * Plugin URI: https://www.wpzoom.com/plugins/instagram-widget/
 * Description: Simple and lightweight widget for WordPress to display your Instagram feed
 * Version: 1.7.7
 * Author: WPZOOM
 * Author URI: https://www.wpzoom.com/
 * Text Domain: instagram-widget-by-wpzoom
 * Domain Path: /languages
 * License: GPLv2 or later
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPZOOM_INSTAGRAM_VERSION' ) ) {
	define( 'WPZOOM_INSTAGRAM_VERSION', '1.7.7' );
}

require_once plugin_dir_path( __FILE__ ) . 'class-wpzoom-instagram-image-uploader.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wpzoom-instagram-widget-api.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wpzoom-instagram-widget-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wpzoom-instagram-widget.php';

add_action( 'widgets_init', 'zoom_instagram_widget_register' );
function zoom_instagram_widget_register() {
	register_widget( 'Wpzoom_Instagram_Widget' );
}

/* Display a notice that can be dismissed */

add_action( 'admin_notices', 'wpzoom_instagram_admin_notice' );

function wpzoom_instagram_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $current_user;
	$user_id = $current_user->ID;
	/* Check that the user hasn't already clicked to ignore the message */
	if ( ! get_user_meta( $user_id, 'wpzoom_instagram_admin_notice' ) ) {
		/**
		 * Fixed dismiss url
		 *
		 * @since 1.7.5
		 */
		$hide_notices_url = html_entity_decode( // to convert &amp;s to normal &, otherwise produces invalid link.
			add_query_arg(
				array(
					'wpzoom_instagram_ignore_admin_notice' => '0',
				),
				wpzoom_instagram_get_current_admin_url() ? wpzoom_instagram_get_current_admin_url() : admin_url( 'options-general.php?page=wpzoom-instagram-widget' )
			)
		);

		$configure_message  = '<strong>' . __( 'Please configure Instagram Widget', 'instagram-widget-by-wpzoom' ) . '</strong><br/><br/>';
		$configure_message .= sprintf( __( 'If you have just installed or updated this plugin, please go to the %1$s and %2$s it with your Instagram account.', 'instagram-widget-by-wpzoom' ), '<a href="options-general.php?page=wpzoom-instagram-widget">' . __( 'Settings page', 'instagram-widget-by-wpzoom' ) . '</a>', '<strong>' . __( 'connect', 'instagram-widget-by-wpzoom' ) . '</strong>' ) . '&nbsp;';
		$configure_message .= __( 'You can ignore this message if you have already configured it.', 'instagram-widget-by-wpzoom' );
		$configure_message .= '<a style="text-decoration: none" class="notice-dismiss" href="' . $hide_notices_url . '"></a>';

		echo '<div class="notice-warning notice" style="position:relative"><p>';
		echo wp_kses_post( $configure_message );
		echo '</p></div>';
	}
}

add_action( 'admin_init', 'wpzoom_instagram_ignore_admin_notice' );

function wpzoom_instagram_ignore_admin_notice() {
	global $current_user;
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset( $_GET['wpzoom_instagram_ignore_admin_notice'] ) && '0' == $_GET['wpzoom_instagram_ignore_admin_notice'] ) {
		add_user_meta( $user_id, 'wpzoom_instagram_admin_notice', 'true', true );
	}
}


function wpzoom_instagram_get_default_settings() {
	return array(
		'access-token'             => '',
		'basic-access-token'       => '',
		'request-type'             => 'with-basic-access-token',
		'username'                 => '',
		'transient-lifetime-value' => 1,
		'transient-lifetime-type'  => 'days',
		'is-forced-timeout'        => '',
		'request-timeout-value'    => 15,
		'user-info-avatar'         => '',
		'user-info-fullname'       => '',
		'user-info-biography'      => '',
	);
}

add_action(
	'init',
	function () {
		$option_name = 'wpzoom-instagram-transition-between-4_7-4_8-versions';
		if ( empty( get_option( $option_name ) ) ) {
			update_option( $option_name, true );
			delete_transient( 'zoom_instagram_is_configured' );
		}
	}
);

/**
 * Get current admin page URL.
 *
 * Returns an empty string if it cannot generate a URL.
 *
 * @internal
 * @since 1.7.5
 * @return string
 */
function wpzoom_instagram_get_current_admin_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

	if ( ! $uri ) {
		return '';
	}

	return remove_query_arg( array( '_wpnonce', 'wpzoom_instagram_ignore_admin_notice' ), admin_url( $uri ) );
}

/**
 * Load textdomain
 *
 * @since 1.7.7
 */
function wpzoom_instagram_load_plugin_textdomain() {
	load_plugin_textdomain( 'instagram-widget-by-wpzoom', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wpzoom_instagram_load_plugin_textdomain' );
