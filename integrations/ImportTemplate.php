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

use WP_Query;

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

	private function find_wp_attachment_ids( $array, &$result = [] ) {
		foreach ( $array['blocks'] as $key => $value ) {
			if ( $value['name'] === 'image' && $value['properties']['wp_attachment_id'] ) {
				$result[ $value['properties']['wp_attachment_id'] ] = $value['properties']['attributes'];
			}
			// if ($key === 'wp_attachment_id') {
			// 	$result[] = $value;
			// }
			elseif ( is_array($value) ) {
				$this->find_wp_attachment_ids($value, $result);
			}
		}
		return $result;
	}

	private function upload_attachment_to_destination( $attachment ) {
        global $wpdb;
        $attachment_id = $attachment->id;
        $local_upload_dir = wp_upload_dir()['basedir']; // /Users/nuralam/valet/kirki/wp-content/uploads

        // download attachments images with variations
        $attachment_sizes = $attachment->media_details->sizes;
        foreach ( $attachment_sizes as $size_key => $size ) {
            $source_file_url = $size->source_url;
            $filename = basename($source_file_url);
            $source_dir_url  = str_replace( $filename , '', $source_file_url); // like: baseurl/wp-content/uploads/2024/06/
            $source_dir_part = str_replace( 'http://droip.test/wp-content/uploads/' , '', $source_dir_url); // like: 2024/06/
    
            $file_path = trailingslashit( $local_upload_dir ) . trailingslashit( $source_dir_part ) . $filename;
            $upload_dir = trailingslashit( $local_upload_dir ) . trailingslashit( $source_dir_part );
            if( ! file_exists( $file_path ) ) {
                if( ! file_exists($upload_dir) ) {
                    mkdir( $upload_dir, 0755, true );
                }
                $file_data = file_get_contents( $source_file_url );
                if( false !== $file_data ) {
                    file_put_contents( $file_path, $file_data );
                }
            }
        }

        $attachment_post_data = json_decode( wp_remote_retrieve_body( wp_remote_get( DRIOP_TEMPLATE_BASE_API . "/posts/{$attachment->id}" ) ) );

        $post_exist_query = $wpdb->prepare( 
                        "SELECT $wpdb->posts.* FROM $wpdb->posts
                        WHERE $wpdb->posts.post_title = %s AND
                        $wpdb->posts.post_type = %s "
                        , $attachment_post_data->post_title, $attachment_post_data->post_type
                    );
		$post_exist = $wpdb->get_row($post_exist_query);
        $attachment_post_id = $post_exist->ID;
        if( ! $post_exist ) {
            $attachment_post_args = [
                'post_content'    =>   $attachment_post_data->post_content,
                'post_title'      =>   $attachment_post_data->post_title,
                'post_name'       =>   $attachment_post_data->post_name,
                'guid'            =>   $file_path,
                'post_type'       =>   $attachment_post_data->post_type,
                'post_mime_type'  =>   "image/png"
            ];
    
            $attachment_post_inserted = wp_insert_post($attachment_post_args);
    
            if (! $attachment_post_inserted ) {
                return wp_send_json_error(['message' => 'Error: Updating attachment post!!' . $file_path]);
                // wp_send_json_error('Error: Updating attachment post!!' . $attachment_post_inserted );
            }
            $attachment_post_id = $attachment_post_inserted;
        }

        // insertion of attachment metadata
        $attachment_metadata = json_decode(wp_remote_retrieve_body(wp_remote_get( DRIOP_TEMPLATE_BASE_API . "/attachment-meta/{$attachment->id}" ) ));
        foreach ( $attachment_metadata as $meta_key => $meta_value ) {
            update_post_meta( $attachment_post_id, $meta_key, maybe_unserialize( $meta_value[0] ) );
        }
    }

	/**
	 * Import driop page, feature image & meta data.
	 */
	public function import() {
		global $wpdb;
        $pluginurl = KIRKI_PLUGIN_URL;
        $WP_PLUGIN_DIR = WP_PLUGIN_DIR;
        $page_id       =  (int) Helpers::sanitize( $_POST['page_id'] );
        $template_id   =  (int) Helpers::sanitize( $_POST['template_id'] );

        $droip_page    = DRIOP_TEMPLATE_BASE_API . '/droip-templates/' . $template_id;
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
            }
        }

        // insertion of attachment metadata
        $template_metadata = json_decode( wp_remote_retrieve_body( wp_remote_get( DRIOP_TEMPLATE_BASE_API . "/post-meta/{$template_id}" ) ) );
        foreach ( $template_metadata as $key => $value ) {
            update_post_meta( $page_id, $value->meta_key, maybe_unserialize( $value->meta_value ) );
        }

        // $droip_template_meta_updated     =  update_post_meta( $page_id, 'droip', maybe_unserialize($template_droip_value) );

        // $droip_editor_mode_meta_updated  =  update_post_meta( $page_id, 'droip_editor_mode','droip' );

        $view_page = get_permalink( $page_id );

		return wp_send_json_success([
            'message'        =>   'Template download completed',
            'pageDetails'    =>   [
                'pageUrl'    =>   $view_page,
                'pageId'     =>   $page_id,
            ]
        ]);
	}
}
