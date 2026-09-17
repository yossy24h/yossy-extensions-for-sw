<?php
/**
 * スマホ開閉メニューのタイトル表示とメニュー揃えをカスタマイザーから変更する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_SP_MENU_VERTICAL_OPTION = 'yefsw_sp_menu_vertical_align';
const YEFSW_SP_MENU_TITLE_OPTION  = 'yefsw_sp_menu_title';
const YEFSW_SP_MENU_NAV_OPTION    = 'yefsw_sp_menu_nav_center';
const YEFSW_SP_MENU_BORDER_OPTION = 'yefsw_sp_menu_hide_border';
const YEFSW_SP_MENU_COLOR_OPTION  = 'yefsw_sp_menu_border_color';
const YEFSW_SP_MENU_ALIGN_HANDLE  = 'yefsw-sp-menu-align';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_sp_menu_align_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * 画面の上下揃えを正規化する。
 *
 * @param mixed $value Input value.
 * @return string
 */
function yefsw_sanitize_sp_menu_vertical_align( $value ) {
	return in_array( $value, array( 'top', 'center' ), true ) ? $value : 'top';
}

/**
 * 上下中央揃えが有効か。
 *
 * @return bool
 */
function yefsw_sp_menu_vertical_is_center() {
	return 'center' === yefsw_sanitize_sp_menu_vertical_align(
		get_option( YEFSW_SP_MENU_VERTICAL_OPTION, 'top' )
	);
}

/**
 * タイトル表示を left / center / hide に正規化する。
 *
 * @param mixed $value Input value.
 * @return string
 */
function yefsw_sanitize_sp_menu_title( $value ) {
	$allowed = array( 'left', 'center', 'hide' );
	return in_array( $value, $allowed, true ) ? $value : 'left';
}

/**
 * メニュー中央揃えを正規化する。
 *
 * @param mixed $value Input value.
 * @return bool
 */
function yefsw_sanitize_sp_menu_nav_center( $value ) {
	return (bool) $value;
}

/**
 * タイトル表示の設定値を返す。
 *
 * @return string
 */
function yefsw_get_sp_menu_title() {
	return yefsw_sanitize_sp_menu_title(
		get_option( YEFSW_SP_MENU_TITLE_OPTION, 'left' )
	);
}

/**
 * メニューを中央揃えにするか。
 *
 * @return bool
 */
function yefsw_sp_menu_nav_is_center() {
	return yefsw_sanitize_sp_menu_nav_center(
		get_option( YEFSW_SP_MENU_NAV_OPTION, false )
	);
}

/**
 * メニュー下の下線を消すか。
 *
 * @param mixed $value Input value.
 * @return bool
 */
function yefsw_sanitize_sp_menu_hide_border( $value ) {
	return (bool) $value;
}

/**
 * メニュー下の下線を消す設定値を返す。
 *
 * @return bool
 */
function yefsw_sp_menu_border_is_hidden() {
	return yefsw_sanitize_sp_menu_hide_border(
		get_option( YEFSW_SP_MENU_BORDER_OPTION, false )
	);
}

/**
 * 下線色を 16 進カラーまたは空文字に正規化する。
 *
 * @param mixed $value Input value.
 * @return string
 */
function yefsw_sanitize_sp_menu_border_color( $value ) {
	$color = sanitize_hex_color( $value );
	return $color ? $color : '';
}

/**
 * 下線色を返す。未選択なら空文字。
 *
 * @return string
 */
function yefsw_get_sp_menu_border_color() {
	return yefsw_sanitize_sp_menu_border_color(
		get_option( YEFSW_SP_MENU_COLOR_OPTION, '' )
	);
}

/**
 * 下線非表示がオフのときだけ色項目を出す。
 *
 * @param WP_Customize_Control $control Control.
 * @return bool
 */
function yefsw_sp_menu_border_color_control_active( $control ) {
	$setting = $control->manager->get_setting( YEFSW_SP_MENU_BORDER_OPTION );
	return $setting && ! yefsw_sanitize_sp_menu_hide_border( $setting->value() );
}

/**
 * カスタマイザーに設定を追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_sp_menu_align( $wp_customize ) {
	if ( ! yefsw_sp_menu_align_is_swell() || ! $wp_customize->get_section( 'swell_section_sp_menu' ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_SP_MENU_VERTICAL_OPTION,
		array(
			'default'           => 'top',
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_menu_vertical_align',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_MENU_VERTICAL_OPTION,
		array(
			'label'       => '[Y]メニュー全体の縦位置',
			'description' => 'タイトル・メニュー・下部のコンテンツをまとめて配置します。',
			'section' => 'swell_section_sp_menu',
			'type'    => 'radio',
			'choices' => array(
				'top'    => '上揃え',
				'center' => '中央揃え',
			),
		)
	);

	$wp_customize->add_setting(
		YEFSW_SP_MENU_TITLE_OPTION,
		array(
			'default'           => 'left',
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_menu_title',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_MENU_TITLE_OPTION,
		array(
			'label'   => '[Y]タイトルの表示',
			'section' => 'swell_section_sp_menu',
			'type'    => 'radio',
			'choices' => array(
				'left'   => '左揃え',
				'center' => '中央揃え',
				'hide'   => '非表示',
			),
		)
	);

	$wp_customize->add_setting(
		YEFSW_SP_MENU_NAV_OPTION,
		array(
			'default'           => false,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_menu_nav_center',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_MENU_NAV_OPTION,
		array(
			'label'       => '[Y]メニューを中央揃えにする',
			'description' => '先頭のアイコン（>）も非表示になります。',
			'section'     => 'swell_section_sp_menu',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		YEFSW_SP_MENU_BORDER_OPTION,
		array(
			'default'           => false,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_menu_hide_border',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_MENU_BORDER_OPTION,
		array(
			'label'   => '[Y]メニュー下の下線を非表示にする',
			'section' => 'swell_section_sp_menu',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		YEFSW_SP_MENU_COLOR_OPTION,
		array(
			'default'           => '',
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_menu_border_color',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			YEFSW_SP_MENU_COLOR_OPTION,
			array(
				'label'           => '[Y]メニュー下の下線の色',
				'description'     => '未選択のときは SWELL のグレーのままです。',
				'section'         => 'swell_section_sp_menu',
				'active_callback' => 'yefsw_sp_menu_border_color_control_active',
			)
		)
	);

	$max  = 10;
	$ours = array(
		YEFSW_SP_MENU_VERTICAL_OPTION,
		YEFSW_SP_MENU_TITLE_OPTION,
		YEFSW_SP_MENU_NAV_OPTION,
		YEFSW_SP_MENU_BORDER_OPTION,
		YEFSW_SP_MENU_COLOR_OPTION,
	);

	foreach ( $wp_customize->controls() as $control ) {
		if ( 'swell_section_sp_menu' !== $control->section || in_array( $control->id, $ours, true ) ) {
			continue;
		}

		$max = max( $max, (int) $control->priority );
	}

	$wp_customize->get_control( YEFSW_SP_MENU_VERTICAL_OPTION )->priority = $max + 5;
	$wp_customize->get_control( YEFSW_SP_MENU_TITLE_OPTION )->priority  = $max + 10;
	$wp_customize->get_control( YEFSW_SP_MENU_NAV_OPTION )->priority    = $max + 20;
	$wp_customize->get_control( YEFSW_SP_MENU_BORDER_OPTION )->priority = $max + 30;
	$wp_customize->get_control( YEFSW_SP_MENU_COLOR_OPTION )->priority  = $max + 40;
}
add_action( 'customize_register', 'yefsw_register_sp_menu_align', 120 );

/**
 * 下線非表示のチェックに合わせて色項目の表示を切り替える。
 */
function yefsw_enqueue_sp_menu_align_customize_controls() {
	if ( ! yefsw_sp_menu_align_is_swell() ) {
		return;
	}

	wp_enqueue_style(
		'yefsw-sp-menu-align-controls',
		YEFSW_PLUGIN_URL . 'assets/sp-menu-align/customize-controls.css',
		array(),
		YEFSW_VERSION
	);

	wp_enqueue_script(
		'yefsw-sp-menu-align-controls',
		YEFSW_PLUGIN_URL . 'assets/sp-menu-align/customize-controls.js',
		array( 'customize-controls', 'jquery' ),
		YEFSW_VERSION,
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'yefsw_enqueue_sp_menu_align_customize_controls' );

/**
 * フロント用の class を body に付ける。
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function yefsw_sp_menu_align_body_class( $classes ) {
	if ( ! yefsw_sp_menu_align_is_swell() ) {
		return $classes;
	}

	if ( yefsw_sp_menu_vertical_is_center() ) {
		$classes[] = 'yefsw-sp-menu-vertical-center';
	}

	$title = yefsw_get_sp_menu_title();
	if ( 'center' === $title ) {
		$classes[] = 'yefsw-sp-menu-title-center';
	} elseif ( 'hide' === $title ) {
		$classes[] = 'yefsw-sp-menu-title-hide';
	}

	if ( yefsw_sp_menu_nav_is_center() ) {
		$classes[] = 'yefsw-sp-menu-nav-center';
	}

	if ( yefsw_sp_menu_border_is_hidden() ) {
		$classes[] = 'yefsw-sp-menu-nav-noborder';
	}

	return $classes;
}
add_filter( 'body_class', 'yefsw_sp_menu_align_body_class' );

/**
 * フロント用 CSS。
 */
function yefsw_enqueue_sp_menu_align_assets() {
	if ( ! yefsw_sp_menu_align_is_swell() ) {
		return;
	}

	$title      = yefsw_get_sp_menu_title();
	$nav_on     = yefsw_sp_menu_nav_is_center();
	$border_off = yefsw_sp_menu_border_is_hidden();
	$color      = yefsw_get_sp_menu_border_color();
	$need_color = ( '' !== $color && ! $border_off );
	if ( ! yefsw_sp_menu_vertical_is_center() && 'left' === $title && ! $nav_on && ! $border_off && ! $need_color ) {
		return;
	}

	wp_enqueue_style(
		YEFSW_SP_MENU_ALIGN_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/sp-menu-align/front.css',
		array(),
		YEFSW_VERSION
	);

	if ( $need_color ) {
		wp_add_inline_style(
			YEFSW_SP_MENU_ALIGN_HANDLE,
			'#sp_menu .c-spnav a,#sp_menu .c-listMenu a{border-bottom-color:' . $color . ';}'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_sp_menu_align_assets', 20 );
