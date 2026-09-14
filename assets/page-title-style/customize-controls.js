(function ($) {
	'use strict';

	function bindVisibility() {
		var setting = wp.customize('loos_customizer[page_title_pos]');
		if (!setting) {
			return;
		}

		var controlIds = [
			'yefsw_page_title_align',
			'yefsw_page_title_fz_pc',
			'yefsw_page_title_fz_sp',
		];

		function toggle() {
			var isTop = 'top' === setting.get();
			controlIds.forEach(function (id) {
				var control = wp.customize.control(id);
				if (control) {
					control.active.set(isTop);
				}
			});
		}

		toggle();
		setting.bind(toggle);
	}

	wp.customize.bind('ready', bindVisibility);
})(jQuery);
