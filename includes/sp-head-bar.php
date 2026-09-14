<?php
/**
 * スマホ用ヘッダーバーをカスタマイザーから表示する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_SP_HEAD_BAR_OPTION = 'yefsw_show_sp_head_bar';
const YEFSW_SP_HEAD_BAR_HANDLE = 'yefsw-sp-head-bar';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_sp_head_bar_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * チェックボックス値を正規化する。
 *
 * @param mixed $value Input value.
 * @return bool
 */
function yefsw_sanitize_sp_head_bar( $value ) {
	return (bool) $value;
}

/**
 * 一般設定のキャッチフレーズだけを返す。
 *
 * @return string
 */
function yefsw_get_sp_head_bar_phrase() {
	return trim( (string) get_option( 'blogdescription', '' ) );
}

/**
 * スマホ用ヘッダーバーを出力する条件。
 *
 * @return bool
 */
function yefsw_sp_head_bar_should_render() {
	if ( ! yefsw_sp_head_bar_is_swell() ) {
		return false;
	}

	if ( ! yefsw_sanitize_sp_head_bar( get_option( YEFSW_SP_HEAD_BAR_OPTION, false ) ) ) {
		return false;
	}

	return '' !== yefsw_get_sp_head_bar_phrase();
}

/**
 * スマホ用ヘッダーバーのHTML。
 *
 * @return string
 */
function yefsw_get_sp_head_bar_html() {
	$phrase = yefsw_get_sp_head_bar_phrase();
	if ( '' === $phrase ) {
		return '';
	}

	return '<div class="l-header__bar yefsw-sp-head-bar"><div class="l-header__barInner l-container"><div class="yefsw-sp-head-bar__phrase">' . esc_html( $phrase ) . '</div></div></div>';
}

/**
 * カスタマイザーに設定を追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_sp_head_bar( $wp_customize ) {
	if ( ! yefsw_sp_head_bar_is_swell() || ! $wp_customize->get_section( 'swell_section_header' ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_SP_HEAD_BAR_OPTION,
		array(
			'default'           => false,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_head_bar',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_HEAD_BAR_OPTION,
		array(
			'label'   => '[Y]SP用ヘッダーバーを表示する',
			'section' => 'swell_section_header',
			'type'    => 'checkbox',
		)
	);

	$controls = $wp_customize->controls();
	$priority = 10;

	foreach ( $controls as $control ) {
		if ( 'swell_section_header' !== $control->section ) {
			continue;
		}

		if ( 0 === strpos( $control->id, 'yefsw_' ) ) {
			continue;
		}

		$control->priority = $priority;
		$priority         += 10;

		if ( 'loos_customizer[show_icon_list]' === $control->id ) {
			$sns = $wp_customize->get_control( 'yefsw_header_sns_icon_size' );
			if ( $sns ) {
				$sns->priority = $control->priority + 1;
			}
		}

		if ( 'loos_customizer[show_title]' === $control->id ) {
			$wp_customize->get_control( YEFSW_SP_HEAD_BAR_OPTION )->priority = $control->priority + 1;
		}
	}
}
add_action( 'customize_register', 'yefsw_register_sp_head_bar', 120 );

/**
 * フロント用 CSS / JS。
 * SWELL に差し込みフックが無いため、#header 先頭へ JS で挿入する。
 */
function yefsw_enqueue_sp_head_bar_assets() {
	if ( ! yefsw_sp_head_bar_should_render() ) {
		return;
	}

	$html = yefsw_get_sp_head_bar_html();
	if ( '' === $html ) {
		return;
	}

	$bg   = '';
	$text = '';
	if ( class_exists( 'SWELL_Theme' ) ) {
		$bg   = (string) SWELL_Theme::get_setting( 'color_head_bar_bg' );
		$text = (string) SWELL_Theme::get_setting( 'color_head_bar_text' );
	}

	$bg   = sanitize_hex_color( $bg );
	$text = sanitize_hex_color( $text );
	$bg   = $bg ? $bg : 'var(--color_main)';
	$text = $text ? $text : '#fff';

	wp_enqueue_style(
		YEFSW_SP_HEAD_BAR_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/sp-head-bar/front.css',
		array(),
		YEFSW_VERSION
	);

	wp_add_inline_style(
		YEFSW_SP_HEAD_BAR_HANDLE,
		'.yefsw-sp-head-bar{color:' . $text . ';background:' . $bg . ';}'
	);

	wp_enqueue_script(
		YEFSW_SP_HEAD_BAR_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/sp-head-bar/front.js',
		array(),
		YEFSW_VERSION,
		true
	);

	wp_localize_script(
		YEFSW_SP_HEAD_BAR_HANDLE,
		'yefswSpHeadBar',
		array(
			'html' => $html,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_sp_head_bar_assets', 20 );
