<?php
/**
 * SWELL フルワイドブロックに任意のコンテンツ横幅（px）を追加する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_FW_ATTR = 'yefswContentWidth';
const YEFSW_FW_BLOCK = 'loos/full-wide';
const YEFSW_FW_HANDLE = 'yefsw-full-wide-custom-width';

/**
 * 親テーマが SWELL かどうか。
 */
function yefsw_fw_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * ブロック属性を追加する。
 *
 * @param array  $args       Block type args.
 * @param string $block_type Block name.
 * @return array
 */
function yefsw_fw_register_block_args( $args, $block_type ) {
	if ( YEFSW_FW_BLOCK !== $block_type ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes'][ YEFSW_FW_ATTR ] = array(
		'type'    => 'integer',
		'default' => 0,
	);

	return $args;
}
add_filter( 'register_block_type_args', 'yefsw_fw_register_block_args', 10, 2 );

/**
 * アセット登録。
 */
function yefsw_fw_register_assets() {
	if ( ! yefsw_fw_is_swell() ) {
		return;
	}

	wp_register_style(
		YEFSW_FW_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/full-wide-custom-width/front.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_style(
		YEFSW_FW_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/full-wide-custom-width/editor.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_script(
		YEFSW_FW_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/full-wide-custom-width/editor.js',
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
add_action( 'init', 'yefsw_fw_register_assets', 20 );

/**
 * ブロックエディタ用アセット。
 */
function yefsw_fw_enqueue_editor_assets() {
	if ( ! yefsw_fw_is_swell() ) {
		return;
	}

	wp_enqueue_style( YEFSW_FW_HANDLE . '-editor' );
	wp_enqueue_script( YEFSW_FW_HANDLE . '-editor' );
}
add_action( 'enqueue_block_editor_assets', 'yefsw_fw_enqueue_editor_assets', 30 );

/**
 * 公開側: カスタム幅が指定されていればクラスと CSS 変数を付与する。
 *
 * @param string $block_content Rendered HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function yefsw_fw_render_block( $block_content, $block ) {
	if ( '' === $block_content || ! is_string( $block_content ) ) {
		return $block_content;
	}

	$width = isset( $block['attrs'][ YEFSW_FW_ATTR ] ) ? (int) $block['attrs'][ YEFSW_FW_ATTR ] : 0;
	if ( $width <= 0 ) {
		return $block_content;
	}

	wp_enqueue_style( YEFSW_FW_HANDLE );

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( array( 'class_name' => 'swell-block-fullWide' ) ) ) {
		return $block_content;
	}

	$processor->add_class( 'has-yefsw-custom-width' );

	$style = (string) $processor->get_attribute( 'style' );
	$style = trim( $style );
	if ( $style && ';' !== substr( $style, -1 ) ) {
		$style .= ';';
	}
	$style .= '--yefsw-fw-content-width:' . $width . 'px;';
	$processor->set_attribute( 'style', $style );

	return $processor->get_updated_html();
}
add_filter( 'render_block_loos/full-wide', 'yefsw_fw_render_block', 20, 2 );
