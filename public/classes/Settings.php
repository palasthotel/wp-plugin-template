<?php

namespace Palasthotel\WordPress\MyPlugin;

/**
 * Settings page under Settings > My Plugin. All values live in one option array,
 * Plugin::OPTION_SETTINGS, so uninstall.php has a single option to delete.
 */
class Settings extends Components\Component {

	const PAGE = "my-plugin";

	const DEFAULTS = [
		"enabled" => false,
		"label"   => "",
	];

	public function onCreate() {
		add_filter( 'plugin_action_links_' . $this->plugin->basename, [ $this, 'add_action_links' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * @return mixed
	 */
	public static function get( string $key ) {
		$settings = wp_parse_args( get_option( Plugin::OPTION_SETTINGS, [] ), self::DEFAULTS );

		return $settings[ $key ] ?? null;
	}

	/**
	 * action link to settings on plugins list page
	 */
	public function add_action_links( array $links ): array {
		return array_merge(
			$links,
			[
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ),
					esc_html__( 'Settings', 'my-plugin' )
				),
			]
		);
	}

	public function admin_menu() {
		add_options_page(
			"My Plugin",
			"My Plugin",
			"manage_options",
			self::PAGE,
			[ $this, 'render_settings_form' ]
		);
	}

	public function register_settings() {

		register_setting(
			self::PAGE,
			Plugin::OPTION_SETTINGS,
			[
				"type"              => "object",
				"default"           => self::DEFAULTS,
				"sanitize_callback" => [ $this, 'sanitize' ],
			]
		);

		add_settings_section(
			'my-plugin-general',
			__( 'General', 'my-plugin' ),
			null,
			self::PAGE
		);

		add_settings_field(
			"enabled",
			__( 'Enabled', 'my-plugin' ),
			[ $this, 'render_enabled' ],
			self::PAGE,
			'my-plugin-general'
		);

		add_settings_field(
			"label",
			__( 'Label', 'my-plugin' ),
			[ $this, 'render_label' ],
			self::PAGE,
			'my-plugin-general'
		);
	}

	/**
	 * Everything from the form is untrusted: keep only known keys, cast every value.
	 *
	 * @param mixed $value
	 */
	public function sanitize( $value ): array {
		$value = is_array( $value ) ? $value : [];

		return [
			"enabled" => ! empty( $value["enabled"] ),
			"label"   => sanitize_text_field( $value["label"] ?? "" ),
		];
	}

	public function render_settings_form() {
		?>
		<div class="wrap">
			<h1>My Plugin</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function render_enabled() {
		printf(
			"<input type='checkbox' name='%s[enabled]' value='1' %s />",
			esc_attr( Plugin::OPTION_SETTINGS ),
			checked( self::get( "enabled" ), true, false )
		);
	}

	public function render_label() {
		printf(
			"<input type='text' class='regular-text' name='%s[label]' value='%s' />",
			esc_attr( Plugin::OPTION_SETTINGS ),
			esc_attr( self::get( "label" ) )
		);
		printf(
			"<p class='description'>%s</p>",
			esc_html__( 'An example text setting.', 'my-plugin' )
		);
	}

}
