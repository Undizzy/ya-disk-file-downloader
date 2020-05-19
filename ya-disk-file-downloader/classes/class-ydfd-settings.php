<?php
/**
 * Class YDFD_Settings file.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Yandex Disc File Downloader Settings
 */
class YDFD_Settings {
    /**
	 * Page Title for plugin
	 */
	private $plugin_page_title = 'Yandex Disc File Downloader:';

	/**
	 * Menu Title for plugin
	 */
	private $plugin_menu_title = 'YDFD';

	/**
	 * Menu Slug for plugin
	 */
	private $plugin_menu_slug = 'YDFD';

	/**
	 * Icon for plugin menu
	 */
	private $plugin_icon_url = 'dashicons-download';

	public function __construct() {
	}

	/**
	 * Добавляет страницы опций и получения файла админ.меню
	 */
	public function YAFD_admin_menu() {
		$admin_output = new YDFD_Output();

		add_menu_page( $this->plugin_page_title, $this->plugin_menu_title, 'manage_options',
			$this->plugin_menu_slug, array($admin_output, 'YDFD_start'), $this->plugin_icon_url );
	}

	/**
	 * Регистрируем настройки.
	 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
	 */
	public function YDFD_register_settings(){
		// параметры: $option_group, $option_name, $sanitize_callback
		register_setting( 'option_group', 'option_name' );

		// параметры: $id, $title, $callback, $page
		add_settings_section( 'section_id', __('Basic settings', 'ydfd'), '', 'YDFD_page' );

		// параметры: $id, $title, $callback, $page, $section, $args
		add_settings_field('YDFD_disc_url', __('Link to Ya.Disk', 'ydfd'), array($this, 'YDFD_disc_url'), 'YDFD_page', 'section_id' );
		add_settings_field('YDFD_send_email', __('Email to send reports', 'ydfd'), array($this, 'YDFD_send_email_callback'), 'YDFD_page', 'section_id' );
	}

	## Заполняем опцию 1
	public function YDFD_disc_url(){
		$val = get_option('option_name');
		$val = $val ? $val['input'] : null;
		?>
		<input id="ya-disc-url" type="text" name="option_name[input]" value="<?php echo esc_attr( $val ) ?>" size="50"/>
        <p><label for="ya-disc-url"><?php _e('The public URL of the file on Ya.Disk for regular receipt of the file. This file can be downloaded on assignment in WP-Crone.', 'ydfd') ?></label></p>
        <p><code><i><?php _e('If you need to get the file once, you can do it on the tab "Actions"', 'ydfd') ?></i></code></p>
		<?php
	}
	## Заполняем опцию 2
		public function YDFD_send_email_callback(){
			$val = get_option('option_name');
		$val = $val ? $val['email'] : null;
		?>
		<input id="ya-disc-email" type="text" name="option_name[email]" value="<?php echo esc_attr( $val ) ?>" size="50"/>
        <p><label for="ya-disc-email"><?php _e('If you want to receive notifications by e-mail about the file download status - enter your e-mail here', 'ydfd') ?></label></p>
        <p><code><i><?php _e('This can be useful when uploading a file through a task in WP-Crone.', 'ydfd'); ?></i></code></p>
		<?php
	}

    public function plugin_lang() {
	    load_plugin_textdomain( 'ydfd', false, dirname( plugin_basename(YDFD_FILE) ) . '/languages' );
    }



}