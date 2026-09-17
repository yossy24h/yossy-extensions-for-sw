(function ($) {
	'use strict';

	function bindVisibility() {
		var setting = wp.customize('yefsw_sp_menu_hide_border');
		var control = wp.customize.control('yefsw_sp_menu_border_color');
		if (!setting || !control) {
			return;
		}

		// プレビューからの active 状態の同期でも、現在のチェック状態を優先する。
		control.active.validate = function () {
			return !setting.get();
		};

		function toggle() {
			var show = !setting.get();
			control.active.set(show);
		}

		toggle();
		setting.bind(toggle);
	}

	wp.customize.bind('ready', bindVisibility);
})(jQuery);
