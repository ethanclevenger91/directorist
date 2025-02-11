<?php
/**
 * Directorist Url Field class.
 *
 */
namespace Directorist\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Url_Field extends Base_Field {

	public $type = 'url';

	public function validate( $posted_data ) {
		$value = $this->get_value( $posted_data );

		if ( ! wp_http_validate_url( $value ) ) {
			$this->add_error( __( 'Invalid URL.', 'directorist' ) );

			return false;
		}

		return true;
	}

	public function sanitize( $posted_data ) {
		return sanitize_url( $this->get_value( $posted_data ) );
	}
}

Fields::register( new Url_Field() );
