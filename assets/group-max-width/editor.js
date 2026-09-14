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
	var BLOCK_NAME = 'core/group';
	var ATTR = 'yefswMaxWidth';
	var ALIGN_ATTR = 'yefswMaxWidthAlign';
	var ALIGN_OPTIONS = [
		{ value: 'left', label: '左' },
		{ value: 'center', label: '中央' },
		{ value: 'right', label: '右' },
	];

	function parseWidth(value) {
		var width = parseInt(value, 10);
		if (isNaN(width) || width <= 0) {
			return 0;
		}
		return Math.min(width, 9999);
	}

	function parseAlign(value) {
		if (value === 'left' || value === 'right') {
			return value;
		}
		return 'center';
	}

	function alignMargins(align) {
		var articleEdge = 'max(0px, calc((100% - var(--block_max_width, 900px)) / 2))';

		if (align === 'left') {
			return { marginLeft: articleEdge, marginRight: 'auto' };
		}
		if (align === 'right') {
			return { marginLeft: 'auto', marginRight: articleEdge };
		}
		return { marginLeft: 'auto', marginRight: 'auto' };
	}

	function isPlainGroup(attributes) {
		var layout = (attributes && attributes.layout) || {};
		var type = layout.type;
		return type !== 'flex' && type !== 'grid';
	}

	function MaxWidthControl(props) {
		var attributes = props.attributes || {};
		var setAttributes = props.setAttributes;
		var clientId = props.clientId || 'group';
		var width = parseWidth(attributes[ATTR]);
		var align = parseAlign(attributes[ALIGN_ATTR]);
		var inputId = 'yefsw-group-max-width-' + clientId;

		return el(
			'div',
			{ className: 'yefsw-group-max-width' },
			el(
				'div',
				{ className: 'yefsw-group-max-width__row' },
				el(
					'label',
					{ className: 'yefsw-group-max-width__label', htmlFor: inputId },
					'[Y]最大幅：'
				),
				el('input', {
					id: inputId,
					className: 'yefsw-group-max-width__input components-text-control__input',
					type: 'number',
					min: 0,
					step: 1,
					inputMode: 'numeric',
					placeholder: '',
					value: width > 0 ? String(width) : '',
					title: '入力するとこのグループの最大横幅になります。空欄で解除。',
					onChange: function (event) {
						var next = parseWidth(event.target.value);
						var nextAttrs = {};
						nextAttrs[ATTR] = next;
						setAttributes(nextAttrs);
					},
				}),
				el('span', { className: 'yefsw-group-max-width__unit' }, 'px')
			),
			width
				? el(
						'div',
						{ className: 'yefsw-group-max-width__row yefsw-group-max-width__align' },
						el('span', { className: 'yefsw-group-max-width__label' }, '[Y]揃え：'),
						el(
							'div',
							{ className: 'yefsw-group-max-width__btns', role: 'group' },
							ALIGN_OPTIONS.map(function (opt) {
								return el(
									'button',
									{
										type: 'button',
										key: opt.value,
										className:
											'yefsw-group-max-width__btn' + (align === opt.value ? ' is-pressed' : ''),
										'aria-pressed': align === opt.value ? 'true' : 'false',
										onClick: function () {
											var nextAttrs = {};
											nextAttrs[ALIGN_ATTR] = opt.value;
											setAttributes(nextAttrs);
										},
									},
									opt.label
								);
							})
						)
					)
				: null
		);
	}

	var withMaxWidthControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== BLOCK_NAME || !isPlainGroup(props.attributes) || !InspectorControls) {
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
					{ group: 'styles' },
					PanelBody
						? el(
								PanelBody,
								{ title: '[Y]最大幅', initialOpen: true },
								el(MaxWidthControl, props)
							)
						: el(MaxWidthControl, props)
				)
			);
		};
	}, 'withYefswGroupMaxWidth');

	addFilter('editor.BlockEdit', 'yefsw/group-max-width/block-edit', withMaxWidthControls);

	addFilter(
		'blocks.registerBlockType',
		'yefsw/group-max-width/attributes',
		function (settings, name) {
			if (name !== BLOCK_NAME) {
				return settings;
			}

			var attributes = settings.attributes || {};
			var next = Object.assign({}, settings);
			next.attributes = Object.assign({}, attributes);

			if (!next.attributes[ATTR]) {
				next.attributes[ATTR] = {
					type: 'integer',
					default: 0,
				};
			}

			if (!next.attributes[ALIGN_ATTR]) {
				next.attributes[ALIGN_ATTR] = {
					type: 'string',
					default: 'center',
				};
			}

			return next;
		}
	);

	var withMaxWidthPreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			var name = props.name || (props.block && props.block.name);
			if (name !== BLOCK_NAME) {
				return el(BlockListBlock, props);
			}

			var attributes = props.attributes || {};
			if (!isPlainGroup(attributes)) {
				return el(BlockListBlock, props);
			}

			var width = parseWidth(attributes[ATTR]);
			if (!width) {
				return el(BlockListBlock, props);
			}

			var align = parseAlign(attributes[ALIGN_ATTR]);
			var wrapperProps = Object.assign({}, props.wrapperProps || {});
			var existingClass = [props.className, wrapperProps.className].filter(Boolean).join(' ');
			var className = (existingClass + ' has-yefsw-max-width has-yefsw-align-' + align).replace(/^\s+/, '');
			wrapperProps.className = className;
			wrapperProps.style = Object.assign({}, wrapperProps.style || {}, alignMargins(align), {
				'--yefsw-group-max-width': width + 'px',
				maxWidth: width + 'px',
				width: '100%',
				boxSizing: 'border-box',
			});

			return el(
				BlockListBlock,
				Object.assign({}, props, {
					className: className,
					wrapperProps: wrapperProps,
				})
			);
		};
	}, 'withYefswGroupMaxWidthPreview');

	addFilter('editor.BlockListBlock', 'yefsw/group-max-width/block-list', withMaxWidthPreview);
})(window.wp);
