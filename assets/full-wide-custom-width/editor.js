(function (wp) {
	'use strict';

	if (!wp || !wp.hooks || !wp.element || !wp.compose) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var createPortal = wp.element.createPortal;
	var useEffect = wp.element.useEffect;
	var useState = wp.element.useState;
	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
	var PanelBody = wp.components && wp.components.PanelBody;
	var BLOCK_NAME = 'loos/full-wide';
	var ATTR = 'yefswContentWidth';
	var HOST_CLASS = 'yefsw-fw-custom-width-host';

	function parseWidth(value) {
		var width = parseInt(value, 10);
		if (isNaN(width) || width <= 0) {
			return 0;
		}
		return Math.min(width, 9999);
	}

	function findInsertHost() {
		var inspector = document.querySelector('.block-editor-block-inspector');
		if (!inspector) {
			return null;
		}

		var bodies = inspector.querySelectorAll('.components-panel__body');
		var i;
		for (i = 0; i < bodies.length; i++) {
			var body = bodies[i];
			var title = body.querySelector('.components-panel__body-title');
			if (!title || title.textContent.indexOf('コンテンツサイズ') === -1) {
				continue;
			}

			var groups = body.querySelectorAll('.swl-btnGroup');
			var sizeGroup = null;
			var g;
			for (g = 0; g < groups.length; g++) {
				if (!groups[g].classList.contains('swl-btnGroup--minWidth')) {
					sizeGroup = groups[g];
					break;
				}
			}
			if (!sizeGroup) {
				continue;
			}

			var anchor = sizeGroup.closest('.components-base-control') || sizeGroup.parentElement;
			if (!anchor || !anchor.parentNode) {
				continue;
			}

			var host = body.querySelector('.' + HOST_CLASS);
			if (!host) {
				host = document.createElement('div');
				host.className = HOST_CLASS;
			}
			if (host.previousElementSibling !== anchor) {
				anchor.after(host);
			}
			return host;
		}

		return null;
	}

	function CustomWidthControl(props) {
		var attributes = props.attributes || {};
		var setAttributes = props.setAttributes;
		var clientId = props.clientId || 'fw';
		var width = parseWidth(attributes[ATTR]);
		var inputId = 'yefsw-fw-custom-width-' + clientId;

		return el(
			'div',
			{ className: 'yefsw-fw-custom-width' },
			el(
				'div',
				{ className: 'yefsw-fw-custom-width__row' },
				el(
					'label',
					{ className: 'yefsw-fw-custom-width__label', htmlFor: inputId },
					'[Y]カスタム幅：'
				),
				el('input', {
					id: inputId,
					className: 'yefsw-fw-custom-width__input components-text-control__input',
					type: 'number',
					min: 0,
					step: 1,
					inputMode: 'numeric',
					placeholder: '',
					value: width > 0 ? String(width) : '',
					title: '入力すると上の選択肢より優先されます。空欄で解除。',
					onChange: function (event) {
						var next = parseWidth(event.target.value);
						var nextAttrs = {};
						nextAttrs[ATTR] = next;
						setAttributes(nextAttrs);
					},
				}),
				el('span', { className: 'yefsw-fw-custom-width__unit' }, 'px')
			)
		);
	}

	function CustomWidthSlot(props) {
		var targetState = useState(null);
		var target = targetState[0];
		var setTarget = targetState[1];
		var fallbackState = useState(false);
		var showFallback = fallbackState[0];
		var setShowFallback = fallbackState[1];

		useEffect(function () {
			var cancelled = false;
			var observer;
			var timeoutId;
			var root;

			function attach() {
				if (cancelled) {
					return;
				}
				var host = findInsertHost();
				setTarget(function (current) {
					return current === host ? current : host;
				});
			}

			attach();
			timeoutId = window.setTimeout(function () {
				if (!cancelled) {
					setShowFallback(true);
				}
			}, 700);

			root =
				document.querySelector('.interface-interface-skeleton__sidebar') ||
				document.querySelector('.block-editor-block-inspector') ||
				document.body;
			observer = new MutationObserver(attach);
			observer.observe(root, { childList: true, subtree: true });

			return function () {
				cancelled = true;
				window.clearTimeout(timeoutId);
				if (observer) {
					observer.disconnect();
				}
			};
		}, [props.clientId]);

		var control = el(CustomWidthControl, props);

		if (target) {
			return createPortal(control, target);
		}

		if (showFallback && PanelBody) {
			return el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: '[Y]カスタム幅', initialOpen: true },
					control
				)
			);
		}

		return null;
	}

	var withCustomWidthControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== BLOCK_NAME) {
				return el(BlockEdit, props);
			}

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				props.isSelected ? el(CustomWidthSlot, props) : null
			);
		};
	}, 'withYefswFullWideCustomWidth');

	addFilter(
		'editor.BlockEdit',
		'yefsw/full-wide-custom-width/block-edit',
		withCustomWidthControls
	);

	addFilter(
		'blocks.registerBlockType',
		'yefsw/full-wide-custom-width/attributes',
		function (settings, name) {
			if (name !== BLOCK_NAME) {
				return settings;
			}

			var attributes = settings.attributes || {};
			if (attributes[ATTR]) {
				return settings;
			}

			var next = Object.assign({}, settings);
			next.attributes = Object.assign({}, attributes);
			next.attributes[ATTR] = {
				type: 'integer',
				default: 0,
			};
			return next;
		}
	);

	var withCustomWidthPreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			var name = props.name || (props.block && props.block.name);
			if (name !== BLOCK_NAME) {
				return el(BlockListBlock, props);
			}

			var attributes = props.attributes || {};
			var width = parseWidth(attributes[ATTR]);
			if (!width) {
				return el(BlockListBlock, props);
			}

			var wrapperProps = Object.assign({}, props.wrapperProps || {});
			var existingClass = [props.className, wrapperProps.className]
				.filter(Boolean)
				.join(' ');
			var className = (existingClass + ' has-yefsw-custom-width').replace(/^\s+/, '');
			wrapperProps.className = className;
			wrapperProps.style = Object.assign({}, wrapperProps.style || {}, {
				'--yefsw-fw-content-width': width + 'px',
			});

			return el(
				BlockListBlock,
				Object.assign({}, props, {
					className: className,
					wrapperProps: wrapperProps,
				})
			);
		};
	}, 'withYefswFullWideCustomWidthPreview');

	addFilter(
		'editor.BlockListBlock',
		'yefsw/full-wide-custom-width/block-list',
		withCustomWidthPreview
	);
})(window.wp);
