(function () {
	'use strict';

	function updateSnsList(enabled) {
		var body = document.querySelector('.p-spMenu__body');
		if (!body) {
			return;
		}

		var list = body.querySelector('.yefsw-sp-menu-sns');
		if (!enabled) {
			if (list) {
				list.remove();
			}
			return;
		}

		if (!list && window.yefswSpMenuSns && yefswSpMenuSns.html) {
			body.insertAdjacentHTML('beforeend', yefswSpMenuSns.html);
		}
	}

	function init() {
		updateSnsList(!!(window.yefswSpMenuSns && yefswSpMenuSns.enabled));

		if (window.wp && wp.customize) {
			wp.customize('yefsw_show_sp_menu_sns', function (setting) {
				updateSnsList(setting.get());
				setting.bind(updateSnsList);
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
