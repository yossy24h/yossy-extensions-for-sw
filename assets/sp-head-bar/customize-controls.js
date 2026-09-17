(function ($) {
	'use strict';

	function bindVisibility() {
		var setting = wp.customize('yefsw_show_sp_head_bar');
		if (!setting) {
			return;
		}

		function toggle() {
			var control = wp.customize.control('yefsw_sp_head_bar_fz');
			if (control) {
				control.active.set(!!setting.get());
			}
		}

		toggle();
		setting.bind(toggle);
	}

	wp.customize.bind('ready', bindVisibility);
})(jQuery);
