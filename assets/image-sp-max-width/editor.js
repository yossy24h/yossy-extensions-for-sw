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
	var BLOCK_NAME = 'core/image';
	var ATTR = 'yefswSpMaxWidth';
	var HOST_CLASS = 'yefsw-image-sp-max-width-host';

	function parseWidth(value) {
		var width = parseInt(value, 10);
		if (isNaN(width) || width <= 0) {
			return 0;
		}
		return Math.min(width, 9999);
	}

	function labelText(node) {
		return ((node && node.textContent) || '').replace(/\s+/g, ' ').trim();
	}

	function findControlByLabels(root, labels) {
		var i;
		for (i = 0; i < labels.length; i++) {
			var byAria = root.querySelector('[aria-label="' + labels[i] + '"]');
			if (byAria) {
				return byAria;
			}
		}

		var nodes = root.querySelectorAll(
			'label, .components-input-control__label, .components-base-control__label, legend, span'
		);
		for (i = 0; i < nodes.length; i++) {
			var text = labelText(nodes[i]);
			for (var j = 0; j < labels.length; j++) {
				if (text === labels[j] || text.indexOf(labels[j]) === 0) {
					return nodes[i];
				}
			}
		}

		return null;
	}

	function isToolsPanelItem(node) {
		if (!node || node.nodeType !== 1) {
			return false;
		}
		var className = ' ' + ((node.className && node.className.baseVal) || node.className || '') + ' ';
		return className.indexOf('tools-panel-item') !== -1 && className.indexOf('placeholder') === -1;
	}

	function closestToolsPanelItem(node) {
		var el = node;
		while (el && el !== document.body) {
			if (isToolsPanelItem(el)) {
				return el;
			}
			el = el.parentElement;
		}
		return null;
	}

	function findHeightPanelItem(root) {
		var height = findControlByLabels(root, ['高さ', 'Height']);
		if (!height) {
			return null;
		}

		var item = closestToolsPanelItem(height);
		if (item) {
			return item;
		}

		var el = height;
		while (el && el.parentElement && el.parentElement !== root) {
			var parent = el.parentElement;
			var display = window.getComputedStyle(parent).display;
			if (
				display === 'grid' &&
				parent.querySelector('[aria-label="幅"], [aria-label="Width"]')
			) {
				return el;
			}
			el = parent;
		}

		return null;
	}

	function panelTitle(root) {
		var heading = root.querySelector(
			'.components-panel__body-title, .components-tools-panel-header, .block-editor-tools-panel__label, legend, h2'
		);
		return labelText(heading);
	}

	function isSizePanel(root) {
		var titleText = panelTitle(root);
		if (titleText.indexOf('コンテンツ') !== -1) {
			return false;
		}
		return titleText.indexOf('サイズ') !== -1 || titleText.toLowerCase().indexOf('dimensions') !== -1;
	}

	function placeHostAfter(anchor, searchRoot) {
		if (!anchor || !anchor.parentNode || !searchRoot) {
			return null;
		}

		var host = searchRoot.querySelector('.' + HOST_CLASS);
		if (!host) {
			host = document.createElement('div');
			host.className = HOST_CLASS;
		}
		host.style.setProperty('grid-column', '1', 'important');
		if (host.previousElementSibling !== anchor) {
			anchor.after(host);
		}
		return host;
	}

	function findInsertHost() {
		var inspector = document.querySelector('.block-editor-block-inspector');
		if (!inspector) {
			return null;
		}

		var panels = inspector.querySelectorAll(
			'.components-tools-panel, .block-editor-tools-panel, .dimensions-block-support-panel, .components-panel__body'
		);
		var i;
		var sizeRoot = null;
		for (i = 0; i < panels.length; i++) {
			if (isSizePanel(panels[i])) {
				sizeRoot = panels[i];
				break;
			}
		}

		var searchRoot = sizeRoot || inspector;
		var heightItem = findHeightPanelItem(searchRoot);
		if (!heightItem) {
			return null;
		}

		var hostRoot =
			sizeRoot ||
			heightItem.closest(
				'.components-tools-panel, .block-editor-tools-panel, .dimensions-block-support-panel, .components-panel__body'
			) ||
			inspector;

		return placeHostAfter(heightItem, hostRoot);
	}

	function SpMaxWidthControl(props) {
		var attributes = props.attributes || {};
		var setAttributes = props.setAttributes;
		var clientId = props.clientId || 'image';
		var width = parseWidth(attributes[ATTR]);
		var inputId = 'yefsw-image-sp-max-width-' + clientId;

		return el(
			'div',
			{ className: 'yefsw-image-sp-max-width' },
			el(
				'label',
				{ className: 'yefsw-image-sp-max-width__label', htmlFor: inputId },
				'[Y]SPでの最大幅'
			),
			el(
				'div',
				{ className: 'yefsw-image-sp-max-width__row' },
				el('input', {
					id: inputId,
					className: 'yefsw-image-sp-max-width__input components-text-control__input',
					type: 'number',
					min: 0,
					step: 1,
					inputMode: 'numeric',
					placeholder: '',
					value: width > 0 ? String(width) : '',
					title: '599px以下の画面で、この画像の最大幅になります。空欄で解除。',
					onChange: function (event) {
						var next = parseWidth(event.target.value);
						var nextAttrs = {};
						nextAttrs[ATTR] = next;
						setAttributes(nextAttrs);
					},
				}),
				el('span', { className: 'yefsw-image-sp-max-width__unit' }, 'px')
			)
		);
	}

	function SpMaxWidthSlot(props) {
		var targetState = useState(null);
		var target = targetState[0];
		var setTarget = targetState[1];
		var fallbackState = useState(false);
		var showFallback = fallbackState[0];
		var setShowFallback = fallbackState[1];

		useEffect(
			function () {
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
			},
			[props.clientId]
		);

		var control = el(SpMaxWidthControl, props);

		if (target) {
			return createPortal(control, target);
		}

		if (showFallback && InspectorControls && PanelBody) {
			return el(
				InspectorControls,
				{ group: 'styles' },
				el(PanelBody, { title: '[Y]SPでの最大幅', initialOpen: true }, control)
			);
		}

		return null;
	}

	var withSpMaxWidthControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== BLOCK_NAME) {
				return el(BlockEdit, props);
			}

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				props.isSelected ? el(SpMaxWidthSlot, props) : null
			);
		};
	}, 'withYefswImageSpMaxWidth');

	addFilter('editor.BlockEdit', 'yefsw/image-sp-max-width/block-edit', withSpMaxWidthControls);

	addFilter(
		'blocks.registerBlockType',
		'yefsw/image-sp-max-width/attributes',
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

	var withSpMaxWidthPreview = createHigherOrderComponent(function (BlockListBlock) {
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
			var existingClass = [props.className, wrapperProps.className].filter(Boolean).join(' ');
			var className = (existingClass + ' has-yefsw-sp-max-width').replace(/^\s+/, '');
			wrapperProps.className = className;
			wrapperProps.style = Object.assign({}, wrapperProps.style || {}, {
				'--yefsw-img-sp-max-width': width + 'px',
			});

			return el(
				BlockListBlock,
				Object.assign({}, props, {
					className: className,
					wrapperProps: wrapperProps,
				})
			);
		};
	}, 'withYefswImageSpMaxWidthPreview');

	addFilter('editor.BlockListBlock', 'yefsw/image-sp-max-width/block-list', withSpMaxWidthPreview);
})(window.wp);
