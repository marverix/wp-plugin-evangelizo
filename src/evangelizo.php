<?php
/**
 * Main plugin file
 *
 * @package           evangelizo
 * @author            Marek Sierociński
 * @copyright         2021 Marek Sierociński
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       evangelizo
 * Plugin URI:        https://github.com/marverix/wp-plugin-evangelizo
 * Description:       Display today readings from the Evangelizo.org project.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Marek Sierociński
 * Author URI:        https://github.com/marverix
 * Text Domain:       evangelizo
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0-standalone.html
 */

if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'EVANGELIZO_NAME', 'evangelizo' );
define( 'EVANGELIZO_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define( 'EVANGELIZO_STYLE', EVANGELIZO_NAME . '-style' );

define(
	'EVANGELIZO_SUPPORTED_LANGUAGES',
	array(
		'AR', // العربية.
		'DE', // Deutsch.
		'GR', // Ελληνικά.
		'AM', // English.
		'SP', // Español.
		'FR', // Français.
		'GA', // Gaeilge.
		'ARM', // հայերեն.
		'IT', // Italiano.
		'KR', // 한국어.
		'MG', // Malagasy.
		'NL', // Nederlands.
		'PL', // Polski.
		'CN', // 中文.
		'PT', // Português.
		'RU', // русский.
		'TR', // Türkçe.
	)
);

define(
	'EVANGELIZO_READINGS_NAMES',
	array(
		'First Reading',
		'Second Reading',
		'Third Reading',
		'Forth Reading',
		'Fifth Reading',
		'Sixth Reading',
		'Seventh Reading',
		'Eight Reading',
	)
);

require 'class-evangelizo-article.php';


/**
 * Textdomain
 */
function evangelizo_textdomain() {
	if ( function_exists( 'load_plugin_textdomain' ) ) {
		load_plugin_textdomain( EVANGELIZO_NAME, false, EVANGELIZO_FOLDER . '/languages' );
	}
}

/**
 * Fetch JSON data from evangelizo.ws Varnish server
 *
 * @param string $language Language code.
 * @param string $date Date in format Y-m-d.
 */
function evangelizo_fetch( $language, $date ) {
	$response = wp_remote_get(
		"https://publication.evangelizo.ws/$language/days/$date",
		array(
			'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
			'headers'    => array(
				'Accept: application/json',
				'Sec-Fetch-Site: cross-site',
				'Sec-Fetch-Mode: cors',
				'Sec-Fetch-Dest: empty',
			),
		)
	);

	if ( is_array( $response ) && ! is_wp_error( $response ) ) {
		return json_decode( $response['body'], true )['data'];
	}

	return null;
}

/**
 * Get Liturgy
 *
 * @param array $data JSON data retrieved from server.
 */
function evangelizo_get_liturgy( $data ) {
	$ret = '';

	// Liturgy Title.
	if ( ! empty( $data['liturgy']['title'] ) ) {
		$ret .= '<p>' . $data['liturgy']['title'] . '<p>' . "\n";
	}

	// Liturgy Description.
	if ( ! empty( $data['liturgy']['description'] ) ) {
		$ret .= '<p>' . $data['liturgy']['description'] . '<p>' . "\n";
	}

	return $ret;
}

/**
 * Get Readings
 *
 * @param array $data JSON data retrieved from server.
 */
function evangelizo_get_readings( $data ) {
	$ret = '';

	$readings_n = 0;
	$psalms_n   = 0;

	foreach ( $data['readings'] as $reading ) {

		$article = new Evangelizo_Article( $reading['type'] );

		// Reading name.
		$reading_name = '';
		switch ( $reading['type'] ) {
			case 'reading':
				$reading_name = __( EVANGELIZO_READINGS_NAMES[ $readings_n++ ], EVANGELIZO_NAME );
				break;

			case 'psalm':
				$reading_name = __( 'Psalm', EVANGELIZO_NAME );
				if ( ++$psalms_n > 1 ) {
					$reading_name = $psalms_n . ' ' . $reading_name;
				}
				break;

			case 'gospel':
				$reading_name = __( 'Gospel', EVANGELIZO_NAME );
				break;
		}

		$article->set_name( $reading_name );
		$article->set_title( $reading['title'] . ' ' . $reading['reference_displayed'] );
		$article->set_text( $reading['text'] );
		$article->set_source( $reading['source'] );

		$ret .= (string) $article;
	}

	return $ret;
}

/**
 * Get Commentary
 *
 * @param array $data JSON data retrieved from server.
 */
function evangelizo_get_commentary( $data ) {
	$commentary = $data['commentary'];

	$article = new Evangelizo_Article( 'commentary' );
	$article->set_name( __( 'Commentary', EVANGELIZO_NAME ) );
	$article->set_title( $commentary['title'] );
	$article->set_text( $commentary['description'] );
	$article->set_source( $commentary['source'] );
	$article->set_author( $commentary['author']['name'] );

	return (string) $article;
}

/**
 * Get Copyright
 *
 * @param string $language Language code.
 */
function evangelizo_get_copyright( $language ) {
	$ret = __( 'The above content was provided by the project #"Evangelizo - Everyday Gospel"#.#The website owner does not claim any rights to this content.##Support the project financially.#', EVANGELIZO_NAME );
	$ret = preg_replace( '/#/', '<a href="http://evangelizo.org" target="_blank">', $ret, 1 );
	$ret = preg_replace( '/#/', '</a>', $ret, 1 );
	$ret = preg_replace( '/#/', '<br>', $ret, 2 );
	$ret = preg_replace( '/#/', '<a href="https://ewangelia.org/' . $language . '/donation" target="_blank">', $ret, 1 );
	$ret = preg_replace( '/#/', '</a>', $ret, 1 );
	return '<p>' . $ret . '</p>';
}

/**
 * Create page
 *
 * @param string $language Language code.
 */
function evangelizo_create_page( $language ) {
	$data = evangelizo_fetch( $language, date( 'Y-m-d' ) );

	if ( empty( $data ) ) {
		return null;
	}

	// Date.
	$ret = '<h2>' . $data['date_displayed'] . '</h2>' . "\n";

	// Liturgy.
	$ret .= evangelizo_get_liturgy( $data );

	// Readings.
	$ret .= evangelizo_get_readings( $data );

	// Commentary.
	$ret .= evangelizo_get_commentary( $data );

	// Copyright.
	$ret .= evangelizo_get_copyright( $language );

	return '<section class="evangelizo">' . $ret . '</section>';
}

/**
 * Shortcode
 *
 * @param array $atts Array of properties passed to the shortcode.
 */
function evangelizo_shortcode( $atts ) {
	global $wp_filesystem;

	$args = shortcode_atts(
		array(
			'language' => 'PL',
			'force' => 'false'
		),
		$atts
	);

	if ( ! in_array( $args['language'], EVANGELIZO_SUPPORTED_LANGUAGES, true ) ) {
		return '<!-- EVANGELIZO ERROR: Unsupported language -->';
	}

	$transient = EVANGELIZO_NAME . '_' . $args['language'];
	$ret = get_site_transient( $transient );

	if ( empty( $ret ) || $args['force'] === 'true' ) {
		// Create new page and cache.
		$ret = evangelizo_create_page( $args['language'] );

		if ( empty( $ret ) ) {
			$ret = '<!-- EVANGELIZO ERROR: Coudln\'t fetch data -->';
		} else {
			$t = time();
			$d = 86400;
			$expire = ( ceil( $t / $d ) * $d ) - $t; // Expire in the end of the day.
			set_site_transient( $transient, $ret, $expire );
		}
	}

	return $ret;
}

/**
 * Stylesheet
 */
function evangelizo_stylesheet() {
	wp_register_style( EVANGELIZO_STYLE, plugins_url( 'style.css', __FILE__ ), array(), 1 );
	wp_enqueue_style( EVANGELIZO_STYLE );
}

// Init.
if ( function_exists( 'add_shortcode' ) ) {
	add_shortcode( 'evangelizo', 'evangelizo_shortcode' );
}

add_action( 'wp_enqueue_scripts', 'evangelizo_stylesheet' );
add_action( 'init', 'evangelizo_textdomain' );
