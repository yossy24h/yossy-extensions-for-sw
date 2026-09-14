<?php
/**
 * カスタムHTMLブロック内の iframe を、任意の縦横比で幅100%にする。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_HTML_RESP_ATTR        = 'yefswHtmlResponsive';
const YEFSW_HTML_ASPECT_W_ATTR    = 'yefswHtmlAspectW';
const YEFSW_HTML_ASPECT_H_ATTR    = 'yefswHtmlAspectH';
const YEFSW_HTML_ASPECT_SP_ATTR   = 'yefswHtmlAspectSp';
const YEFSW_HTML_ASPECT_W_SP_ATTR = 'yefswHtmlAspectWSp';
const YEFSW_HTML_ASPECT_H_SP_ATTR = 'yefswHtmlAspectHSp';
const YEFSW_HTML_BLOCK            = 'core/html';
const YEFSW_HTML_HANDLE           = 'yefsw-html-iframe-responsive';

/**
 * 親テーマが SWELL かどうか。
 *
 * @return bool
 */
function yefsw_html_responsive_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * 縦横比の数値を 1 以上に正規化する。
 *
 * @param mixed $value Input.
 * @param int   $default Default.
 * @return int
 */
function yefsw_html_responsive_sanitize_ratio( $value, $default ) {
	$value = is_numeric( $value ) ? (int) $value : (int) $default;
	if ( $value < 1 ) {
		return 0;
	}
	return min( 9999, $value );
}

/**
 * レスポンシブ指定が有効かどうか。
 *
 * @param array $block Parsed block.
 * @return bool
 */
function yefsw_html_responsive_is_active( $block ) {
	if ( empty( $block['attrs'][ YEFSW_HTML_RESP_ATTR ] ) ) {
		return false;
	}

	$width  = yefsw_html_responsive_sanitize_ratio(
		isset( $block['attrs'][ YEFSW_HTML_ASPECT_W_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_W_ATTR ] : 16,
		16
	);
	$height = yefsw_html_responsive_sanitize_ratio(
		isset( $block['attrs'][ YEFSW_HTML_ASPECT_H_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_H_ATTR ] : 9,
		9
	);

	return $width > 0 && $height > 0;
}

/**
 * スマホ縦持ち（599px以下）で別の縦横比を使うか。
 *
 * @param array $block Parsed block.
 * @return bool
 */
function yefsw_html_responsive_has_sp_ratio( $block ) {
	if ( ! yefsw_html_responsive_is_active( $block ) ) {
		return false;
	}

	if ( empty( $block['attrs'][ YEFSW_HTML_ASPECT_SP_ATTR ] ) ) {
		return false;
	}

	$width  = yefsw_html_responsive_sanitize_ratio(
		isset( $block['attrs'][ YEFSW_HTML_ASPECT_W_SP_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_W_SP_ATTR ] : 16,
		16
	);
	$height = yefsw_html_responsive_sanitize_ratio(
		isset( $block['attrs'][ YEFSW_HTML_ASPECT_H_SP_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_H_SP_ATTR ] : 9,
		9
	);

	return $width > 0 && $height > 0;
}

/**
 * ブロック属性を追加する。
 *
 * @param array  $args       Block type args.
 * @param string $block_type Block name.
 * @return array
 */
function yefsw_html_responsive_register_block_args( $args, $block_type ) {
	if ( YEFSW_HTML_BLOCK !== $block_type ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes'][ YEFSW_HTML_RESP_ATTR ] = array(
		'type'    => 'boolean',
		'default' => false,
	);

	$args['attributes'][ YEFSW_HTML_ASPECT_W_ATTR ] = array(
		'type'    => 'integer',
		'default' => 16,
	);

	$args['attributes'][ YEFSW_HTML_ASPECT_H_ATTR ] = array(
		'type'    => 'integer',
		'default' => 9,
	);

	$args['attributes'][ YEFSW_HTML_ASPECT_SP_ATTR ] = array(
		'type'    => 'boolean',
		'default' => false,
	);

	$args['attributes'][ YEFSW_HTML_ASPECT_W_SP_ATTR ] = array(
		'type'    => 'integer',
		'default' => 16,
	);

	$args['attributes'][ YEFSW_HTML_ASPECT_H_SP_ATTR ] = array(
		'type'    => 'integer',
		'default' => 9,
	);

	return $args;
}
add_filter( 'register_block_type_args', 'yefsw_html_responsive_register_block_args', 10, 2 );

/**
 * アセット登録。
 */
function yefsw_html_responsive_register_assets() {
	if ( ! yefsw_html_responsive_is_swell() ) {
		return;
	}

	wp_register_style(
		YEFSW_HTML_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/html-iframe-responsive/front.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_style(
		YEFSW_HTML_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/html-iframe-responsive/editor.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_script(
		YEFSW_HTML_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/html-iframe-responsive/editor.js',
		array(
			'wp-blocks',
			'wp-hooks',
			'wp-compose',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-i18n',
		),
		YEFSW_VERSION,
		true
	);
}
add_action( 'init', 'yefsw_html_responsive_register_assets', 20 );

/**
 * ブロックエディタ用アセット。
 */
function yefsw_html_responsive_enqueue_editor_assets() {
	if ( ! yefsw_html_responsive_is_swell() ) {
		return;
	}

	wp_enqueue_style( YEFSW_HTML_HANDLE . '-editor' );
	wp_enqueue_script( YEFSW_HTML_HANDLE . '-editor' );
}
add_action( 'enqueue_block_editor_assets', 'yefsw_html_responsive_enqueue_editor_assets', 30 );

/**
 * 編集キャンバス用。公開側は render_block で必要なときだけ読む。
 */
function yefsw_html_responsive_enqueue_canvas_assets() {
	if ( ! yefsw_html_responsive_is_swell() || ! is_admin() ) {
		return;
	}

	wp_enqueue_style( YEFSW_HTML_HANDLE );
}
add_action( 'enqueue_block_assets', 'yefsw_html_responsive_enqueue_canvas_assets', 30 );

/**
 * 公開側: 有効ならラッパーと CSS 変数を付ける。
 *
 * @param string $block_content Rendered HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function yefsw_html_responsive_render_block( $block_content, $block ) {
	if ( '' === $block_content || ! is_string( $block_content ) ) {
		return $block_content;
	}

	if ( ! yefsw_html_responsive_is_active( $block ) ) {
		return $block_content;
	}

	$width  = yefsw_html_responsive_sanitize_ratio(
		isset( $block['attrs'][ YEFSW_HTML_ASPECT_W_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_W_ATTR ] : 16,
		16
	);
	$height = yefsw_html_responsive_sanitize_ratio(
		isset( $block['attrs'][ YEFSW_HTML_ASPECT_H_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_H_ATTR ] : 9,
		9
	);

	$style = '--yefsw-aspect-w:' . (int) $width . ';--yefsw-aspect-h:' . (int) $height . ';';
	$class = 'yefsw-html-responsive';

	if ( yefsw_html_responsive_has_sp_ratio( $block ) ) {
		$sp_width  = yefsw_html_responsive_sanitize_ratio(
			isset( $block['attrs'][ YEFSW_HTML_ASPECT_W_SP_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_W_SP_ATTR ] : 16,
			16
		);
		$sp_height = yefsw_html_responsive_sanitize_ratio(
			isset( $block['attrs'][ YEFSW_HTML_ASPECT_H_SP_ATTR ] ) ? $block['attrs'][ YEFSW_HTML_ASPECT_H_SP_ATTR ] : 9,
			9
		);
		$style    .= '--yefsw-aspect-w-sp:' . (int) $sp_width . ';--yefsw-aspect-h-sp:' . (int) $sp_height . ';';
		$class    .= ' has-yefsw-html-aspect-sp';
	}

	wp_enqueue_style( YEFSW_HTML_HANDLE );

	return '<div class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">' . $block_content . '</div>';
}
add_filter( 'render_block_core/html', 'yefsw_html_responsive_render_block', 20, 2 );
