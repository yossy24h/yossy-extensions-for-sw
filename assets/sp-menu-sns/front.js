(function () {
	function insertSnsList() {
		var body = document.querySelector(".p-spMenu__body");
		if (!body || body.querySelector(".yefsw-sp-menu-sns")) {
			return;
		}

		if (!window.yefswSpMenuSns || !yefswSpMenuSns.html) {
			return;
		}

		body.insertAdjacentHTML("beforeend", yefswSpMenuSns.html);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", insertSnsList);
	} else {
		insertSnsList();
	}
})();
