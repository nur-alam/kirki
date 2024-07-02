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
 * ImportTemplate class
 */
class ImportTemplate {

	/**
	 * Register hooks and dependency
	 *
	 * @param   bool $register_hooks  register hooks or not.
	 *
	 * @return  bool
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		// add_action( 'wp_ajax_install_plugin', array( $this, 'install_plugin' ) );
	}

	private function find_wp_attachment_ids($array, &$result = []) {
		foreach ($array as $key => $value) {
			if( $key === 'properties' && $value['tag'] === 'img' ) {
				$result[$value['wp_attachment_id']] = $value['attributes'];
			}
			// if ($key === 'wp_attachment_id') {
			// 	$result[] = $value;
			// } 
			elseif (is_array($value)) {
				$this->find_wp_attachment_ids($value, $result);
			}
		}
		return $result;
	}

	private function upload_attachment_to_destination($attachment) {
        // $upload_dirr = wp_upload_dir();
        // $upload_dir = wp_upload_dir()['basedir'];
        // $file_url = $attachment->source_url;
        // $filename = basename($attachment->source_url);
        // $source_dir_url  = str_replace( $filename, '', $file_url );
        // $source_dir_part = str_replace( 'https://www.themeum.com/wp-content/uploads/', '', $source_dir_url );

		// $file_path = trailingslashit( $upload_dir ) . trailingslashit( $source_dir_part ) . $filename;

		// $upload_dir = trailingslashit( $upload_dir ) . trailingslashit( $source_dir_part );

        $response = wp_remote_get($attachment->source_url);
        if (is_wp_error($response)) {
            return;
        }

        $file_data = wp_remote_retrieve_body($response);
        return;
        $upload = wp_upload_bits($attachment->title->rendered . '.' . pathinfo($attachment->source_url, PATHINFO_EXTENSION), null, $file_data);

        if ($upload['error']) {
            return;
        }

        $filename = $upload['file'];
        $filetype = wp_check_filetype($filename);
        $attachment_data = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name($attachment->title->rendered),
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attachment_id = wp_insert_attachment($attachment_data, $filename);

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $filename);
        wp_update_attachment_metadata($attachment_id, $attachment_metadata);
    }

	/**
	 * Import driop page, feature image & meta data.
	 */
	public function import() {
		global $wpdb;
		$page_id       = $_POST['page_id'];
		$template_id   = $_POST['template_id'];
        $droip_page    = 'http://droip.test/wp-json/droiptemplate-provider/v1/droip-templates/' . $template_id;
		$template_data = wp_remote_get($droip_page);
		$template_body = wp_remote_retrieve_body($template_data);
		$template = json_decode($template_body);
        $template_droip_value = $template[0]->droip_value;
        $meta_value = unserialize($template_droip_value);
        $wp_attachment_ids = $this->find_wp_attachment_ids($meta_value);
        if ( count( $wp_attachment_ids ) ) {
            foreach ( $wp_attachment_ids as $key => $value ) {
                $attachment_data = wp_remote_get('http://droip.test/wp-json/wp/v2/media/' . $key);
                $attachment_body = json_decode(wp_remote_retrieve_body($attachment_data));
                $this->upload_attachment_to_destination($attachment_body);
                // $source_file = wp_remote_get($value['src']);
                // $file_data = wp_remote_retrieve_body($source_file);
            }
        }
        $args = [
            'post_id' 		=>   $page_id,
            'meta_key' 		=>   'droip',
            'meta_value'	=>   $template_droip_value,
        ];
        $droip_editor_mode_meta = $wpdb->insert(
            $wpdb->postmeta,
            [
                'post_id' 		=>   $page_id,
                'meta_key' 		=>   'droip_editor_mode',
                'meta_value'	=>   'droip',
            ]
        );
        if (! $droip_editor_mode_meta ) {
            wp_send_json_error('Error: ' . $droip_editor_mode_meta );
        }
        $inserted = $wpdb->insert(
            $wpdb->postmeta,
            $args,
            [
                '%d', '%s', '%s'
            ]
        );
        if (! $inserted ) {
            wp_send_json_error('Error: ' . $inserted );
        }
		return wp_send_json_success($inserted);
	}
}
