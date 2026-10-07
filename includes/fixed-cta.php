<?php
/**
 * 固定ページごとに表示できる固定 CTA ボタン。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const YEFSW_FIXED_CTA_PREFIX = 'yefsw_fixed_cta_';
const YEFSW_FIXED_CTA_META   = '_yefsw_fixed_cta_enabled';
const YEFSW_FIXED_CTA_HANDLE = 'yefsw-fixed-cta';

function yefsw_fixed_cta_is_swell() {
	return 'swell' === get_template() || defined( 'SWELL_VERSION' );
}

function yefsw_fixed_cta_sanitize_bool( $value ) {
	return (int) (bool) $value;
}

function yefsw_fixed_cta_sanitize_int( $value, $min, $max, $default ) {
	$value = is_numeric( $value ) ? (int) $value : $default;
	return max( $min, min( $max, $value ) );
}

function yefsw_fixed_cta_sanitize_width_pc( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 160, 640, 320 );
}

function yefsw_fixed_cta_sanitize_width_sp( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 200, 360, 300 );
}

function yefsw_fixed_cta_sanitize_font_pc( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 12, 24, 16 );
}

function yefsw_fixed_cta_sanitize_font_sp( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 12, 20, 15 );
}

function yefsw_fixed_cta_sanitize_height_pc( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 44, 80, 52 );
}

function yefsw_fixed_cta_sanitize_height_sp( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 44, 72, 48 );
}

function yefsw_fixed_cta_sanitize_border_width( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 0, 8, 0 );
}

function yefsw_fixed_cta_sanitize_radius( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 0, 999, 999 );
}

function yefsw_fixed_cta_sanitize_bottom( $value ) {
	return yefsw_fixed_cta_sanitize_int( $value, 0, 100, 16 );
}

function yefsw_fixed_cta_sanitize_threshold( $value ) {
	$value = is_numeric( $value ) ? (int) $value : 300;
	return max( 0, $value );
}

function yefsw_fixed_cta_sanitize_devices( $value ) {
	$value = is_string( $value ) ? $value : 'all';
	return in_array( $value, array( 'all', 'pc', 'sp' ), true ) ? $value : 'all';
}

function yefsw_fixed_cta_sanitize_animation( $value ) {
	$value = is_string( $value ) ? $value : 'fade-slide';
	return in_array( $value, array( 'none', 'fade', 'slide', 'fade-slide' ), true ) ? $value : 'fade-slide';
}

function yefsw_fixed_cta_sanitize_image_id( $value ) {
	return max( 0, absint( $value ) );
}

/**
 * CTA 共通設定をカスタマイザーへ追加する。
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function yefsw_fixed_cta_customize_register( $wp_customize ) {
	if ( ! yefsw_fixed_cta_is_swell() ) {
		return;
	}

	$wp_customize->add_section(
		'yefsw_fixed_cta_section',
		array(
			'title'       => '[Y] 固定CTAボタン',
			'description' => '全体機能を有効にした後、表示する固定ページを各ページの編集画面で選択します。',
			'panel'       => $wp_customize->get_panel( 'swell_panel_common' ) ? 'swell_panel_common' : '',
			'priority'    => 250,
		)
	);

	$settings = array(
		'enabled'      => array( 0, 'yefsw_fixed_cta_sanitize_bool' ),
		'label'        => array( '詳しくはこちら', 'sanitize_text_field' ),
		'url'          => array( '', 'esc_url_raw' ),
		'devices'      => array( 'all', 'yefsw_fixed_cta_sanitize_devices' ),
		'width_pc'     => array( 320, 'yefsw_fixed_cta_sanitize_width_pc' ),
		'width_sp'     => array( 300, 'yefsw_fixed_cta_sanitize_width_sp' ),
		'font_pc'      => array( 16, 'yefsw_fixed_cta_sanitize_font_pc' ),
		'font_sp'      => array( 15, 'yefsw_fixed_cta_sanitize_font_sp' ),
		'height_pc'    => array( 52, 'yefsw_fixed_cta_sanitize_height_pc' ),
		'height_sp'    => array( 48, 'yefsw_fixed_cta_sanitize_height_sp' ),
		'bg_color'     => array( '', 'sanitize_hex_color' ),
		'text_color'   => array( '#ffffff', 'sanitize_hex_color' ),
		'border_color' => array( '', 'sanitize_hex_color' ),
		'border_width' => array( 0, 'yefsw_fixed_cta_sanitize_border_width' ),
		'radius'       => array( 999, 'yefsw_fixed_cta_sanitize_radius' ),
		'bottom_pc'    => array( 16, 'yefsw_fixed_cta_sanitize_bottom' ),
		'bottom_sp'    => array( 16, 'yefsw_fixed_cta_sanitize_bottom' ),
		'threshold'    => array( 300, 'yefsw_fixed_cta_sanitize_threshold' ),
		'animation'    => array( 'fade-slide', 'yefsw_fixed_cta_sanitize_animation' ),
		'new_tab'      => array( 0, 'yefsw_fixed_cta_sanitize_bool' ),
		'image_pc'     => array( 0, 'yefsw_fixed_cta_sanitize_image_id' ),
		'image_sp'     => array( 0, 'yefsw_fixed_cta_sanitize_image_id' ),
	);

	foreach ( $settings as $key => $setting ) {
		$wp_customize->add_setting(
			YEFSW_FIXED_CTA_PREFIX . $key,
			array(
				'default'           => $setting[0],
				'type'              => 'option',
				'transport'         => 'refresh',
				'sanitize_callback' => $setting[1],
			)
		);
	}

	$section  = 'yefsw_fixed_cta_section';
	$priority = 10;
	$controls = array(
		'enabled' => array( 'label' => '[Y] 固定CTAボタンを使用する', 'type' => 'checkbox' ),
		'label'   => array(
			'label'       => '[Y] ボタンのラベル',
			'type'        => 'text',
			'description' => '画像ボタンではリンクの読み上げ用テキストとして使用します。',
		),
		'url'     => array( 'label' => '[Y] リンク先URL', 'type' => 'url' ),
		'devices' => array(
			'label'   => '[Y] 表示端末',
			'type'    => 'radio',
			'choices' => array(
				'all' => 'PC・タブレットとスマホの両方',
				'pc'  => 'PC・タブレットのみ（600px以上）',
				'sp'  => 'スマホのみ（599px以下）',
			),
		),
		'width_pc' => array( 'label' => '[Y] PC・タブレットでの最大幅（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 160, 'max' => 640, 'step' => 1 ) ),
		'width_sp' => array(
			'label'       => '[Y] スマホでの最大幅（px）',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 200, 'max' => 360, 'step' => 1 ),
			'description' => '狭い画面ではSWELLのページ上部へ戻るボタンと重ならない幅まで自動的に縮小します。',
		),
		'font_pc'   => array( 'label' => '[Y] PC・タブレットでの文字サイズ（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 12, 'max' => 24, 'step' => 1 ) ),
		'font_sp'   => array( 'label' => '[Y] スマホでの文字サイズ（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 12, 'max' => 20, 'step' => 1 ) ),
		'height_pc' => array( 'label' => '[Y] PC・タブレットでの最小高さ（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 44, 'max' => 80, 'step' => 1 ) ),
		'height_sp' => array( 'label' => '[Y] スマホでの最小高さ（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 44, 'max' => 72, 'step' => 1 ) ),
		'border_width' => array(
			'label'       => '[Y] ボタンの枠線の太さ（px）',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 8, 'step' => 1 ),
			'description' => '0の場合は枠線を表示しません。',
		),
		'radius'    => array( 'label' => '[Y] ボタンの角丸（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 0, 'max' => 999, 'step' => 1 ) ),
		'bottom_pc' => array( 'label' => '[Y] PC・タブレットでの下端余白（px）', 'type' => 'number', 'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 1 ) ),
		'bottom_sp' => array(
			'label'       => '[Y] スマホでの下端余白（px）',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
			'description' => 'iPhoneなどのセーフエリアは自動的に加算します。',
		),
		'threshold' => array(
			'label'       => '[Y] CTAを表示し始めるスクロール位置（px）',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'step' => 1 ),
			'description' => 'ページ最上部からの距離です。0の場合は最初から表示します。',
		),
		'animation' => array(
			'label'   => '[Y] 表示・非表示のアニメーション',
			'type'    => 'select',
			'choices' => array(
				'none'       => 'アニメーションなし',
				'fade'       => 'フェード',
				'slide'      => 'スライド',
				'fade-slide' => 'フェード＋スライド',
			),
		),
		'new_tab' => array( 'label' => '[Y] リンクを新しいタブで開く', 'type' => 'checkbox' ),
	);

	foreach ( $controls as $key => $args ) {
		$args['section']  = $section;
		$args['priority'] = $priority;
		$wp_customize->add_control( YEFSW_FIXED_CTA_PREFIX . $key, $args );
		$priority += 10;
	}

	$colors = array(
		'bg_color'     => array( '[Y] ボタンの背景色', '未選択の場合はSWELLのメインカラーを使用します。' ),
		'text_color'   => array( '[Y] ボタンの文字色', '' ),
		'border_color' => array( '[Y] ボタンの枠線の色', '未選択の場合はボタンの文字色を使用します。' ),
	);
	foreach ( $colors as $key => $color ) {
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				YEFSW_FIXED_CTA_PREFIX . $key,
				array(
					'label' => $color[0], 'description' => $color[1], 'section' => $section, 'priority' => $priority,
				)
			)
		);
		$priority += 10;
	}

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			YEFSW_FIXED_CTA_PREFIX . 'image_pc',
			array(
				'label' => '[Y] CTAボタン画像（共通・PC）', 'description' => '設定するとCSSボタンより優先されます。',
				'section' => $section, 'priority' => $priority, 'mime_type' => 'image',
			)
		)
	);
	$priority += 10;
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			YEFSW_FIXED_CTA_PREFIX . 'image_sp',
			array(
				'label' => '[Y] CTAボタン画像（スマホ用・任意）',
				'description' => '599px以下で使用します。未設定の場合は共通・PC用画像を使用します。',
				'section' => $section, 'priority' => $priority, 'mime_type' => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'yefsw_fixed_cta_customize_register', 120 );

function yefsw_fixed_cta_register_meta() {
	register_post_meta(
		'page',
		YEFSW_FIXED_CTA_META,
		array(
			'type' => 'boolean', 'single' => true, 'default' => false, 'show_in_rest' => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback' => function() {
				return current_user_can( 'edit_pages' );
			},
		)
	);
}
add_action( 'init', 'yefsw_fixed_cta_register_meta' );

function yefsw_fixed_cta_enqueue_editor() {
	if ( ! yefsw_fixed_cta_is_swell() || ! yefsw_fixed_cta_sanitize_bool( get_option( YEFSW_FIXED_CTA_PREFIX . 'enabled', 0 ) ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'page' !== $screen->post_type || ! $screen->is_block_editor() ) {
		return;
	}
	$path = YEFSW_PLUGIN_DIR . 'assets/fixed-cta/editor.js';
	wp_enqueue_script(
		YEFSW_FIXED_CTA_HANDLE . '-editor',
		YEFSW_PLUGIN_URL . 'assets/fixed-cta/editor.js',
		array( 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-element', 'wp-components', 'wp-i18n' ),
		file_exists( $path ) ? (string) filemtime( $path ) : YEFSW_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'yefsw_fixed_cta_enqueue_editor' );

function yefsw_fixed_cta_should_render() {
	if ( is_admin() || ! yefsw_fixed_cta_is_swell() || ! is_page() ) {
		return false;
	}
	if ( ! yefsw_fixed_cta_sanitize_bool( get_option( YEFSW_FIXED_CTA_PREFIX . 'enabled', 0 ) ) ) {
		return false;
	}
	if ( ! yefsw_fixed_cta_sanitize_bool( get_post_meta( get_queried_object_id(), YEFSW_FIXED_CTA_META, true ) ) ) {
		return false;
	}
	return '' !== trim( (string) get_option( YEFSW_FIXED_CTA_PREFIX . 'url', '' ) );
}

/**
 * CTAを表示する固定ページでだけ公開用CSSを読み込む。
 */
function yefsw_fixed_cta_enqueue_style() {
	if ( ! yefsw_fixed_cta_should_render() ) {
		return;
	}

	wp_enqueue_style(
		YEFSW_FIXED_CTA_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/fixed-cta/front.css',
		array(),
		YEFSW_VERSION
	);
	wp_enqueue_script(
		YEFSW_FIXED_CTA_HANDLE,
		YEFSW_PLUGIN_URL . 'assets/fixed-cta/front.js',
		array(),
		YEFSW_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'yefsw_fixed_cta_enqueue_style', 20 );

function yefsw_fixed_cta_get_image( $attachment_id ) {
	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		return '';
	}
	return wp_get_attachment_image(
		$attachment_id,
		'full',
		false,
		array( 'alt' => '', 'loading' => 'eager', 'decoding' => 'async' )
	);
}

function yefsw_fixed_cta_render() {
	if ( ! yefsw_fixed_cta_should_render() ) {
		return;
	}

	$label        = trim( (string) get_option( YEFSW_FIXED_CTA_PREFIX . 'label', '詳しくはこちら' ) );
	$label        = '' !== $label ? $label : '詳しくはこちら';
	$url          = esc_url( get_option( YEFSW_FIXED_CTA_PREFIX . 'url', '' ) );
	$devices      = yefsw_fixed_cta_sanitize_devices( get_option( YEFSW_FIXED_CTA_PREFIX . 'devices', 'all' ) );
	$width_pc     = yefsw_fixed_cta_sanitize_width_pc( get_option( YEFSW_FIXED_CTA_PREFIX . 'width_pc', 320 ) );
	$width_sp     = yefsw_fixed_cta_sanitize_width_sp( get_option( YEFSW_FIXED_CTA_PREFIX . 'width_sp', 300 ) );
	$font_pc      = yefsw_fixed_cta_sanitize_font_pc( get_option( YEFSW_FIXED_CTA_PREFIX . 'font_pc', 16 ) );
	$font_sp      = yefsw_fixed_cta_sanitize_font_sp( get_option( YEFSW_FIXED_CTA_PREFIX . 'font_sp', 15 ) );
	$height_pc    = yefsw_fixed_cta_sanitize_height_pc( get_option( YEFSW_FIXED_CTA_PREFIX . 'height_pc', 52 ) );
	$height_sp    = yefsw_fixed_cta_sanitize_height_sp( get_option( YEFSW_FIXED_CTA_PREFIX . 'height_sp', 48 ) );
	$border_width = yefsw_fixed_cta_sanitize_border_width( get_option( YEFSW_FIXED_CTA_PREFIX . 'border_width', 0 ) );
	$radius       = yefsw_fixed_cta_sanitize_radius( get_option( YEFSW_FIXED_CTA_PREFIX . 'radius', 999 ) );
	$bottom_pc    = yefsw_fixed_cta_sanitize_bottom( get_option( YEFSW_FIXED_CTA_PREFIX . 'bottom_pc', 16 ) );
	$bottom_sp    = yefsw_fixed_cta_sanitize_bottom( get_option( YEFSW_FIXED_CTA_PREFIX . 'bottom_sp', 16 ) );
	$threshold    = yefsw_fixed_cta_sanitize_threshold( get_option( YEFSW_FIXED_CTA_PREFIX . 'threshold', 300 ) );
	$animation    = yefsw_fixed_cta_sanitize_animation( get_option( YEFSW_FIXED_CTA_PREFIX . 'animation', 'fade-slide' ) );
	$bg_color     = sanitize_hex_color( get_option( YEFSW_FIXED_CTA_PREFIX . 'bg_color', '' ) );
	$text_color   = sanitize_hex_color( get_option( YEFSW_FIXED_CTA_PREFIX . 'text_color', '#ffffff' ) );
	$border_color = sanitize_hex_color( get_option( YEFSW_FIXED_CTA_PREFIX . 'border_color', '' ) );
	$image_pc     = yefsw_fixed_cta_get_image( yefsw_fixed_cta_sanitize_image_id( get_option( YEFSW_FIXED_CTA_PREFIX . 'image_pc', 0 ) ) );
	$image_sp     = yefsw_fixed_cta_get_image( yefsw_fixed_cta_sanitize_image_id( get_option( YEFSW_FIXED_CTA_PREFIX . 'image_sp', 0 ) ) );
	$image_sp     = $image_sp ?: $image_pc;
	$new_tab      = yefsw_fixed_cta_sanitize_bool( get_option( YEFSW_FIXED_CTA_PREFIX . 'new_tab', 0 ) );

	$vars = array(
		'--yefsw-cta-width-pc:' . $width_pc . 'px', '--yefsw-cta-width-sp:' . $width_sp . 'px',
		'--yefsw-cta-font-pc:' . $font_pc . 'px', '--yefsw-cta-font-sp:' . $font_sp . 'px',
		'--yefsw-cta-height-pc:' . $height_pc . 'px', '--yefsw-cta-height-sp:' . $height_sp . 'px',
		'--yefsw-cta-border-width:' . $border_width . 'px', '--yefsw-cta-radius:' . $radius . 'px',
		'--yefsw-cta-bottom-pc:' . $bottom_pc . 'px', '--yefsw-cta-bottom-sp:' . $bottom_sp . 'px',
		'--yefsw-cta-bg:' . ( $bg_color ?: 'var(--color_main)' ),
		'--yefsw-cta-color:' . ( $text_color ?: '#fff' ),
		'--yefsw-cta-border-color:' . ( $border_color ?: 'currentColor' ),
	);
	$classes = array(
		'yefsw-fixed-cta', 'yefsw-fixed-cta--' . $devices, 'yefsw-fixed-cta--anim-' . $animation,
		$image_pc ? 'has-pc-image' : 'has-pc-css', $image_sp ? 'has-sp-image' : 'has-sp-css',
	);
	if ( 0 === $threshold ) {
		$classes[] = 'is-visible';
	}

	$target = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( implode( ';', $vars ) ); ?>" data-scroll-threshold="<?php echo esc_attr( $threshold ); ?>">
		<a class="yefsw-fixed-cta__link" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="yefsw-fixed-cta__visual yefsw-fixed-cta__visual--pc"><?php echo $image_pc ?: esc_html( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="yefsw-fixed-cta__visual yefsw-fixed-cta__visual--sp"><?php echo $image_sp ?: esc_html( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
	</div>
	<?php
}
add_action( 'wp_footer', 'yefsw_fixed_cta_render', 20 );
