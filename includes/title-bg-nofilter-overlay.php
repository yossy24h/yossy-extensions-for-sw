<?php
/**
 * コンテンツヘッダーの画像フィルターが「なし」でもカラーオーバーレイを有効にする。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_TTLBG_NOFILTER_OVERLAY_NOTE = 'yefsw_ttlbg_nofilter_overlay_note';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_ttlbg_nofilter_overlay_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * 不透明度を 0〜1 に正規化する。
 *
 * @param mixed $value Input value.
 * @return float
 */
function yefsw_sanitize_ttlbg_overlay_opacity( $value ) {
	$value = is_numeric( $value ) ? (float) $value : 0.2;
	return round( max( 0, min( 1, $value ) ), 1 );
}

/**
 * 画像フィルターのセレクト直下に説明を出す。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_ttlbg_nofilter_overlay_note( $wp_customize ) {
	if ( ! yefsw_ttlbg_nofilter_overlay_is_swell() || ! $wp_customize->get_section( 'swell_section_content_header' ) ) {
		return;
	}

	if ( ! class_exists( '\SWELL_Theme\Customizer\Control\Sub_Title' ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_TTLBG_NOFILTER_OVERLAY_NOTE,
		array(
			'sanitize_callback' => '__return_empty_string',
		)
	);

	$wp_customize->add_control(
		new \SWELL_Theme\Customizer\Control\Sub_Title(
			$wp_customize,
			YEFSW_TTLBG_NOFILTER_OVERLAY_NOTE,
			array(
				'label'   => '[Y]「なし」でもオーバーレイカラーを有効化済み',
				'section' => 'swell_section_content_header',
			)
		)
	);

	$controls = $wp_customize->controls();
	$priority = 10;

	foreach ( $controls as $control ) {
		if ( 'swell_section_content_header' !== $control->section || YEFSW_TTLBG_NOFILTER_OVERLAY_NOTE === $control->id ) {
			continue;
		}

		if ( 0 === strpos( $control->id, 'yefsw_page_title_' ) ) {
			continue;
		}

		$control->priority = $priority;
		$priority         += 10;

		if ( 'loos_customizer[title_bg_filter]' === $control->id ) {
			$wp_customize->get_control( YEFSW_TTLBG_NOFILTER_OVERLAY_NOTE )->priority = $control->priority + 1;
		}
	}
}
add_action( 'customize_register', 'yefsw_register_ttlbg_nofilter_overlay_note', 110 );

/**
 * フィルター「なし」のときだけ、SWELLと同じオーバーレイを当てる。
 */
function yefsw_enqueue_ttlbg_nofilter_overlay_css() {
	if ( ! yefsw_ttlbg_nofilter_overlay_is_swell() || ! class_exists( 'SWELL_Theme' ) ) {
		return;
	}

	if ( 'nofilter' !== SWELL_Theme::get_setting( 'title_bg_filter' ) ) {
		return;
	}

	$color   = sanitize_hex_color( (string) SWELL_Theme::get_setting( 'ttlbg_overlay_color' ) );
	$color   = $color ? $color : '#000000';
	$opacity = yefsw_sanitize_ttlbg_overlay_opacity(
		SWELL_Theme::get_setting( 'ttlbg_overlay_opacity' )
	);

	$css = sprintf(
		'.l-topTitleArea:not(.c-filterLayer)::before{content:"";position:absolute;top:0;left:0;width:100%%;height:100%%;z-index:1;background-color:%1$s;opacity:%2$s;pointer-events:none}',
		$color,
		number_format( $opacity, 1, '.', '' )
	);

	wp_add_inline_style( 'main_style', $css );
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_ttlbg_nofilter_overlay_css', 20 );
