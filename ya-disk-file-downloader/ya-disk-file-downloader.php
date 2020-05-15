<?php
/**
Plugin Name: Yandex Disc file downloader
Description: Скачивает расшаренный файл с ценами и остатками с Яндекс.Диска по публичной ссылке и добавляет в CRON на 6:00 утра.
Version: 1.01
Author: Undizzy
Author URI: https://twitter.com/NVitkovsky
Short Name: YDFD_
*/
defined( 'ABSPATH' ) || exit;

define('YDFD_PATH', plugin_dir_path( __FILE__ ));

require YDFD_PATH . 'classes/class-ydfd.php';

function run_ydfd() {
	$plugin = new YDFD();
}
run_ydfd();

register_uninstall_hook(__FILE__, 'YDFD_uninstall' );
function YDFD_uninstall(){
    $admin_actions = new YDFD_Actions();
	delete_option("option_name");
	$files = scandir($admin_actions->dirpath);
	foreach ($files as $file){
		if (is_file($admin_actions->dirpath . '/' . $file)) {
			unlink( $admin_actions->dirpath . '/' . $file );
		}
	}
	if (file_exists($admin_actions->dirpath) ) {
		rmdir($admin_actions->dirpath);
	}
}