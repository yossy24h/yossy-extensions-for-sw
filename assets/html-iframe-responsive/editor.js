(function (wp) {
	'use strict';

	if (!wp || !wp.hooks || !wp.element || !wp.compose) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
	var PanelBody = wp.components && wp.components.PanelBody;
	var ToggleControl = wp.components && wp.components.ToggleControl;
	var BLOCK_NAME = 'core/html';
	var RESP_ATTR = 'yefswHtmlResponsive';
	var W_ATTR = 'yefswHtmlAspectW';
	var H_ATTR = 'yefswHtmlAspectH';
	var SP_ATTR = 'yefswHtmlAspectSp';
	var W_SP_ATTR = 'yefswHtmlAspectWSp';
	var H_SP_ATTR = 'yefswHtmlAspectHSp';

	function parseRatio(value, emptyValue) {
		if (value === '' || value === null || typeof value === 'undefined') {
			return emptyValue;
		}

		var next = parseInt(value, 10);
		if (isNaN(next) || next < 1) {
			return 0;
		}

		return Math.min(next, 9999);
	}

	function isActive(attributes) {
		var attrs = attributes || {};
		if (!attrs[RESP_ATTR]) {
			return false;
		}
		return parseRatio(attrs[W_ATTR], 0) > 0 && parseRatio(attrs[H_ATTR], 0) > 0;
	}

	function hasSpRatio(attributes) {
		var attrs = attributes || {};
		if (!isActive(attrs) || !attrs[SP_ATTR]) {
			return false;
		}
		return parseRatio(attrs[W_SP_ATTR], 0) > 0 && parseRatio(attrs[H_SP_ATTR], 0) > 0;
	}

	function RatioFields(props) {
		var label = props.label;
		var widthId = props.widthId;
		var heightId = props.heightId;
		var width = props.width;
		var height = props.height;
		var widthLabel = props.widthLabel;
		var heightLabel = props.heightLabel;
		var onWidth = props.onWidth;
		var onHeight = props.onHeight;

		return el(
			'div',
			{ className: 'yefsw-html-responsive-panel__ratio' },
			el('span', { className: 'yefsw-html-responsive-panel__label' }, label),
			el('input', {
				id: widthId,
				className: 'yefsw-html-responsive-panel__input components-text-control__input',
				type: 'number',
				min: 1,
				step: 1,
				inputMode: 'numeric',
				value: String(width),
				title: '1以上の整数',
				'aria-label': widthLabel,
				onChange: function (event) {
					var next = parseRatio(event.target.value, 0);
					if (next < 1) {
						return;
					}
					onWidth(next);
				},
			}),
			el('span', { className: 'yefsw-html-responsive-panel__sep', 'aria-hidden': 'true' }, ':'),
			el('input', {
				id: heightId,
				className: 'yefsw-html-responsive-panel__input components-text-control__input',
				type: 'number',
				min: 1,
				step: 1,
				inputMode: 'numeric',
				value: String(height),
				title: '1以上の整数',
				'aria-label': heightLabel,
				onChange: function (event) {
					var next = parseRatio(event.target.value, 0);
					if (next < 1) {
						return;
					}
					onHeight(next);
				},
			})
		);
	}

	function ResponsiveControls(props) {
		var attributes = props.attributes || {};
		var setAttributes = props.setAttributes;
		var clientId = props.clientId || 'html';
		var enabled = !!attributes[RESP_ATTR];
		var spEnabled = !!attributes[SP_ATTR];
		var width = parseRatio(attributes[W_ATTR], 16) || 16;
		var height = parseRatio(attributes[H_ATTR], 9) || 9;
		var spWidth = parseRatio(attributes[W_SP_ATTR], 16) || 16;
		var spHeight = parseRatio(attributes[H_SP_ATTR], 9) || 9;

		return el(
			'div',
			{ className: 'yefsw-html-responsive-panel' },
			ToggleControl
				? el(ToggleControl, {
						label: '[Y]レスポンシブ対応',
						checked: enabled,
						onChange: function (next) {
							var nextAttrs = {};
							nextAttrs[RESP_ATTR] = !!next;
							if (next) {
								nextAttrs[W_ATTR] = parseRatio(attributes[W_ATTR], 16) || 16;
								nextAttrs[H_ATTR] = parseRatio(attributes[H_ATTR], 9) || 9;
							}
							setAttributes(nextAttrs);
						},
				  })
				: null,
			enabled
				? el(
						Fragment,
						null,
						el(RatioFields, {
							label: '[Y]縦横比',
							widthId: 'yefsw-html-aspect-w-' + clientId,
							heightId: 'yefsw-html-aspect-h-' + clientId,
							width: width,
							height: height,
							widthLabel: '縦横比の幅',
							heightLabel: '縦横比の高さ',
							onWidth: function (next) {
								var nextAttrs = {};
								nextAttrs[W_ATTR] = next;
								setAttributes(nextAttrs);
							},
							onHeight: function (next) {
								var nextAttrs = {};
								nextAttrs[H_ATTR] = next;
								setAttributes(nextAttrs);
							},
						}),
						el(
							'div',
							{ className: 'yefsw-html-responsive-panel__sp' },
							ToggleControl
								? el(ToggleControl, {
										label: '[Y]SPは別の縦横比を使用する',
										checked: spEnabled,
										onChange: function (next) {
											var nextAttrs = {};
											nextAttrs[SP_ATTR] = !!next;
											if (next) {
												nextAttrs[W_SP_ATTR] = parseRatio(attributes[W_SP_ATTR], 16) || 16;
												nextAttrs[H_SP_ATTR] = parseRatio(attributes[H_SP_ATTR], 9) || 9;
											}
											setAttributes(nextAttrs);
										},
								  })
								: null,
							spEnabled
								? el(RatioFields, {
										label: '[Y]SP縦横比',
										widthId: 'yefsw-html-aspect-w-sp-' + clientId,
										heightId: 'yefsw-html-aspect-h-sp-' + clientId,
										width: spWidth,
										height: spHeight,
										widthLabel: 'SP縦横比の幅',
										heightLabel: 'SP縦横比の高さ',
										onWidth: function (next) {
											var nextAttrs = {};
											nextAttrs[W_SP_ATTR] = next;
											setAttributes(nextAttrs);
										},
										onHeight: function (next) {
											var nextAttrs = {};
											nextAttrs[H_SP_ATTR] = next;
											setAttributes(nextAttrs);
										},
								  })
								: null
						)
				  )
				: null
		);
	}

	var withResponsiveControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== BLOCK_NAME || !InspectorControls) {
				return el(BlockEdit, props);
			}

			if (!props.isSelected) {
				return el(BlockEdit, props);
			}

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					InspectorControls,
					null,
					PanelBody
						? el(
								PanelBody,
								{ title: '[Y]レスポンシブ', initialOpen: true },
								el(ResponsiveControls, props)
							)
						: el(ResponsiveControls, props)
				)
			);
		};
	}, 'withYefswHtmlResponsive');

	addFilter('editor.BlockEdit', 'yefsw/html-iframe-responsive/block-edit', withResponsiveControls);

	addFilter(
		'blocks.registerBlockType',
		'yefsw/html-iframe-responsive/attributes',
		function (settings, name) {
			if (name !== BLOCK_NAME) {
				return settings;
			}

			var attributes = settings.attributes || {};
			var next = Object.assign({}, settings);
			next.attributes = Object.assign({}, attributes);

			if (!next.attributes[RESP_ATTR]) {
				next.attributes[RESP_ATTR] = {
					type: 'boolean',
					default: false,
				};
			}

			if (!next.attributes[W_ATTR]) {
				next.attributes[W_ATTR] = {
					type: 'integer',
					default: 16,
				};
			}

			if (!next.attributes[H_ATTR]) {
				next.attributes[H_ATTR] = {
					type: 'integer',
					default: 9,
				};
			}

			if (!next.attributes[SP_ATTR]) {
				next.attributes[SP_ATTR] = {
					type: 'boolean',
					default: false,
				};
			}

			if (!next.attributes[W_SP_ATTR]) {
				next.attributes[W_SP_ATTR] = {
					type: 'integer',
					default: 16,
				};
			}

			if (!next.attributes[H_SP_ATTR]) {
				next.attributes[H_SP_ATTR] = {
					type: 'integer',
					default: 9,
				};
			}

			return next;
		}
	);

	var withResponsivePreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			var name = props.name || (props.block && props.block.name);
			if (name !== BLOCK_NAME) {
				return el(BlockListBlock, props);
			}

			var attributes = props.attributes || {};
			if (!isActive(attributes)) {
				return el(BlockListBlock, props);
			}

			var width = parseRatio(attributes[W_ATTR], 16) || 16;
			var height = parseRatio(attributes[H_ATTR], 9) || 9;
			var wrapperProps = Object.assign({}, props.wrapperProps || {});
			var existingClass = [props.className, wrapperProps.className].filter(Boolean).join(' ');
			var className = (existingClass + ' has-yefsw-html-responsive').replace(/^\s+/, '');
			var style = {
				'--yefsw-aspect-w': String(width),
				'--yefsw-aspect-h': String(height),
			};

			if (hasSpRatio(attributes)) {
				className += ' has-yefsw-html-aspect-sp';
				style['--yefsw-aspect-w-sp'] = String(parseRatio(attributes[W_SP_ATTR], 16) || 16);
				style['--yefsw-aspect-h-sp'] = String(parseRatio(attributes[H_SP_ATTR], 9) || 9);
			}

			wrapperProps.className = className;
			wrapperProps.style = Object.assign({}, wrapperProps.style || {}, style);

			return el(
				BlockListBlock,
				Object.assign({}, props, {
					className: className,
					wrapperProps: wrapperProps,
				})
			);
		};
	}, 'withYefswHtmlResponsivePreview');

	addFilter('editor.BlockListBlock', 'yefsw/html-iframe-responsive/block-list', withResponsivePreview);
})(window.wp);
