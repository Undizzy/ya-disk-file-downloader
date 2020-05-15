<?php


class YDFD_Settings {
	public function __construct() {
	}

	/**
	 * Page Title for plugin
	 */
	private $plugin_page_title = 'Yandex Disc file downloader:';

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
	private $plugin_icon_url = 'dashicons-editor-bold';

	/**
	 * Добавляем страницы опций и получения файла админ.меню
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
		add_settings_section( 'section_id', 'Основные настройки', '', 'YDFD_page' );

		// параметры: $id, $title, $callback, $page, $section, $args
		add_settings_field('primer_field1', 'Ссылка на Я.Диск', array($this, 'fill_primer_field1'), 'YDFD_page', 'section_id' );
		add_settings_field('YDFD_send_email', 'Email для отправки отчётов', array($this, 'YDFD_send_email_callback'), 'YDFD_page', 'section_id' );
	}

	## Заполняем опцию 1
	public function fill_primer_field1(){
		$val = get_option('option_name');
		$val = $val ? $val['input'] : null;
		?>
		<input id="ya-disc-url" type="text" name="option_name[input]" value="<?php echo esc_attr( $val ) ?>" size="50"/>
        <p><label for="ya-disc-url">Публичный URL файла на Я.Диске для регулярного получения файла. Этот файл может скачиваться по заданию в WP-Crone.</label></p>
        <p><code><i>Если вам необходипо получить файл один раз, это можно сделать на вкладке "Действия"</i></code></p>
		<?php
	}
	## Заполняем опцию 2
		public function YDFD_send_email_callback(){
			$val = get_option('option_name');
		$val = $val ? $val['email'] : null;
		?>
		<input id="ya-disc-email" type="text" name="option_name[email]" value="<?php echo esc_attr( $val ) ?>" size="50"/>
        <p><label for="ya-disc-email">Если вы хотите получать уведомления на почту о статусе скачивания файла - введите сюда свой e-mail</label></p>
        <p><code><i>Это может быть полезно при запранированной загрузке файла через задачу в WP-Crone</i></code></p>
		<?php
	}




}