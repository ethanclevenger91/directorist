<?php

// it handles directorist upgrade
class ATBDP_Upgrade
{
	public $upgrade_notice_id       = 'migrate_to_7';

	public $legacy_notice_id        = 'directorist_legacy_template';

	public $directorist_notices     = [];

	public $directorist_migration   = [];

	public function __construct()
	{
		if ( !is_admin() ) return;

		add_action('admin_init', array($this, 'configure_notices'));

		add_action('admin_notices', array($this, 'upgrade_notice'), 100);

		add_action('directorist_before_settings_panel_header', array($this, 'promo_banner') );

		add_action('directorist_before_all_directory_types', array($this, 'promo_banner') );

		add_action('directorist_before_directory_type_edited', array($this, 'promo_banner') );

		add_action( 'admin_notices', array( $this, 'bfcm_notice') );
	}

	public function is_pro_user() {
		$plugin = get_user_meta( get_current_user_id(), '_plugins_available_in_subscriptions', true );
		$theme  = get_user_meta( get_current_user_id(), '_themes_available_in_subscriptions', true );

		if( $plugin || $theme ) {
			return true;
		} else {
			return false;
		}
	}

	public function promo_banner(){
		if( ! self::is_pro_user() ) {
			ATBDP()->load_template( 'admin-templates/admin-promo-banner' );
		}
	}

	public function bfcm_notice() {
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		if( self::is_pro_user() ) {
			return;
		}

		$response_body  = self::promo_remote_get();
		$display        = ! empty( $response_body->promo_2_display ) ? $response_body->promo_2_display : '';
		$text           = ! empty( $response_body->promo_2_text ) ? $response_body->promo_2_text : '';
		$version        = ! empty( $response_body->promo_2_version ) ? $response_body->promo_2_version : '';
		$link           = ! empty( $response_body->get_now_button_link ) ? self::promo_link( $response_body->get_now_button_link ) : '';

		$closed_version = get_user_meta( get_current_user_id(), 'directorist_promo2_closed_version', true );

		if ( !$display || $version == $closed_version || !$text ) {
			return;
		}

		$text = str_replace( '{{link}}', $link, $text );

		$dismiss_url = add_query_arg(
			array(
				'directorist_promo2_closed_version' => $version,
			),
			atbdp_get_current_url()
		);

		$notice = '<div class="notice notice-info is-dismissible"><p style="font-size: 16px;">' . $text . '</p><a href="'.esc_url( $dismiss_url ).'" class="notice-dismiss" style="text-decoration: none;"><span class="screen-reader-text">'. __( 'Dismiss this notice.', 'directorist' ) .'</span></a></div>';

		echo wp_kses_post( $notice );
	}

	public static function promo_remote_get() {
		$url     = 'https://app.directorist.com/wp-json/directorist/v1/get-promo';
		$headers = [
			'user-agent' => 'Directorist/' . md5( esc_url( home_url() ) ) . ';',
			'Accept'     => 'application/json',
		];

		$config = [
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'headers'     => $headers,
			'cookies'     => [],
		];

		$response_body = [];

		$cached_response = get_transient( 'directorist_get_promo_banner' );

		if( $cached_response ) {
			$response_body = $cached_response;
		} else {
			try {
				$response = wp_remote_get( $url, $config );
				$response_body = ! is_wp_error( $response ) ? wp_remote_retrieve_body( $response ) : [];
				set_transient( 'directorist_get_promo_banner', $response_body, 24 * HOUR_IN_SECONDS );
			} catch ( Exception $e ) {
				return $response_body;
			}
		}

		$response_body = is_string( $response_body ) ? json_decode( $response_body ) : $response_body;

		return $response_body;
	}

	public function upgrade_notice()
	{
		if (!current_user_can('administrator')) return;

		if( '7.0' !== ATBDP_VERSION ) return;

		if ( get_user_meta( get_current_user_id(), $this->upgrade_notice_id, true ) || ! empty( $this->directorist_migration[ $this->upgrade_notice_id ] ) ) return;

		$text = '';

		$link = 'https://directorist.com/blog/directorist-7-0-released/';
		$membership_page = admin_url('edit.php?post_type=at_biz_dir&page=atbdp-extension');

		$wp_rollback = 'https://wordpress.org/plugins/wp-rollback/';

		$text .= sprintf( __( '<p class="directorist__notice_new"><span>Congratulations!</span> You are now using the latest version of Directorist with some cool <a href="%s" target="blank">new features</a>. If you are using any of our premium theme or extension, please update them from this <a href="%s">page</a> </p>', 'directorist' ), $link, $membership_page );

		$text .= sprintf( __( '<p class="directorist__notice_new_action"><a href="%s" class="directorist__notice_new__btn">Continue using Directorist 7.0 </a><a target="blank" href="%s"> Roll back to v6.5.8</a></p>', 'directorist' ), add_query_arg( 'directorist-v7', 1 ), $wp_rollback );

		$notice = '<div class="notice notice-warning is-dismissible directorist-plugin-updater-notice" style="font-weight:bold;padding-top: 5px;padding-bottom: 5px;">' . $text . '</div>';

		echo wp_kses_post( $notice );

	}

	public function configure_notices(){

		$this->directorist_notices      = get_option( 'directorist_notices' );

		$this->directorist_migration    = get_option( 'directorist_migration' );

		if ( isset( $_GET['directorist-v7'] ) ) {
			$this->directorist_migration[ $this->upgrade_notice_id ] = 1;
			update_option( 'directorist_migration', $this->directorist_migration );
		}

		if ( isset( $_GET['directorist-depricated-notice'] ) ) {
			$this->directorist_notices[ $this->legacy_notice_id ] = 1;
			update_option( 'directorist_notices', $this->directorist_notices );

		}

		if ( isset( $_GET['close-directorist-promo-version'] ) ) {
			update_user_meta( get_current_user_id(), '_directorist_promo_closed', directorist_clean( wp_unslash( $_GET['close-directorist-promo-version'] ) ) );
		}

		if ( isset( $_GET['directorist_promo2_closed_version'] ) ) {
			update_user_meta( get_current_user_id(), 'directorist_promo2_closed_version', directorist_clean( wp_unslash( $_GET['directorist_promo2_closed_version'] ) ) );
		}

	}

	public static function promo_link( $link ) {
		if( defined( 'DIRECTORIST_AFFLILIATE_ID' ) && DIRECTORIST_AFFLILIATE_ID !== null ) {
			$link = $link . "ref/" . DIRECTORIST_AFFLILIATE_ID;
		}

		return $link;
	}

}