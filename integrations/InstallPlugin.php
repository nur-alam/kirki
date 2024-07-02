<?php
/**
 * Droip integration
 *
 * @package     Kirki
 * @category    Core
 * @author      Themeum
 * @copyright   Copyright (c) 2023, Themeum
 * @license    https://opensource.org/licenses/MIT
 * @since       1.0
 */

namespace Kirki\Integrations;

/**
 * InstallPlugin class
 */
class InstallPlugin {

	/**
	 * Register hooks and dependency
	 *
	 * @param   bool $register_hooks  register hooks or not.
	 *
	 * @return  bool
	 */
	public function __construct ( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		// add_action( 'wp_ajax_install_plugin', array( $this, 'install_plugin' ) );
		// $this->install_plugin();
	}

	/**
	 * Install required plugin.
	 */
	public function install_plugin () {
		$plugin_url       = 'https://droip.s3.amazonaws.com/dist/droip-builds/droip-1.1.1.zip';
		$plugin_file_path = WP_PLUGIN_DIR . '/droip/droip.php';

		if ( ! wp_verify_nonce( $_REQUEST['droip_template_nonce'], 'droip_template_nonce' ) ) {
			wp_send_json_error([
                'message' => 'Nonce verification failed!'
            ]);
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have sufficient permissions to install plugins.' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$plugin_slug = $this->get_plugin_slug( $plugin_url );
		if ( ! $this->is_plugin_installed( $plugin_slug ) ) {
			$upgrader  = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
			$installed = $upgrader->install( $plugin_url );

			if ( is_wp_error( $installed ) ) {
				wp_send_json_error( array( 'message' => $installed->get_error_message() ) );
			}
		}
		$is_plugin_activated = is_plugin_active( $plugin_file_path );
		if ( ! $is_plugin_activated ) {
			$activate = activate_plugin( $plugin_file_path, '', false, true );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error( array( 'message' => $activate->get_error_message() ) );
			}
		}
		
		return wp_send_json_success( ['message' => 'Droip Installed'] );
	}

	/**
	 * Check plugin is install or not
	 *
	 * @param   string $plugin_slug plugin-slug.
	 *
	 * @return  bool
	 */
	private function is_plugin_installed ( $plugin_slug ) {
		$installed_plugins = get_plugins();
		foreach ( $installed_plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, $plugin_slug ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get_plugin_slug.
	 *
	 * @param   string $plugin_url url as string.
	 *
	 * @return  string
	 */
	private function get_plugin_slug( $plugin_url ) {
		// Extract the plugin slug from the URL.
		$filename       = basename( $plugin_url );
		$clean_filename = $this->remove_text_from_hyphen( $filename );

		return $clean_filename;
	}

	/**
	 * Clean file name from url removing text starting from hyphen.
	 *
	 * @param  string $filename plugin basename.
	 *
	 * @return string
	 */
	private function remove_text_from_hyphen( $filename ) {
		// Find the position of the hyphen in the string.
		$hyphen_pos = strpos( $filename, '-' );
		// If there's no hyphen in the string, return the original string.
		if ( false === $hyphen_pos ) {
			return $filename;
		}
		// Return the substring.
		return substr( $filename, 0, $hyphen_pos );
	}
}
