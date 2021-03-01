<?php

/**
 * Plugin Name:	evangelizo
 * Text Domain:	evangelizo
 * Domain Path:	/languages
 * Description:	Display today readings from the Evangelizo project
 * Author:		Marek Sierociński
 * Version:		0.1.0
 * License:		GPLv3+
 * Last change:	2021-02-28
 */

if ( !function_exists( 'add_action' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

include 'EvangelizoArticle.php';

if ( !defined( 'TIME_S_DAY' ) ) {
    define( 'TIME_S_DAY', 86400 );
}

define( 'EVANGELIZO_NAME', 'evangelizo' );
define( 'EVANGELIZO_FOLDER', plugin_basename( __DIR__ ) );
define( 'EVANGELIZO_PATH', plugin_dir_path( __DIR__ ) . EVANGELIZO_FOLDER );
define( 'EVANGELIZO_TEXTDOMAIN', EVANGELIZO_NAME );
define( 'EVANGELIZO_STYLE', EVANGELIZO_NAME . '-style' );
define( 'EVANGELIZO_CACHE_FILE', EVANGELIZO_PATH . '/cached_#.html' );

define( 'EVANGELIZO_SUPPORTED_LANGUAGES', array(
    'AR', # العربية
    'DE', # Deutsch
    'GR', # Ελληνικά
    'AM', # English
    'SP', # Español
    'FR', # Français
    'GA', # Gaeilge
    'ARM', # հայերեն
    'IT', # Italiano
    'KR', # 한국어
    'MG', # Malagasy
    'NL', # Nederlands
    'PL', # Polski
    'CN', # 中文
    'PT', # Português
    'RU', # русский
    'TR' # Türkçe
        )
);

define( 'EVANGELIZO_READINGS_NAMES', array(
    'First Reading',
    'Second Reading',
    'Third Reading',
    'Forth Reading',
    'Fifth Reading',
    'Sixth Reading',
    'Seventh Reading',
    'Eight Reading'
        )
);

/**
 * Textdomain
 */
function evangelizo_textdomain () {
    if ( function_exists( 'load_plugin_textdomain' ) ) {
        load_plugin_textdomain( EVANGELIZO_TEXTDOMAIN, false, EVANGELIZO_FOLDER . '/languages' );
    }
}

/**
 * Fetch JSON data from evangelizo.ws Varnish server
 */
function evangelizo_fetch ( $language, $date ) {
    $ch = curl_init( "https://publication.evangelizo.ws/$language/days/$date" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'User-Agent: Mozilla/5.0 (X11; Linux x86_64)',
        'Accept: application/json',
        'Sec-Fetch-Site: cross-site',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Dest: empty'
    ) );
    $result = curl_exec( $ch );
    curl_close( $ch );
    return json_decode( $result, true )['data'];
}

/**
 * Get Liturgy
 */
function evangelizo_get_liturgy ( $data ) {
    $ret = '';

    # Liturgy Title
    if ( !empty( $data['liturgy']['title'] ) ) {
        $ret .= '<p>' . $data['liturgy']['title'] . '<p>' . "\n";
    }

    # Liturgy Description
    if ( !empty( $data['liturgy']['description'] ) ) {
        $ret .= '<p>' . $data['liturgy']['description'] . '<p>' . "\n";
    }

    return $ret;
}

/**
 * Get Readings
 */
function evangelizo_get_readings ( $data ) {
    $ret = '';

    $readings_n = 0;
    $psalms_n = 0;

    foreach ( $data['readings'] as $reading ) {

        $article = new EvangelizoArticle( $reading['type'] );

        # Reading name
        $reading_name = '';
        switch ( $reading['type'] ) {
            case 'reading' :
                $reading_name = __( EVANGELIZO_READINGS_NAMES[$readings_n++], EVANGELIZO_TEXTDOMAIN );
                break;

            case 'psalm' :
                $reading_name = __( 'Psalm', EVANGELIZO_TEXTDOMAIN );
                if ( ++$psalms_n > 1 ) {
                    $reading_name = $psalms_n . ' ' . $reading_name;
                }
                break;

            case 'gospel' :
                $reading_name = __( 'Gospel', EVANGELIZO_TEXTDOMAIN );
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
 */
function evangelizo_get_commentary ( $data ) {
    $commentary = $data['commentary'];

    $article = new EvangelizoArticle( 'commentary' );
    $article->set_name( __( 'Commentary', EVANGELIZO_TEXTDOMAIN ) );
    $article->set_title( $commentary['title'] );
    $article->set_text( $commentary['description'] );
    $article->set_source( $commentary['source'] );
    $article->set_author( $commentary['author']['name'] );

    return (string) $article;
}

/**
 * Get Copyright
 */
function evangelizo_get_copyright ( $language ) {
    $ret = __( 'The above content was provided by the project #"Evangelizo - Everyday Gospel"#.#The website owner does not claim any rights to this content.##Support the project financially.#', EVANGELIZO_TEXTDOMAIN );
    $ret = preg_replace( '/#/', '<a href="http://evangelizo.org" target="_blank">', $ret, 1 );
    $ret = preg_replace( '/#/', '</a>', $ret, 1 );
    $ret = preg_replace( '/#/', '<br>', $ret, 2 );
    $ret = preg_replace( '/#/', '<a href="https://ewangelia.org/' . $language . '/donation" target="_blank">', $ret, 1 );
    $ret = preg_replace( '/#/', '</a>', $ret, 1 );
    return '<p>' . $ret . '</p>';
}

/**
 * Create page
 */
function evangelizo_create_page ( $language ) {
    $data = evangelizo_fetch( $language, date( 'Y-m-d' ) );

    # Date
    $ret = '<h2>' . $data['date_displayed'] . '</h2>' . "\n";

    # Liturgy
    $ret .= evangelizo_get_liturgy( $data );

    # Readings
    $ret .= evangelizo_get_readings( $data );

    # Commentary
    $ret .= evangelizo_get_commentary( $data );

    # Copyright
    $ret .= evangelizo_get_copyright( $language );

    return '<section class="evangelizo">' . $ret . '</section>';
}

/**
 * Shortcode
 */
function evangelizo_shortcode ( $atts ) {
    $args = shortcode_atts(
            [
                'language' => 'PL'
            ],
            $atts
    );

    if ( !in_array( $args['language'], EVANGELIZO_SUPPORTED_LANGUAGES ) ) {
        return '<!-- EVANGELIZO ERROR: Unsupported language -->';
    }

    $cache_file = str_replace( '#', $args['language'], EVANGELIZO_CACHE_FILE );

    if ( !file_exists( $cache_file ) ) {
        touch( $cache_file, 0 );
    }

    $current_days = floor( time() / TIME_S_DAY );
    $cache_days = floor( filemtime( $cache_file ) / TIME_S_DAY );

    $ret = '';
    if ( $current_days > $cache_days ) {
        # Create new page and cache
        $ret = evangelizo_create_page( $args['language'] );
        file_put_contents( $cache_file, $ret );
    } else {
        # Use cache
        $ret = file_get_contents( $cache_file );
    }

    return $ret;
}

/**
 * Stylesheet
 */
function evangelizo_stylesheet () {
    wp_register_style( EVANGELIZO_STYLE, plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style( EVANGELIZO_STYLE );
}

# Init
if ( function_exists( 'add_shortcode' ) ) {
    add_shortcode( 'evangelizo', 'evangelizo_shortcode' );
}

add_action( 'wp_enqueue_scripts', 'evangelizo_stylesheet' );
add_action( 'init', 'evangelizo_textdomain' );
