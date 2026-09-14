(function () {
	function insertSpHeadBar() {
		if (document.querySelector(".yefsw-sp-head-bar")) {
			return;
		}

		var header = document.getElementById("header");
		if (!header || !window.yefswSpHeadBar || !yefswSpHeadBar.html) {
			return;
		}

		header.insertAdjacentHTML("afterbegin", yefswSpHeadBar.html);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", insertSpHeadBar);
	} else {
		insertSpHeadBar();
	}
})();
