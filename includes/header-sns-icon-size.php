<?php
/**
 * PCヘッダーバーのSNSアイコンサイズをカスタマイザーから変更する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_HEADER_SNS_ICON_SIZE_OPTION = 'yefsw_header_sns_icon_size';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_header_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * SNSアイコンサイズを 1.0〜2.0 の範囲に正規化する。
 *
 * @param mixed $value Input value.
 * @return float
 */
function yefsw_sanitize_header_sns_icon_size( $value ) {
	$value = is_numeric( $value ) ? (float) $value : 1.0;
	return round( max( 1.0, min( 2.0, $value ) ), 1 );
}

/**
 * カスタマイザーに設定を追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_header_sns_icon_size( $wp_customize ) {
	if ( ! yefsw_header_is_swell() || ! $wp_customize->get_section( 'swell_section_header' ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_HEADER_SNS_ICON_SIZE_OPTION,
		array(
			'default'           => 1.0,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_header_sns_icon_size',
		)
	);

	$wp_customize->add_control(
		YEFSW_HEADER_SNS_ICON_SIZE_OPTION,
		array(
			'label'       => '[Y]SNSアイコンのフォントサイズ（em）',
			'section'     => 'swell_section_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => '1.0',
				'max'  => '2.0',
				'step' => '0.1',
			),
		)
	);

	/*
	 * SWELLの「SNSアイコンリストを表示する」の直後へ配置する。
	 * 同一priorityの既存コントロール順を明示的な数値へ置き換えて維持する。
	 */
	$controls = $wp_customize->controls();
	$priority = 10;

	foreach ( $controls as $control ) {
		if ( 'swell_section_header' !== $control->section || YEFSW_HEADER_SNS_ICON_SIZE_OPTION === $control->id ) {
			continue;
		}

		$control->priority = $priority;
		$priority         += 10;

		if ( 'loos_customizer[show_icon_list]' === $control->id ) {
			$wp_customize->get_control( YEFSW_HEADER_SNS_ICON_SIZE_OPTION )->priority = $control->priority + 1;
		}
	}
}
add_action( 'customize_register', 'yefsw_register_header_sns_icon_size', 110 );

/**
 * PCヘッダーバーのアイコンリスト（SNS・検索）にサイズ倍率を適用する。
 */
function yefsw_enqueue_header_sns_icon_size_css() {
	if ( ! yefsw_header_is_swell() ) {
		return;
	}

	$size = yefsw_sanitize_header_sns_icon_size(
		get_option( YEFSW_HEADER_SNS_ICON_SIZE_OPTION, 1.0 )
	);

	$css = sprintf(
		'.l-header__bar .c-iconList__item .c-iconList__link{display:inline-flex;align-items:center;justify-content:center;font-size:calc(14px * %1$s)!important;line-height:1;width:auto;height:auto;min-width:1em;padding-block:.286em}',
		number_format( $size, 1, '.', '' )
	);

	wp_add_inline_style( 'main_style', $css );
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_header_sns_icon_size_css', 20 );
