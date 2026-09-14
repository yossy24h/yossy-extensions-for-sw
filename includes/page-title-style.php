<?php
/**
 * 固定ページのページヘッダータイトル（横位置・フォントサイズ）をカスタマイザーから変更する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_PAGE_TITLE_ALIGN_OPTION = 'yefsw_page_title_align';
const YEFSW_PAGE_TITLE_FZ_SP_OPTION = 'yefsw_page_title_fz_sp';
const YEFSW_PAGE_TITLE_FZ_PC_OPTION = 'yefsw_page_title_fz_pc';
const YEFSW_PAGE_TITLE_POS_SETTING  = 'loos_customizer[page_title_pos]';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_page_title_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * 横位置を left / center に正規化する。
 *
 * @param mixed $value Input value.
 * @return string
 */
function yefsw_sanitize_page_title_align( $value ) {
	return 'center' === $value ? 'center' : 'left';
}

/**
 * フォントサイズを指定範囲へ正規化する。
 *
 * @param mixed $value   Input value.
 * @param float $min     Minimum.
 * @param float $max     Maximum.
 * @param float $default Default.
 * @return float
 */
function yefsw_sanitize_page_title_fz( $value, $min, $max, $default ) {
	$value = is_numeric( $value ) ? (float) $value : $default;
	return round( max( $min, min( $max, $value ) ), 1 );
}

/**
 * SPサイズを正規化する。
 *
 * @param mixed $value Input value.
 * @return float
 */
function yefsw_sanitize_page_title_fz_sp( $value ) {
	return yefsw_sanitize_page_title_fz( $value, 1.3, 2.3, 1.3 );
}

/**
 * PCサイズを正規化する。
 *
 * @param mixed $value Input value.
 * @return float
 */
function yefsw_sanitize_page_title_fz_pc( $value ) {
	return yefsw_sanitize_page_title_fz( $value, 1.5, 2.5, 1.5 );
}

/**
 * 固定ページのタイトル位置が「コンテンツ上」のときだけコントロールを表示する。
 *
 * @param WP_Customize_Control $control Control.
 * @return bool
 */
function yefsw_page_title_control_active( $control ) {
	$setting = $control->manager->get_setting( YEFSW_PAGE_TITLE_POS_SETTING );
	return $setting && 'top' === $setting->value();
}

/**
 * カスタマイザーに設定を追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_page_title_style( $wp_customize ) {
	if ( ! yefsw_page_title_is_swell() || ! $wp_customize->get_section( 'swell_section_content_header' ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_PAGE_TITLE_ALIGN_OPTION,
		array(
			'default'           => 'left',
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_page_title_align',
		)
	);

	$wp_customize->add_setting(
		YEFSW_PAGE_TITLE_FZ_SP_OPTION,
		array(
			'default'           => 1.3,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_page_title_fz_sp',
		)
	);

	$wp_customize->add_setting(
		YEFSW_PAGE_TITLE_FZ_PC_OPTION,
		array(
			'default'           => 1.5,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_page_title_fz_pc',
		)
	);

	$wp_customize->add_control(
		YEFSW_PAGE_TITLE_ALIGN_OPTION,
		array(
			'label'           => '[Y]固定ページタイトルの横位置',
			'section'         => 'swell_section_content_header',
			'type'            => 'radio',
			'choices'         => array(
				'left'   => '左揃え',
				'center' => '中央揃え',
			),
			'active_callback' => 'yefsw_page_title_control_active',
		)
	);

	$wp_customize->add_control(
		YEFSW_PAGE_TITLE_FZ_SP_OPTION,
		array(
			'label'           => '[Y]固定ページタイトルのフォントサイズ（SP）（em）',
			'section'         => 'swell_section_content_header',
			'type'            => 'number',
			'input_attrs'     => array(
				'min'  => '1.3',
				'max'  => '2.3',
				'step' => '0.1',
			),
			'active_callback' => 'yefsw_page_title_control_active',
		)
	);

	$wp_customize->add_control(
		YEFSW_PAGE_TITLE_FZ_PC_OPTION,
		array(
			'label'           => '[Y]固定ページタイトルのフォントサイズ（PC）（em）',
			'section'         => 'swell_section_content_header',
			'type'            => 'number',
			'input_attrs'     => array(
				'min'  => '1.5',
				'max'  => '2.5',
				'step' => '0.1',
			),
			'active_callback' => 'yefsw_page_title_control_active',
		)
	);

	$max = 10;
	$ours = array(
		YEFSW_PAGE_TITLE_ALIGN_OPTION,
		YEFSW_PAGE_TITLE_FZ_SP_OPTION,
		YEFSW_PAGE_TITLE_FZ_PC_OPTION,
	);

	foreach ( $wp_customize->controls() as $control ) {
		if ( 'swell_section_content_header' !== $control->section || in_array( $control->id, $ours, true ) ) {
			continue;
		}

		$max = max( $max, (int) $control->priority );
	}

	$wp_customize->get_control( YEFSW_PAGE_TITLE_ALIGN_OPTION )->priority = $max + 10;
	$wp_customize->get_control( YEFSW_PAGE_TITLE_FZ_PC_OPTION )->priority = $max + 20;
	$wp_customize->get_control( YEFSW_PAGE_TITLE_FZ_SP_OPTION )->priority = $max + 30;
}
add_action( 'customize_register', 'yefsw_register_page_title_style', 120 );

/**
 * タイトル位置の変更に合わせてコントロール表示を切り替える。
 */
function yefsw_enqueue_page_title_customize_controls() {
	if ( ! yefsw_page_title_is_swell() ) {
		return;
	}

	wp_enqueue_script(
		'yefsw-page-title-style-controls',
		YEFSW_PLUGIN_URL . 'assets/page-title-style/customize-controls.js',
		array( 'customize-controls', 'jquery' ),
		YEFSW_VERSION,
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'yefsw_enqueue_page_title_customize_controls' );

/**
 * 固定ページのページヘッダータイトルにだけCSSを適用する。
 */
function yefsw_enqueue_page_title_style_css() {
	if ( ! yefsw_page_title_is_swell() ) {
		return;
	}

	$align = yefsw_sanitize_page_title_align(
		get_option( YEFSW_PAGE_TITLE_ALIGN_OPTION, 'left' )
	);
	$fz_sp = yefsw_sanitize_page_title_fz_sp(
		get_option( YEFSW_PAGE_TITLE_FZ_SP_OPTION, 1.3 )
	);
	$fz_pc = yefsw_sanitize_page_title_fz_pc(
		get_option( YEFSW_PAGE_TITLE_FZ_PC_OPTION, 1.5 )
	);

	$css = sprintf(
		'.page .l-topTitleArea .c-pageTitle{text-align:%1$s;font-size:%2$sem}@media(min-width:600px){.page .l-topTitleArea .c-pageTitle{font-size:%3$sem}}',
		$align,
		number_format( $fz_sp, 1, '.', '' ),
		number_format( $fz_pc, 1, '.', '' )
	);

	wp_add_inline_style( 'main_style', $css );
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_page_title_style_css', 20 );
