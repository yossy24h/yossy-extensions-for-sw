<?php
/**
 * Plugin Name:       YOSSY Extensions for SW
 * Description:       有料テーマ SWELL 専用の非公式拡張です。テーマの設定に加え、グループやカスタムHTMLも少し使いやすくします。
 * Version:           0.2.18
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            yossy
 * Text Domain:       yossy-extensions-for-sw
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YEFSW_VERSION', '0.2.18' );
define( 'YEFSW_PLUGIN_FILE', __FILE__ );
define( 'YEFSW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'YEFSW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bootstrap.
 */
function yefsw_bootstrap() {
	require_once YEFSW_PLUGIN_DIR . 'includes/full-wide-custom-width.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/group-max-width.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/html-iframe-responsive.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/header-sns-icon-size.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/pc-head-bar-menu.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/page-title-style.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/sp-head-bar.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/sp-menu-sns.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/sp-menu-align.php';
	require_once YEFSW_PLUGIN_DIR . 'includes/title-bg-nofilter-overlay.php';
}
add_action( 'plugins_loaded', 'yefsw_bootstrap' );
