<?php

/**
 * Plugin Name: Activate Plugins on Local
 * Plugin URI: https://wptop.net/
 * Description: Activate Plugins on Local for development and testing purpose.
 * Version: 1.0.0
 * Author: HungNth
 * Author URI: https://wptop.net/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( is_plugin_active( 'seo-by-rank-math-pro/rank-math-pro.php' ) ) {
	add_filter( 'rank_math/admin/sensitive_data_encryption', '__return_false' );
	
	update_option( 'rank_math_connect_data', [
		'username'  => 'user420',
		'email'     => 'user420@gmail.com',
		'api_key'   => '*********',
		'plan'      => 'business',
		'connected' => true,
	] );
	update_option( 'rank_math_registration_skip', 1 );
	
	add_action( 'init', function () {
		add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) {
			if ( strpos( $url, 'https://rankmath.com/wp-json/rankmath/v1/' ) !== false ) {
				$basename = basename( parse_url( $url, PHP_URL_PATH ) );
				if ( $basename == 'siteSettings' ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'ОК' ],
						'body'     => json_encode( [
							'error'     => '',
							'plan'      => 'business',
							'keywords'  => get_option( 'rank_math_keyword_quota',
								[ 'available' => 10000, 'taken' => 0 ] ),
							'analytics' => 'on',
						] ),
					];
				} elseif ( $basename == 'keywordsInfo' ) {
					if ( isset( $parsed_args['body']['count'] ) ) {
						return [
							'response' => [ 'code' => 200, 'message' => 'ОК' ],
							'body'     => json_encode( [
								'available' => 10000,
								'taken'     => $parsed_args['body']['count']
							] ),
						];
					}
				}
				
				return [ 'response' => [ 'code' => 200, 'message' => 'ОК' ] ];
			}
			
			return $pre;
		}, 10, 3 );
	} );
}