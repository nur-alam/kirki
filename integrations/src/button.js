/**
 * Add custom button on Gutenberg header to open Tutor frontend
 * builder
 *
 * @since v2.0.5
 */

(function (window, wp) {
	const { __ } = wp.i18n;
	const buttonId = "driop-kirki-root";
	// prepare our custom link's html.
	const buttonHtml = `
        <button id="${buttonId}" class="tutor-btn tutor-btn-primary tutor-btn-sm" >
            ${__("Droip Templates", "tutor")}
        </button>
    `;

	// check if gutenberg's editor root element is present.
	const editorElement = document?.getElementById("editor");
	wp?.data?.subscribe(function () {
		setTimeout(function () {
			if (!document?.getElementById(buttonId)) {
				var toolbarElement = editorElement?.querySelector(
					".edit-post-header-toolbar"
				);
				if (toolbarElement instanceof HTMLElement) {
					toolbarElement?.insertAdjacentHTML("beforeend", buttonHtml);
				}
				const driopKirkiRoot = document?.getElementById("driop-kirki-root");
				const droipIntegrationsRoot = document?.querySelector(
					".droip-integrations-wrapper"
				);
				driopKirkiRoot?.addEventListener("click", (event) => {
					event.preventDefault();
					droipIntegrationsRoot.style.display = "flex";
				});
			}
		}, 100);
	});
})(window, wp);
