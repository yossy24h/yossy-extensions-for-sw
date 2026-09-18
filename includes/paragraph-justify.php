<?php
/**
 * 段落ブロックの両端揃えをカスタマイザーから切り替える。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_JUSTIFY_PARAGRAPHS_OPTION = 'yefsw_justify_paragraphs';
const YEFSW_JUSTIFY_PARAGRAPHS_EDITOR_HANDLE = 'yefsw-paragraph-justify-editor';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_justify_paragraphs_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * チェックボックス値を 0 / 1 に正規化する。
 *
 * option 型設定のプレビューでは false が予約値になるため、オフは整数 0 を返す。
 *
 * @param mixed $value Input value.
 * @return int
 */
function yefsw_sanitize_justify_paragraphs( $value ) {
	return (int) (bool) $value;
}

/**
 * 両端揃え用CSSを返す。
 *
 * @param string $scope 対象範囲のセレクター。
 * @return string
 */
function yefsw_get_justify_paragraphs_css( $scope ) {
	$selector = $scope . ' p.wp-block-paragraph:not(.is-style-yefsw-align-left):not(.has-text-align-left):not(.has-text-align-center):not(.has-text-align-right):not([style*="text-align"])';
	return $selector . '{text-align:justify;text-align-last:start;text-justify:inter-character}';
}

/**
 * 基本デザインの「字間」の直後へ設定を追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_justify_paragraphs( $wp_customize ) {
	$section = 'swell_section_base_design';

	if ( ! yefsw_justify_paragraphs_is_swell() || ! $wp_customize->get_section( $section ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_JUSTIFY_PARAGRAPHS_OPTION,
		array(
			'default'           => 0,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_justify_paragraphs',
		)
	);

	$wp_customize->add_control(
		YEFSW_JUSTIFY_PARAGRAPHS_OPTION,
		array(
			'label'       => '[Y]段落ブロックを両端揃えにする',
			'description' => '段落の途中の行を両端揃えにします。最終行、中央・右揃え、および「左揃え（両端揃えを解除）」スタイルの段落には適用されません。',
			'section'     => $section,
			'type'        => 'checkbox',
		)
	);

	/*
	 * SWELLの「字間」の直後へ配置する。
	 * 同一priorityの既存コントロール順を明示的な数値へ置き換えて維持する。
	 */
	$controls = $wp_customize->controls();
	$priority = 10;

	foreach ( $controls as $control ) {
		if ( $section !== $control->section || YEFSW_JUSTIFY_PARAGRAPHS_OPTION === $control->id ) {
			continue;
		}

		$control->priority = $priority;
		$priority         += 10;

		if ( 'loos_customizer[site_letter_space]' === $control->id ) {
			$wp_customize->get_control( YEFSW_JUSTIFY_PARAGRAPHS_OPTION )->priority = $control->priority + 1;
		}
	}
}
add_action( 'customize_register', 'yefsw_register_justify_paragraphs', 120 );

/**
 * 左揃えを個別に優先するための段落スタイルを追加する。
 *
 * WordPressは標準の左揃えをHTMLへ記録しないため、専用クラスで識別する。
 */
function yefsw_register_paragraph_left_style() {
	if ( ! yefsw_justify_paragraphs_is_swell() || ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style(
		'core/paragraph',
		array(
			'name'         => 'yefsw-align-left',
			'label'        => '左揃え（両端揃えを解除）',
			'inline_style' => '.wp-block-paragraph.is-style-yefsw-align-left{text-align:start}',
		)
	);
}
add_action( 'init', 'yefsw_register_paragraph_left_style', 20 );

/**
 * 通常の段落ブロックだけを両端揃えにする。
 *
 * 配置クラスまたはインラインの text-align がある段落は、個別指定を優先する。
 */
function yefsw_enqueue_justify_paragraphs_css() {
	if ( ! yefsw_justify_paragraphs_is_swell() ) {
		return;
	}

	if ( ! yefsw_sanitize_justify_paragraphs( get_option( YEFSW_JUSTIFY_PARAGRAPHS_OPTION, 0 ) ) ) {
		return;
	}

	wp_add_inline_style( 'main_style', yefsw_get_justify_paragraphs_css( '.post_content' ) );
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_justify_paragraphs_css', 20 );

/**
 * ブロックエディターの編集キャンバスにも両端揃えを反映する。
 */
function yefsw_enqueue_justify_paragraphs_editor_css() {
	if ( ! is_admin() || ! yefsw_justify_paragraphs_is_swell() ) {
		return;
	}

	if ( ! yefsw_sanitize_justify_paragraphs( get_option( YEFSW_JUSTIFY_PARAGRAPHS_OPTION, 0 ) ) ) {
		return;
	}

	wp_register_style(
		YEFSW_JUSTIFY_PARAGRAPHS_EDITOR_HANDLE,
		false,
		array(),
		YEFSW_VERSION
	);

	wp_enqueue_style( YEFSW_JUSTIFY_PARAGRAPHS_EDITOR_HANDLE );
	wp_add_inline_style(
		YEFSW_JUSTIFY_PARAGRAPHS_EDITOR_HANDLE,
		yefsw_get_justify_paragraphs_css( '.editor-styles-wrapper' )
	);
}
add_action( 'enqueue_block_assets', 'yefsw_enqueue_justify_paragraphs_editor_css', 30 );
