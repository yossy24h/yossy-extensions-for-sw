<?php
/**
 * 画像ブロックに、スマホ縦持ちだけの最大幅（px）を追加する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_IMAGE_SP_ATTR  = 'yefswSpMaxWidth';
const YEFSW_IMAGE_BLOCK    = 'core/image';
const YEFSW_IMAGE_HANDLE   = 'yefsw-image-sp-max-width';
const YEFSW_IMAGE_SP_MAX   = 9999;

/**
 * 親テーマが SWELL かどうか。
 *
 * @return bool
 */
function yefsw_image_sp_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * 最大幅を 0〜9999 の整数に正規化する。
 *
 * @param mixed $value Input value.
 * @return int
 */
function yefsw_image_sp_sanitize_width( $value ) {
	$value = is_numeric( $value ) ? (int) $value : 0;
	if ( $value <= 0 ) {
		return 0;
	}

	return min( YEFSW_IMAGE_SP_MAX, $value );
}

/**
 * ブロック属性を追加する。
 *
 * @param array  $args       Block type args.
 * @param string $block_type Block name.
 * @return array
 */
function yefsw_image_sp_register_block_args( $args, $block_type ) {
	if ( YEFSW_IMAGE_BLOCK !== $block_type ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes'][ YEFSW_IMAGE_SP_ATTR ] = array(
		'type'    => 'integer',
		'default' => 0,
	);

	return $args;
}
add_filter( 'register_block_type_args', 'yefsw_image_sp_register_block_args', 10, 2 );

/**
 * アセット登録。
 */
function yefsw_image_sp_register_assets() {
	if ( ! yefsw_image_sp_is_swell() ) {
		return;
	}

	$editor_css = YEFSW_PLUGIN_DIR . 'assets/image-sp-max-width/editor.css';
	$editor_js  = YEFSW_PLUGIN_DIR . 'assets/image-sp-max-width/editor.js';
	$editor_ver = file_exists( $editor_js ) ? (string) filemtime( $editor_js ) : YEFSW_VERSION;

	wp_register_style(
		YEFSW_IMAGE_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/image-sp-max-width/front.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_style(
		YEFSW_IMAGE_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/image-sp-max-width/editor.css',
		array(),
		file_exists( $editor_css ) ? (string) filemtime( $editor_css ) : YEFSW_VERSION
	);

	wp_register_script(
		YEFSW_IMAGE_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/image-sp-max-width/editor.js',
		array(
			'wp-blocks',
			'wp-hooks',
			'wp-compose',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-i18n',
		),
		$editor_ver,
		true
	);
}
add_action( 'init', 'yefsw_image_sp_register_assets', 20 );

/**
 * ブロックエディタ用アセット。
 */
function yefsw_image_sp_enqueue_editor_assets() {
	if ( ! yefsw_image_sp_is_swell() ) {
		return;
	}

	wp_enqueue_style( YEFSW_IMAGE_HANDLE . '-editor' );
	wp_enqueue_script( YEFSW_IMAGE_HANDLE . '-editor' );
}
add_action( 'enqueue_block_editor_assets', 'yefsw_image_sp_enqueue_editor_assets', 30 );

/**
 * 編集キャンバス（iframe）用。公開側は render_block で必要なときだけ読む。
 */
function yefsw_image_sp_enqueue_canvas_assets() {
	if ( ! yefsw_image_sp_is_swell() || ! is_admin() ) {
		return;
	}

	wp_enqueue_style( YEFSW_IMAGE_HANDLE );
}
add_action( 'enqueue_block_assets', 'yefsw_image_sp_enqueue_canvas_assets', 30 );

/**
 * 公開側: SP 最大幅が指定されていればクラスと CSS 変数を付与する。
 *
 * @param string $block_content Rendered HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function yefsw_image_sp_render_block( $block_content, $block ) {
	if ( '' === $block_content || ! is_string( $block_content ) ) {
		return $block_content;
	}

	$width = isset( $block['attrs'][ YEFSW_IMAGE_SP_ATTR ] ) ? yefsw_image_sp_sanitize_width( $block['attrs'][ YEFSW_IMAGE_SP_ATTR ] ) : 0;
	if ( $width <= 0 ) {
		return $block_content;
	}

	wp_enqueue_style( YEFSW_IMAGE_HANDLE );

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( array( 'class_name' => 'wp-block-image' ) ) ) {
		return $block_content;
	}

	$processor->add_class( 'has-yefsw-sp-max-width' );

	$style = (string) $processor->get_attribute( 'style' );
	$style = trim( $style );
	if ( $style && ';' !== substr( $style, -1 ) ) {
		$style .= ';';
	}
	$style .= '--yefsw-img-sp-max-width:' . $width . 'px;';
	$processor->set_attribute( 'style', $style );

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/image', 'yefsw_image_sp_render_block', 20, 2 );
