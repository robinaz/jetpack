<?php

class VideoPress_Options {

	/** @var string */
	public static $option_name = 'videopress';

	/** @var array */
	public static $jetpack_plans_with_videopress = array( 'jetpack_premium', 'jetpack_business' );

	/** @var array */
	protected static $options = array();

	/**
	 * Get VideoPress options
	 */
	public static function get_options() {
		// Make sure we only get options from the database and services once per connection.
		if ( count( self::$options ) > 0 ) {
			return self::$options;
		}

		$defaults = array(
			'freedom'        => false,
			'hd'             => true,
			'meta'           => array(
				'max_upload_size' => 0,
			),
		);

		self::$options = Jetpack_Options::get_option( self::$option_name, array() );

		// If options have not been saved yet, check for older VideoPress plugin options.
		if ( empty( self::$options ) ) {
			self::$options['freedom'] = (bool) get_option( 'video_player_freedom', false );
			self::$options['hd']      = (bool) get_option( 'video_player_high_quality', false );
		}

		self::$options = array_merge( $defaults, self::$options );

		// Make sure that the shadow blog id never comes from the options, but instead uses the
		// associated shadow blog id, if videopress is enabled.
		self::$options['shadow_blog_id'] = 0;
		if ( self::isVideoPressIncludedInJetpackPlan() ) {
			self::$options['shadow_blog_id'] = Jetpack_Options::get_option( 'id' );
		}

		return self::$options;
	}

	/**
	 * Update VideoPress options
	 */
	public static function update_options( $options ) {
		Jetpack_Options::update_option( self::$option_name, $options );

		self::$options = $options;
	}

	/**
	 * Runs when the VideoPress module is deactivated.
	 */
	public static function delete_options() {
		Jetpack_Options::delete_option( self::$option_name );

		self::$options = array();
	}


	/**
	 * Does the site have a Jetpack plan attached to it that includes VideoPress
	 *
	 * @todo We might want to cache this.
	 * @return bool
	 */
	protected static function isVideoPressIncludedInJetpackPlan() {
		$site_id = Jetpack_Options::get_option( 'id' );
		$result  = Jetpack_Client::wpcom_json_api_request_as_blog( sprintf( '/sites/%d', $site_id ), '1.1' );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$response = json_decode( $result['body'], true );

		return in_array( $response['plan']['product_slug'], self::$jetpack_plans_with_videopress );
	}
}