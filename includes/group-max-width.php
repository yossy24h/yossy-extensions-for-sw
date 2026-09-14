<?php
/**
 * グループブロックに任意の最大横幅（px）と左右揃えを追加する。
 * 行・スタック・グリッド（flex / grid）では出さない。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_GROUP_ATTR       = 'yefswMaxWidth';
const YEFSW_GROUP_ALIGN_ATTR = 'yefswMaxWidthAlign';
const YEFSW_GROUP_BLOCK      = 'core/group';
const YEFSW_GROUP_HANDLE     = 'yefsw-group-max-width';

/**
 * 親テーマが SWELL かどうか。
 */
function yefsw_group_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * グループ（行・スタック・グリッド以外）かどうか。
 *
 * @param array $block Parsed block.
 * @return bool
 */
function yefsw_group_is_plain_group( $block ) {
	$type = isset( $block['attrs']['layout']['type'] ) ? (string) $block['attrs']['layout']['type'] : '';
	return 'flex' !== $type && 'grid' !== $type;
}

/**
 * ブロック属性を追加する。
 *
 * @param array  $args       Block type args.
 * @param string $block_type Block name.
 * @return array
 */
function yefsw_group_register_block_args( $args, $block_type ) {
	if ( YEFSW_GROUP_BLOCK !== $block_type ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes'][ YEFSW_GROUP_ATTR ] = array(
		'type'    => 'integer',
		'default' => 0,
	);

	$args['attributes'][ YEFSW_GROUP_ALIGN_ATTR ] = array(
		'type'    => 'string',
		'default' => 'center',
	);

	return $args;
}
add_filter( 'register_block_type_args', 'yefsw_group_register_block_args', 10, 2 );

/**
 * アセット登録。
 */
function yefsw_group_register_assets() {
	if ( ! yefsw_group_is_swell() ) {
		return;
	}

	wp_register_style(
		YEFSW_GROUP_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/group-max-width/front.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_style(
		YEFSW_GROUP_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/group-max-width/editor.css',
		array(),
		YEFSW_VERSION
	);

	wp_register_script(
		YEFSW_GROUP_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/group-max-width/editor.js',
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
add_action( 'init', 'yefsw_group_register_assets', 20 );

/**
 * ブロックエディタ用アセット。
 */
function yefsw_group_enqueue_editor_assets() {
	if ( ! yefsw_group_is_swell() ) {
		return;
	}

	wp_enqueue_style( YEFSW_GROUP_HANDLE . '-editor' );
	wp_enqueue_script( YEFSW_GROUP_HANDLE . '-editor' );
}
add_action( 'enqueue_block_editor_assets', 'yefsw_group_enqueue_editor_assets', 30 );

/**
 * 編集キャンバス（iframe）用。公開側は render_block で必要なときだけ読む。
 */
function yefsw_group_enqueue_canvas_assets() {
	if ( ! yefsw_group_is_swell() || ! is_admin() ) {
		return;
	}

	wp_enqueue_style( YEFSW_GROUP_HANDLE );
}
add_action( 'enqueue_block_assets', 'yefsw_group_enqueue_canvas_assets', 30 );

/**
 * 公開側: カスタム最大幅が指定されていればクラスと CSS 変数を付与する。
 *
 * @param string $block_content Rendered HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function yefsw_group_render_block( $block_content, $block ) {
	if ( '' === $block_content || ! is_string( $block_content ) ) {
		return $block_content;
	}

	if ( ! yefsw_group_is_plain_group( $block ) ) {
		return $block_content;
	}

	$width = isset( $block['attrs'][ YEFSW_GROUP_ATTR ] ) ? (int) $block['attrs'][ YEFSW_GROUP_ATTR ] : 0;
	if ( $width <= 0 ) {
		return $block_content;
	}

	wp_enqueue_style( YEFSW_GROUP_HANDLE );

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( array( 'class_name' => 'wp-block-group' ) ) ) {
		return $block_content;
	}

	$align = isset( $block['attrs'][ YEFSW_GROUP_ALIGN_ATTR ] ) ? (string) $block['attrs'][ YEFSW_GROUP_ALIGN_ATTR ] : 'center';
	if ( 'left' !== $align && 'right' !== $align ) {
		$align = 'center';
	}

	$processor->add_class( 'has-yefsw-max-width' );
	$processor->add_class( 'has-yefsw-align-' . $align );

	$style = (string) $processor->get_attribute( 'style' );
	$style = trim( $style );
	if ( $style && ';' !== substr( $style, -1 ) ) {
		$style .= ';';
	}
	$style .= '--yefsw-group-max-width:' . $width . 'px;';
	$processor->set_attribute( 'style', $style );

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/group', 'yefsw_group_render_block', 20, 2 );
