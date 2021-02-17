<?php
/*
Plugin Name: AJAX Endpoint
Plugin URI: 
Description: I built this WordPress plugin for demonstration purposes only. Basically, it pulls some dummy data remotely from the restapiexample.com and cached it. Allowing a WordPress based website (where this plugin is installed) to consume those information locally through RESTful access from an ajax request.
Version: 1.0
Author: Ton
Author URI: https://www.guru.com/freelancers/coderprovw/portfolio
Text Domain: ajaxendpoint
Requires at least: 5.0
Tested up to: 5.5
License: MIT

Copyright: 2020
*/

// This class should belong to this namespace to prevent any collision from other plugins.
namespace ajaxrestdemo;

if ( !defined( 'ABSPATH' ) ) die( 'Access denied.' );

define( 'AJAX_ENDPOINT_FILE', __FILE__ );
define( 'AJAX_ENDPOINT_DIR', __DIR__ );
define( 'AJAX_ENDPOINT_URL', plugins_url( '', AJAX_ENDPOINT_FILE ) );
define( 'AJAX_ENDPOINT_PLUGIN', plugin_basename( AJAX_ENDPOINT_FILE ) );
define( 'AJAX_ENDPOINT_TEMPLATE_DIR', AJAX_ENDPOINT_DIR . '/templates/' );

/**
 * AJAXEndpoint class
 *
 * Handles and renders all the necessary actions/features/behaviors of this plugin.
 *
 */
class AJAXEndpoint {
	// Current version of this plugin
	const VERSION = '1.0';

	// Minimum PHP version required to run this plugin
	const PHP_REQUIRED = '5.3';

	// Minimum WP version required to run this plugin
	const WP_REQUIRED = '5.0';

	// Dummy URL
	const DUMMY_ENDPOINT = 'https://dummy.restapiexample.com/api/v1/employees/';

	// Cache Key
	const CACHE_KEY = 'remote_dummy_data';

	// Static property of this class that will hold the singleton instance of this class
	protected static $instance = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		register_activation_hook( AJAX_ENDPOINT_FILE, array( $this , 'activate' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		add_shortcode( 'ajaxendpoint', array( $this, 'shortcode' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );
	}

	/**
	 * Registers our custom routes (AJAX endpoint) for public consumption
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route('ajaxendpoint/v1', '/dummy-data/', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_dummy_data' ),
			'permission_callback' => '__return_true'
			)
		);
	}

	/**
	 * Enqueues our ajaxendpoint css style for the frontend and admin content
	 *
	 * @return void
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'ajaxendpoint-css', AJAX_ENDPOINT_URL . '/css/ajaxendpoint.css', array(), '1.0' );
	}

	/**
	 * Creates an instance of this class. Singleton Pattern
	 *
	 * @return object Instance of this class
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds admin page for the AJAX Endpoint plugin under the "Tools" menu
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page( 'tools.php', 'AJAXEndpoint', 'AJAXEndpoint', 'manage_options', 'ajaxendpoint', array( $this, 'admin_page' ) );
	}

	/**
	 * Manages the cached data through fetching and pruning actions
	 *
	 * @return void
	 */
	public function admin_page() {
		$data = array();

		if ( isset( $_POST['fetch'] ) || isset( $_POST['prune'] ) ) {
			delete_site_transient( self::CACHE_KEY );

			if ( isset( $_POST['fetch'] ) ) {
				$remote_data = $this->get_remote_data();
				if ( ! isset( $remote_data['error'] ) ) {
					if ( ! empty($remote_data) && isset( $remote_data['status'] ) && 'success' == $remote_data['status'] ) {
						$data = $remote_data['data'];
					}
				} else {
					$data = $remote_data;
				}
			}
		} else {
			$cached_data = get_site_transient( self::CACHE_KEY );
			if ( ! empty($cached_data) && 'success' == $cached_data['status'] ) {
				$data = $cached_data['data'];
			}
		}

		include_once AJAX_ENDPOINT_TEMPLATE_DIR . 'admin/dashboard.php';
	}

	/**
	 * Runs PHP and WP version checks on activation
	 *
	 * @return void
	 */
	public function activate() {
		$data = get_plugin_data( AJAX_ENDPOINT_FILE );

		if ( version_compare( PHP_VERSION, self::PHP_REQUIRED, '<' ) ) {
			deactivate_plugins( AJAX_ENDPOINT_PLUGIN );

			wp_die( __( $data['Name'] . ' requires PHP version ' . self::PHP_REQUIRED . ' or greater.', 'ajaxendpoint' ) );
		}

		include ABSPATH . WPINC . '/version.php';
		if ( version_compare( $wp_version, self::WP_REQUIRED, '<' ) ) {
			deactivate_plugins( AJAX_ENDPOINT_PLUGIN );

			wp_die( __( $data['Name'] . ' requires WordPress version ' . self::WP_REQUIRED . ' or greater.', 'ajaxendpoint' ) );
		}
	}

	/**
	 * Loads and renders our "ajaxendpoint" shortcode
	 *
	 * @param array $atts An array of attributes attached to the shortcode
	 *
	 * @return string
	 */
	public function shortcode($atts) {
		// We're not going to render the shortcode within the admin area,
		// thus, we bail
		if (is_admin()) return '';

		wp_enqueue_script( 'ajaxendpoint-js', AJAX_ENDPOINT_URL . '/js/ajaxendpoint.js', array( 'jquery' ), '1.0' );
		$args = array(
			'rest_url' => rest_url(),
			'rest_nonce' => wp_create_nonce( 'wp_rest' )
		);

		wp_localize_script( 'ajaxendpoint-js', 'ajaxendpoint', $args);

		ob_start();
		include_once AJAX_ENDPOINT_TEMPLATE_DIR . 'frontend/main.php';
		return ob_get_clean();
	}

	/**
	 * Pulls the cached data from the remote (dummy) endpoint and loads it for consumption
	 * by an ajax request.
	 *
	 * @return void
	 */
	public function get_dummy_data() {
		$data = $this->get_remote_data();
		echo json_encode($data);
		die();
	}

	/**
	 * Fetches the remote data from the dummy endpoint and caches it for 12 hours as well
	 * as returning the data for consumption.
	 *
	 * @return array
	 */
	private function get_remote_data() {
		$data = get_site_transient( self::CACHE_KEY );
		if ( ! $data ) {
			$args = array( 
				'timeout' => 120,
				'headers' => array(
					'Content-Type' => 'application/json'
				)
			);

			$response = wp_remote_get( self::DUMMY_ENDPOINT, $args );
			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				if ( ! empty( $body ) ) {
					$data = @json_decode( $body, true );

					if ( json_last_error() === JSON_ERROR_NONE && ! empty( $data ) ) {
						set_site_transient( self::CACHE_KEY, $data, 12 * HOUR_IN_SECONDS );
					} else {
						$message = wp_remote_retrieve_response_message( $response );
						$message = ( ! empty( $message ) ) ? $message : json_last_error_msg();
						$data = array( 'error' => true, 'message' => $message );
					}
				}
			} else {
				$data = array( 'error' => true, 'message' => $response->get_error_message() );
			}
		}

		return $data;
	}
}


/**
 * Creates or make use of the singleton instance of AJAXEndpoint class
 *
 * @return object
 */
function AJAXEndpoint() {
	// We are on the same file or namespace so there's no need to use '\ajaxrestdemo\AJAXEndpoint' to call the instance method.
	return AJAXEndpoint::instance();
}

$GLOBALS[ 'ajaxendpoint' ] = AJAXEndpoint();