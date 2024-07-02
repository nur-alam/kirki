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
 * Droip class
 */
class Droip {

	/**
	 * Register hooks and dependency
	 *
	 * @param   bool   $register_hooks  register hooks or not.
	 *
	 * @return  bool
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
		add_action( 'wp_ajax_install_plugins', array( $this, 'install_plugins' ) );
		add_action( 'wp_ajax_import_template', array( $this, 'import_template' ) );
	}

	/**
	 * Droip integration load_admin_scripts description.
	 *
	 *  @param  string $page page.
	 *
	 * @return  void
	 */
	public function load_admin_scripts( $page ) {
		$data = array(
			'ajax_url'                   => 	admin_url( 'admin-ajax.php' ),
			'nonce_value' 		         => 	wp_create_nonce( 'droip_template_nonce' ),
			'DRIOP_TEMPLATE_BASE_API'    => 	DRIOP_TEMPLATE_BASE_API,
			// 'plugin_url'  => 'https://droip.s3.amazonaws.com/dist/droip-builds/droip-1.1.1.zip',
			// 'plugin_slug' => 'driop',
		);

		if ( 'post.php' === $page ) {
			wp_enqueue_script( 'droip-button-script', KIRKI_PLUGIN_URL . '/assets/dist/js/button.min.js', array( 'wp-element' ), filemtime( KIRKI_PLUGIN_DIR . '/assets/dist/js/button.min.js' ), true );
			wp_enqueue_script( 'droip-integrations', KIRKI_PLUGIN_URL . '/assets/dist/js/droip-integrations.min.js', array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ), filemtime( KIRKI_PLUGIN_DIR . '/assets/dist/js/droip-integrations.min.js' ), true );
		}
		// Add data to use in js files.
		wp_add_inline_script( 'droip-integrations', 'const droipIntegrationObject = ' . wp_json_encode( $data ) . ';window.droipIntegrationObject=droipIntegrationObject', 'before' );
	}

	public function install_plugins() {
		try {
			$plugin_installer = new InstallPlugin();
			$plugin_installer->install_plugin();
			// $templateImporter = new ImportTemplate();
			// $templateImporter->import();
		} catch (\Throwable $th) {
			$error = $th->getMessage();
			$file = $th->getFile();
			$line = $th->getLine();
			wp_send_json_error(['success' => false, 'message' => 'Error: ' . $error . ' ' . $file . ' ' . $line  ]);
		}
	}

	public function import_template() {
		try {
			$templateImporter = new ImportTemplate();
			$templateImporter->import();
		} catch (\Throwable $th) {
			$error = $th->getMessage();
			$file = $th->getFile();
			$line = $th->getLine();
			wp_send_json_error(['success' => false, 'message' => 'Error: ' . $error . ' ' . $file . ' ' . $line  ]);
		}
	}

}
