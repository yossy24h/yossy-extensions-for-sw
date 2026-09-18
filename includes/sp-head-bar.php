<?php
/**
 * スマホ用ヘッダーバーをカスタマイザーから表示する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_SP_HEAD_BAR_OPTION    = 'yefsw_show_sp_head_bar';
const YEFSW_SP_HEAD_BAR_FZ_OPTION = 'yefsw_sp_head_bar_fz';
const YEFSW_SP_HEAD_BAR_HANDLE    = 'yefsw-sp-head-bar';
const YEFSW_SP_HEAD_BAR_FZ_MIN    = 9;
const YEFSW_SP_HEAD_BAR_FZ_MAX    = 14;
const YEFSW_SP_HEAD_BAR_FZ_DEFAULT = 12;

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_sp_head_bar_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * チェックボックス値を 0 / 1 に正規化する。
 *
 * option 型設定のプレビューは pre_option フィルターを使う。
 * false は「保存済みの値を取得する」という予約値なので、オフは整数 0 を返す。
 *
 * @param mixed $value Input value.
 * @return int
 */
function yefsw_sanitize_sp_head_bar( $value ) {
	return (int) (bool) $value;
}

/**
 * フォントサイズを 9〜14 の整数に正規化する。
 *
 * @param mixed $value Input value.
 * @return int
 */
function yefsw_sanitize_sp_head_bar_fz( $value ) {
	$value = is_numeric( $value ) ? (int) $value : YEFSW_SP_HEAD_BAR_FZ_DEFAULT;
	return max( YEFSW_SP_HEAD_BAR_FZ_MIN, min( YEFSW_SP_HEAD_BAR_FZ_MAX, $value ) );
}

/**
 * キャッチコピー帯のフォントサイズを返す。
 *
 * @return int
 */
function yefsw_get_sp_head_bar_fz() {
	return yefsw_sanitize_sp_head_bar_fz(
		get_option( YEFSW_SP_HEAD_BAR_FZ_OPTION, YEFSW_SP_HEAD_BAR_FZ_DEFAULT )
	);
}

/**
 * SPヘッダーバーが表示オンのときだけサイズ項目を出す。
 *
 * @param WP_Customize_Control $control Control.
 * @return bool
 */
function yefsw_sp_head_bar_fz_control_active( $control ) {
	$setting = $control->manager->get_setting( YEFSW_SP_HEAD_BAR_OPTION );
	return $setting && yefsw_sanitize_sp_head_bar( $setting->value() );
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
			'default'           => 0,
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

	$wp_customize->add_setting(
		YEFSW_SP_HEAD_BAR_FZ_OPTION,
		array(
			'default'           => YEFSW_SP_HEAD_BAR_FZ_DEFAULT,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_head_bar_fz',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_HEAD_BAR_FZ_OPTION,
		array(
			'label'           => '[Y]SPヘッダーバーのフォントサイズ（px）',
			'description'     => '初期値は 12。小さいほど1行に収まりやすいです。',
			'section'         => 'swell_section_header',
			'type'            => 'number',
			'input_attrs'     => array(
				'min'  => (string) YEFSW_SP_HEAD_BAR_FZ_MIN,
				'max'  => (string) YEFSW_SP_HEAD_BAR_FZ_MAX,
				'step' => '1',
			),
			'active_callback' => 'yefsw_sp_head_bar_fz_control_active',
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
			$fz = $wp_customize->get_control( YEFSW_SP_HEAD_BAR_FZ_OPTION );
			if ( $fz ) {
				$fz->priority = $control->priority + 2;
			}
		}
	}
}
add_action( 'customize_register', 'yefsw_register_sp_head_bar', 120 );

/**
 * 表示チェックに合わせてフォントサイズ項目の表示を切り替える。
 */
function yefsw_enqueue_sp_head_bar_customize_controls() {
	if ( ! yefsw_sp_head_bar_is_swell() ) {
		return;
	}

	wp_enqueue_script(
		'yefsw-sp-head-bar-controls',
		YEFSW_PLUGIN_URL . 'assets/sp-head-bar/customize-controls.js',
		array( 'customize-controls', 'jquery' ),
		YEFSW_VERSION,
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'yefsw_enqueue_sp_head_bar_customize_controls' );

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

	$fz = yefsw_get_sp_head_bar_fz();

	wp_add_inline_style(
		YEFSW_SP_HEAD_BAR_HANDLE,
		'.yefsw-sp-head-bar{color:' . $text . ';background:' . $bg . ';}.yefsw-sp-head-bar__phrase{font-size:' . $fz . 'px;}'
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
