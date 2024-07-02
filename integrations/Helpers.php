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
 * Helpers class
 */
class Helpers {

    /**
     * Sanitize fields
     * 
     * @since integrations
     * 
     * @param mixed $data string or array
     *
     * @return mixed return input data after sanitize
     */
    public static function sanitize ( $data ) {
        if( is_array( $data ) ) {
            $data = array_map(
                function ( $value ) {
                    return sanitize_text_field( $value );
                },
                $data
            );
        } else {
            $data = sanitize_text_field( $data );
        }
        return $data;
    }

}