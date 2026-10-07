( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.data || ! wp.element || ! wp.components ) {
		return;
	}

	const el = wp.element.createElement;
	const useSelect = wp.data.useSelect;
	const useDispatch = wp.data.useDispatch;
	const PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	const ToggleControl = wp.components.ToggleControl;
	const __ = wp.i18n.__;

	function FixedCtaPanel() {
		const editor = useSelect( function ( select ) {
			const store = select( 'core/editor' );
			return {
				postType: store.getCurrentPostType(),
				meta: store.getEditedPostAttribute( 'meta' ) || {},
			};
		}, [] );
		const editPost = useDispatch( 'core/editor' ).editPost;

		if ( 'page' !== editor.postType ) {
			return null;
		}

		const enabled = Boolean( editor.meta._yefsw_fixed_cta_enabled );
		return el(
			PluginDocumentSettingPanel,
			{
				name: 'yefsw-fixed-cta',
				title: __( '[Y] 固定CTA', 'yossy-extensions-for-sw' ),
				className: 'yefsw-fixed-cta-panel',
			},
			el( ToggleControl, {
				label: __( 'このページに固定CTAボタンを表示する', 'yossy-extensions-for-sw' ),
				checked: enabled,
				onChange: function ( value ) {
					editPost( {
						meta: Object.assign( {}, editor.meta, {
							_yefsw_fixed_cta_enabled: Boolean( value ),
						} ),
					} );
				},
			} )
		);
	}

	wp.plugins.registerPlugin( 'yefsw-fixed-cta', {
		render: FixedCtaPanel,
		icon: 'megaphone',
	} );
} )( window.wp );
