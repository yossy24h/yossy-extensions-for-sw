<?php
/**
 * PCヘッダーバーに、メニュー位置で割り当てたリンクを出す。
 * SWELL に差し込みフックが無いため、JS で挿入する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_PC_HEAD_BAR_MENU_LOCATION = 'yefsw_pc_head_bar';
const YEFSW_PC_HEAD_BAR_MENU_HANDLE   = 'yefsw-pc-head-bar-menu';

/**
 * 親テーマが SWELL かどうか。
 *
 * @return bool
 */
function yefsw_pc_head_bar_menu_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * メニュー位置を登録する。
 */
function yefsw_pc_head_bar_menu_register_location() {
	if ( ! yefsw_pc_head_bar_menu_is_swell() ) {
		return;
	}

	register_nav_menu( YEFSW_PC_HEAD_BAR_MENU_LOCATION, '[Y]PCヘッダーバー' );
}
add_action( 'after_setup_theme', 'yefsw_pc_head_bar_menu_register_location', 20 );

/**
 * 表示する条件。
 *
 * @return bool
 */
function yefsw_pc_head_bar_menu_should_render() {
	if ( ! yefsw_pc_head_bar_menu_is_swell() ) {
		return false;
	}

	return has_nav_menu( YEFSW_PC_HEAD_BAR_MENU_LOCATION );
}

/**
 * 差し込み用のメニュー HTML。
 *
 * @return string
 */
function yefsw_get_pc_head_bar_menu_html() {
	$menu = wp_nav_menu(
		array(
			'theme_location' => YEFSW_PC_HEAD_BAR_MENU_LOCATION,
			'container'      => false,
			'menu_class'     => 'yefsw-pc-head-bar-menu',
			'depth'          => 1,
			'fallback_cb'    => false,
			'echo'           => false,
		)
	);

	if ( ! is_string( $menu ) || '' === trim( $menu ) ) {
		return '';
	}

	return '<nav class="yefsw-pc-head-bar-nav" aria-label="' . esc_attr( 'PCヘッダーバー' ) . '">' . $menu . '</nav>';
}

/**
 * バーを自作するとき用の色。
 *
 * @return array{bg:string,color:string}
 */
function yefsw_get_pc_head_bar_menu_colors() {
	$bg    = '';
	$color = '';

	if ( class_exists( 'SWELL_Theme' ) ) {
		$bg    = (string) SWELL_Theme::get_setting( 'color_head_bar_bg' );
		$color = (string) SWELL_Theme::get_setting( 'color_head_bar_text' );
	}

	$bg    = sanitize_hex_color( $bg );
	$color = sanitize_hex_color( $color );

	return array(
		'bg'    => $bg ? $bg : 'var(--color_main)',
		'color' => $color ? $color : '#fff',
	);
}

/**
 * フロント用 CSS / JS。
 */
function yefsw_enqueue_pc_head_bar_menu_assets() {
	if ( ! yefsw_pc_head_bar_menu_should_render() ) {
		return;
	}

	$html = yefsw_get_pc_head_bar_menu_html();
	if ( '' === $html ) {
		return;
	}

	$colors = yefsw_get_pc_head_bar_menu_colors();

	$css_file = YEFSW_PLUGIN_DIR . 'assets/pc-head-bar-menu/front.css';
	$css_ver  = file_exists( $css_file ) ? (string) filemtime( $css_file ) : YEFSW_VERSION;

	wp_enqueue_style(
		YEFSW_PC_HEAD_BAR_MENU_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/pc-head-bar-menu/front.css',
		array(),
		$css_ver
	);

	wp_enqueue_script(
		YEFSW_PC_HEAD_BAR_MENU_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/pc-head-bar-menu/front.js',
		array(),
		YEFSW_VERSION,
		true
	);

	wp_localize_script(
		YEFSW_PC_HEAD_BAR_MENU_HANDLE,
		'yefswPcHeadBarMenu',
		array(
			'html'  => $html,
			'bg'    => $colors['bg'],
			'color' => $colors['color'],
		)
	);
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_pc_head_bar_menu_assets', 20 );
