<?php
/**
 * スマホ開閉メニューに SNS アイコンリストを表示する。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_SP_MENU_SNS_OPTION = 'yefsw_show_sp_menu_sns';
const YEFSW_SP_MENU_SNS_HANDLE = 'yefsw-sp-menu-sns';

/**
 * 親テーマがSWELLかどうか。
 *
 * @return bool
 */
function yefsw_sp_menu_sns_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

/**
 * チェックボックス値を正規化する。
 *
 * @param mixed $value Input value.
 * @return bool
 */
function yefsw_sanitize_sp_menu_sns( $value ) {
	return (bool) $value;
}

/**
 * 開閉メニューに SNS リストを出す条件。
 *
 * @return bool
 */
function yefsw_sp_menu_sns_should_render() {
	if ( ! yefsw_sp_menu_sns_is_swell() ) {
		return false;
	}

	if ( ! yefsw_sanitize_sp_menu_sns( get_option( YEFSW_SP_MENU_SNS_OPTION, false ) ) ) {
		return false;
	}

	return '' !== yefsw_get_sp_menu_sns_html();
}

/**
 * SNS アイコンリストの HTML。
 *
 * @return string
 */
function yefsw_get_sp_menu_sns_html() {
	if ( ! class_exists( 'SWELL_Theme' ) ) {
		return '';
	}

	$sns_settings = SWELL_Theme::get_sns_settings();
	if ( empty( $sns_settings ) ) {
		return '';
	}

	ob_start();
	SWELL_Theme::get_parts(
		'parts/icon_list',
		array(
			'list_data' => $sns_settings,
			'ul_class'  => '',
			'fz_class'  => 'u-fz-14',
		)
	);
	$list = trim( (string) ob_get_clean() );
	if ( '' === $list ) {
		return '';
	}

	return '<div class="yefsw-sp-menu-sns">' . $list . '</div>';
}

/**
 * カスタマイザーに設定を追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_register_sp_menu_sns( $wp_customize ) {
	if ( ! yefsw_sp_menu_sns_is_swell() || ! $wp_customize->get_section( 'swell_section_sp_menu' ) ) {
		return;
	}

	$wp_customize->add_setting(
		YEFSW_SP_MENU_SNS_OPTION,
		array(
			'default'           => false,
			'type'              => 'option',
			'transport'         => 'refresh',
			'sanitize_callback' => 'yefsw_sanitize_sp_menu_sns',
		)
	);

	$wp_customize->add_control(
		YEFSW_SP_MENU_SNS_OPTION,
		array(
			'label'   => '[Y]SNSアイコンリストを表示する',
			'section' => 'swell_section_sp_menu',
			'type'    => 'checkbox',
		)
	);

	$controls = $wp_customize->controls();
	$priority = 10;

	foreach ( $controls as $control ) {
		if ( 'swell_section_sp_menu' !== $control->section || YEFSW_SP_MENU_SNS_OPTION === $control->id ) {
			continue;
		}

		$control->priority = $priority;
		$priority         += 10;

		if ( 'sub_ttl_acc_sp_submenu' === $control->id ) {
			$wp_customize->get_control( YEFSW_SP_MENU_SNS_OPTION )->priority = $control->priority + 1;
		}
	}
}
add_action( 'customize_register', 'yefsw_register_sp_menu_sns', 110 );

/**
 * フロント用 CSS / JS。
 */
function yefsw_enqueue_sp_menu_sns_assets() {
	if ( ! yefsw_sp_menu_sns_should_render() ) {
		return;
	}

	$html = yefsw_get_sp_menu_sns_html();
	if ( '' === $html ) {
		return;
	}

	$size = 1.0;
	if ( function_exists( 'yefsw_sanitize_header_sns_icon_size' ) ) {
		$size = yefsw_sanitize_header_sns_icon_size(
			get_option( YEFSW_HEADER_SNS_ICON_SIZE_OPTION, 1.0 )
		);
	}

	wp_enqueue_style(
		YEFSW_SP_MENU_SNS_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/sp-menu-sns/front.css',
		array(),
		YEFSW_VERSION
	);

	wp_add_inline_style(
		YEFSW_SP_MENU_SNS_HANDLE,
		sprintf(
			'.yefsw-sp-menu-sns .c-iconList__link{display:inline-flex;align-items:center;justify-content:center;font-size:calc(14px * %1$s)!important;line-height:1;width:auto;height:auto;min-width:1em;padding-block:.286em}',
			number_format( $size, 1, '.', '' )
		)
	);

	wp_enqueue_script(
		YEFSW_SP_MENU_SNS_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/sp-menu-sns/front.js',
		array(),
		YEFSW_VERSION,
		true
	);

	wp_localize_script(
		YEFSW_SP_MENU_SNS_HANDLE,
		'yefswSpMenuSns',
		array(
			'html' => $html,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'yefsw_enqueue_sp_menu_sns_assets', 20 );
