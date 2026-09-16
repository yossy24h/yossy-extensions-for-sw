(function () {
	function insertPcHeadBarMenu() {
		if (document.querySelector(".yefsw-pc-head-bar-nav")) {
			return;
		}

		var header = document.getElementById("header");
		if (!header || !window.yefswPcHeadBarMenu || !yefswPcHeadBarMenu.html) {
			return;
		}

		var html = yefswPcHeadBarMenu.html;
		var pcBar = header.querySelector(".l-header__bar.pc_");

		if (pcBar) {
			var inner = pcBar.querySelector(".l-header__barInner");
			if (!inner) {
				return;
			}

			var icons = inner.querySelector(".c-iconList");
			if (icons) {
				icons.insertAdjacentHTML("beforebegin", html);
			} else {
				inner.insertAdjacentHTML("beforeend", html);
			}
			return;
		}

		var bar = document.createElement("div");
		bar.className = "l-header__bar pc_ yefsw-pc-head-bar";
		if (yefswPcHeadBarMenu.bg) {
			bar.style.background = yefswPcHeadBarMenu.bg;
		}
		if (yefswPcHeadBarMenu.color) {
			bar.style.color = yefswPcHeadBarMenu.color;
		}
		bar.innerHTML = '<div class="l-header__barInner l-container"></div>';
		bar.querySelector(".l-header__barInner").insertAdjacentHTML("afterbegin", html);
		header.insertAdjacentElement("afterbegin", bar);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", insertPcHeadBarMenu);
	} else {
		insertPcHeadBarMenu();
	}
})();
