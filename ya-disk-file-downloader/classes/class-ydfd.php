<?php


class YDFD {
	public function __construct(){
		$this->load_dependencies();
		$this->define_admin_hooks();
	}
	private function load_dependencies(){
		require_once __DIR__ . '/class-ydfd-settings.php';
		require_once __DIR__ . '/class-ydfd-actions.php';
		require_once __DIR__ . '/class-ydfd-output.php';

	}
	public function define_admin_hooks(){
		$admin_settings = new YDFD_Settings();
		$admin_actions  = new YDFD_Actions();

		add_action('admin_menu', array($admin_settings, 'YAFD_admin_menu') );
		add_action('admin_init', array($admin_settings, 'YDFD_register_settings') );
		add_action( 'plugins_loaded', array($admin_settings, 'plugin_lang') );

		add_action('admin_post_get_file', array($admin_actions, 'YDFD_get_file'));
		add_action('admin_post_delete_file', array($admin_actions, 'delete_file'));
		add_action('admin_post_delete_cron_event', array($admin_actions, 'YDFD_dell_cron'));
		add_action('admin_post_add_cron_event', array($admin_actions, 'YDFD_add_to_cron'));

		register_activation_hook(YDFD_FILE, array($admin_actions, 'YDFD_add_file_folder') );
		add_action('YDFD_daily_event', array($admin_actions, 'do_YDFD_daily_cron') );
		register_deactivation_hook( YDFD_PATH, array($admin_actions, 'YDFD_dell_cron') );
		add_action('init', function () {
			if (session_id() == '') {
				session_start();
			}
		});
	}
}