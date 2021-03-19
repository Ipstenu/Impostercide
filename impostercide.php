<?php
/*
Plugin Name: Impostercide
Plugin URI: http://halfelf.org/plugins/impostercide/
Description: Impostercide prevents unauthenticated users from "signing" a comment as a registered users.
Version: 3.0
Author: Mika Epstein, Scott Merrill
Author URI: http://halfelf.org/
Network: true
Text Domain: impostercide

Copyright 2005-07 Scott Merrill (skippy@skippy.net)
Copyright 2010-21 Mika Epstein (ipstenu@halfelf.org)

This file is part of Impostercide, a plugin for WordPress.

Impostercide is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

Impostercide is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Settings Class
 *
 * @since 3.0
 */
class Impostercide {

	// Holds option data.
	public $option_name = 'impostercide_options';
	public $option_defaults;
	public $options;

	// DB version, for schema upgrades. It's not being used but just in case.
	public $db_version = 1;

	// Instance
	public static $instance;

	/**
	 * Construct
	 *
	 * @since 3.0
	 * @access public
	 */
	public function __construct() {

		//allow this instance to be called from outside the class
		self::$instance = $this;

		// Setting plugin defaults here:
		$this->option_defaults = array(
			'db_version' => $this->db_version,
			'header'     => __( 'Possible Imposter', 'impostercide' ),
			'message'    => __( 'You are attempting to post a comment with information (i.e. email address or login ID) belonging to a registered user. If you have an account on this site, please login to make your comment. Otherwise, please try again with different information.', 'impostercide' ),
			'error'      => __( 'Error: Imposter Detected', 'impostercide' ),
		);

		// Fetch and set up options.
		$this->options = wp_parse_args( get_site_option( $this->option_name ), $this->option_defaults, false );

		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		// add admin panel
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( &$this, 'network_admin_menu' ) );
			add_action( 'current_screen', array( &$this, 'network_admin_screen' ) );
		} else {
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		}

		add_filter( 'preprocess_comment', array( &$this, 'impostercide_protect_email' ) );
	}

	/**
	 * Admin init Callback
	 *
	 * @since 3.0
	 */
	public function admin_init() {

		// Settings links
		add_filter( 'plugin_row_meta', array( &$this, 'settings_link' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( &$this, 'donate_link' ), 10, 2 );

		// Register Settings
		$this->register_settings();
	}

	/**
	 * Admin Menu
	 *
	 * @since 3.0
	 * @access public
	 */
	public function admin_menu() {
		add_management_page( __( 'Impostercide', 'impostercide' ), __( 'Impostercide', 'impostercide' ), 'remove_users', 'impostercide', array( &$this, 'options' ) );
	}

	/**
	 * Network Admin Menu
	 *
	 * @since 3.0
	 * @access public
	 */
	public function network_admin_menu() {
		add_submenu_page( 'settings.php', __( 'Impostercide', 'impostercide' ), __( 'Impostercide', 'impostercide' ), 'manage_networks', 'impostercide', array( &$this, 'options' ) );
	}

	/**
	 * Network Admin Screen Callback
	 *
	 * @since 3.0
	 */
	public function network_admin_screen() {
		$current_screen = get_current_screen();
		if ( 'settings_page_impostercide-network' === $current_screen->id ) {

			if ( isset( $_POST['update'] ) && check_admin_referer( 'impostercide_networksave' ) ) {
				$options = $this->options;
				$input   = $_POST['impostercide_options']; // phpcs:ignore - Sanitized further down.
				$output  = array();

				// This is hardcoded for a reason.
				$output['db_version'] = $this->db_version;

				// This is not currently changeable but may be later...
				$output['error'] = $options['error'];

				// Header
				if ( $input['header'] !== $options['header'] && sanitize_text_field( $input['header'] ) === $input['header'] ) {
					$output['header'] = sanitize_text_field( $input['header'] );
				} else {
					$output['header'] = $options['header'];
				}

				// Message
				if ( $input['message'] !== $options['message'] && wp_kses_post( $input['message'] ) === $input['message'] ) {
					$output['message'] = wp_kses_post( $input['message'] );
				} else {
					$output['message'] = $options['message'];
				}

				$this->options = $output;
				update_site_option( $this->option_name, $output );

				?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Options Updated!', 'impostercide' ); ?></strong></p></div>
				<?php
			}
		}
	}

	/**
	 * Register Admin Settings
	 *
	 * @since 3.0
	 */
	public function register_settings() {
		register_setting( 'impostercide', 'impostercide_options', array( &$this, 'impostercide_sanitize' ) );

		// The main section
		add_settings_section( 'impostercide-settings', '', array( &$this, 'impostercide_settings_callback' ), 'impostercide-settings' );

		// The Fields
		add_settings_field( 'header', __( 'Header', 'impostercide' ), array( &$this, 'header_callback' ), 'impostercide-settings', 'impostercide-settings' );
		add_settings_field( 'message', __( 'Message', 'impostercide' ), array( &$this, 'message_callback' ), 'impostercide-settings', 'impostercide-settings' );
	}

	/**
	 * Settings Callback
	 *
	 * @since 3.0
	 */
	public function impostercide_settings_callback() {
		?>
		<p><?php esc_html_e( 'Customize your Impostercide experience via the settings below.', 'impostercide' ); ?></p>
		<?php
	}

	/**
	 * Header Callback
	 *
	 * @since 3.0
	 */
	public function header_callback() {
		?>
		<p><?php esc_html_e( 'The header is essentially the page title. Keep it short and to the point.', 'impostercide' ); ?></p>
		<p><textarea name="impostercide_options[header]" id="impostercide_options[header]" cols="80" rows="2"><?php echo esc_html( $this->options['header'] ); ?></textarea></p>
		<?php
	}

	/**
	 * Message Callback
	 *
	 * @since 3.0
	 */
	public function message_callback() {
		?>
		<p><?php esc_html_e( 'The message below is displayed to users who are caught by the Imposter check. Edit is as you see fit, but remember you don\'t get a lot of space so keep it simple.', 'impostercide' ); ?></p>
		<p><textarea name="impostercide_options[message]" id="impostercide_options[message]" cols="80" rows="8"><?php echo esc_html( $this->options['message'] ); ?></textarea></p>
		<?php
	}

	/**
	 * Options
	 *
	 * The options page. Since this has to run on Multisite, it can't use the settings API.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function options() {
		?>
		<div class="wrap">

		<h1><?php esc_html_e( 'Impostercide', 'impostercide' ); ?></h1>

		<?php
		settings_errors();

		if ( is_network_admin() ) {
			?>
			<form method="post" width='1'>
			<?php
			wp_nonce_field( 'impostercide_networksave' );
		} else {
			?>
			<form action="options.php" method="POST" >
			<?php
			settings_fields( 'impostercide' );
		}
				do_settings_sections( 'impostercide-settings' );
				submit_button( '', 'primary', 'update' );
		?>
			</form>
		</div>
		<?php
	}

	/**
	 * Options sanitization and validation
	 *
	 * @param $input the input to be sanitized
	 * @since 2.6
	 */
	public function impostercide_sanitize( $input ) {
		// Get current options
		$options = $this->options;
		$output  = array();

		// This is hardcoded for a reason.
		$output['db_version'] = $this->db_version;

		// This is not currently changeable but may be later...
		$output['error'] = $options['error'];

		// Header
		if ( $input['header'] !== $options['header'] && sanitize_text_field( $input['header'] ) === $input['header'] ) {
			$output['header'] = sanitize_text_field( $input['header'] );
		} else {
			$output['header'] = $options['header'];
		}

		// Message
		if ( $input['message'] !== $options['message'] && wp_kses_post( $input['message'] ) === $input['message'] ) {
			$output['message'] = wp_kses_post( $input['message'] );
		} else {
			$output['message'] = $options['message'];
		}

		return $output;
	}

	/**
	 * Impostercide - this is the magic sauce.
	 *
	 * It looks odd, but effectively if the object is fine, we return it. If not, we DIE.
	 *
	 * @param  object $data The data object passed to the comment form.
	 * @return object       The unedited data object.
	 */
	public function impostercide_protect_email( $data ) {
		global $wpdb, $user_ID, $user_login, $user_email;

		extract( $data );

		if ( 'comment' !== $comment_type ) {
			// it's not a comment, let it through
			return $data;
		}

		$current_user = wp_get_current_user();

		if ( is_user_logged_in() ) {
			// It's a logged in user, so it's good.
			return $data;
		}

		// Get the options
		$options = $this->options;

		// Build the message
		$imposter_message = '<h2>' . $options['header'] . '</h2> <p>' . $options['message'] . '</p>';
		$imposter_error   = $options['error'];

		// a name was supplied, so let's check the login names
		if ( '' !== $comment_author ) {
			$result = $wpdb->get_var( "SELECT count(ID) FROM $wpdb->users WHERE user_login='$comment_author'" );
			if ( $result > 0 ) {
				wp_die( wp_kses_post( $imposter_message ), esc_html( $imposter_error ) );
			}
		}

		// an email was supplied, so let's see if we know about it
		if ( '' !== $comment_author_email ) {
			$result = $wpdb->get_var( "SELECT count(ID) FROM $wpdb->users WHERE user_email='$comment_author_email'" );
			if ( $result > 0 ) {
				wp_die( wp_kses_post( $imposter_message ), esc_html( $imposter_error ) );
			}
		}

		return $data;
	}


	/**
	 * Donate Link
	 * @param  array $links  array of links in plugin list
	 * @param  string $file  plugin base name
	 * @return array         New Links
	 */
	public function donate_link( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$donate_link = '<a href="https://ko-fi.com/A236CEN/">Donate</a>';
			$links[]     = $donate_link;
		}
		return $links;
	}

	/**
	 * Settings link
	 *
	 * Adds link to settings page on the plugins page
	 *
	 * @access public
	 */
	public function settings_link( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			if ( is_multisite() ) {
				$settings_link = network_admin_url( 'settings.php?page=impostercide' );
			} else {
				$settings_link = admin_url( 'tools.php?page=impostercide' );
			}

			$settings_link = '<a href="' . $settings_link . '">' . __( 'Settings', 'impostercide' ) . '</a>';
			if ( user_can( get_current_user_id(), 'remove_users' ) ) {
				$links[] = $settings_link;
			}
		}

		return $links;
	}

}

new Impostercide();
