<?php
/**
 * テーブルブロックに、枠線なしのスタイルを追加する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_TABLE_NO_BORDER_HANDLE = 'yefsw-table-no-border';
const YEFSW_TABLE_NO_BORDER_STYLE  = 'yefsw-no-border';

/**
 * 親テーマが SWELL かどうか。
 *
 * @return bool
 */
function yefsw_table_no_border_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * CSS を登録する。
 */
function yefsw_table_no_border_register_assets() {
	if ( ! yefsw_table_no_border_is_swell() ) {
		return;
	}

	$css_file = YEFSW_PLUGIN_DIR . 'assets/table-no-border/front.css';

	wp_register_style(
		YEFSW_TABLE_NO_BORDER_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/table-no-border/front.css',
		array(),
		file_exists( $css_file ) ? (string) filemtime( $css_file ) : YEFSW_VERSION
	);
}
add_action( 'init', 'yefsw_table_no_border_register_assets', 20 );

/**
 * テーブルブロックのスタイルを追加する。
 */
function yefsw_table_no_border_register_style() {
	if ( ! yefsw_table_no_border_is_swell() || ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style(
		'core/table',
		array(
			'name'         => YEFSW_TABLE_NO_BORDER_STYLE,
			'label'        => '[Y]線なし',
			'style_handle' => YEFSW_TABLE_NO_BORDER_HANDLE,
		)
	);
}
add_action( 'init', 'yefsw_table_no_border_register_style', 21 );

/**
 * 公開側。SWELL のテーブル用 CSS のあとに読む。
 */
function yefsw_table_no_border_enqueue_front() {
	if ( ! yefsw_table_no_border_is_swell() ) {
		return;
	}

	wp_enqueue_style( YEFSW_TABLE_NO_BORDER_HANDLE );
}
add_action( 'wp_enqueue_scripts', 'yefsw_table_no_border_enqueue_front', 30 );

/**
 * ブロックエディターの編集キャンバスにも同じ見た目を出す。
 */
function yefsw_table_no_border_enqueue_editor() {
	if ( ! is_admin() || ! yefsw_table_no_border_is_swell() ) {
		return;
	}

	wp_enqueue_style( YEFSW_TABLE_NO_BORDER_HANDLE );
}
add_action( 'enqueue_block_assets', 'yefsw_table_no_border_enqueue_editor', 30 );
