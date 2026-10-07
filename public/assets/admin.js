// Plain JavaScript, no jQuery and no build step. Switch to TypeScript with
// @wordpress/scripts once there is more than a few files - see CONTRIBUTING.md.
document.addEventListener("DOMContentLoaded", () => {
	const { i18n } = window.MyPlugin;
	const label = document.querySelector('input[name="my_plugin_settings[label]"]');
	if (!label) {
		return;
	}
	const hint = document.createElement("p");
	hint.className = "my-plugin-hint";
	hint.textContent = i18n.label_empty;
	label.after(hint);

	const update = () => {
		hint.hidden = label.value.trim() !== "";
	};
	label.addEventListener("input", update);
	update();
});
